<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Imports students from the template produced by StudentImportTemplateExport.
 * Every row is validated with the same rules as the Students form; rows whose
 * course/group name matches nothing in this center are skipped and reported —
 * never guessed, never created implicitly. Valid rows are imported even when
 * others fail. Created rows get tenant_id from the signed-in user, and the
 * course/group lookups run under TenantScope, so another center's names can
 * never match.
 */
class StudentsImport implements SkipsEmptyRows, SkipsOnError, SkipsOnFailure, ToModel, WithHeadingRow
{
    use Importable, RemembersRowNumber, SkipsErrors;

    /** Template columns, in order. Kept in one place so the template and the import can't drift. */
    public const HEADINGS = ['الاسم', 'الجنس', 'الهاتف', 'البريد الإلكتروني', 'المدينة', 'هاتف ولي الأمر', 'الدورة', 'المجموعة', 'تاريخ التسجيل'];

    public int $imported = 0;

    /** @var list<array{row: int, message: string}> */
    public array $failures = [];

    public function __construct()
    {
        // Keep the Arabic headings as-is; the default "slug" formatter would mangle them.
        HeadingRowFormatter::default('none');
    }

    public function model(array $row): ?Student
    {
        $rowNumber = $this->getRowNumber();
        $get = fn (string $key) => isset($row[$key]) && $row[$key] !== null ? trim((string) $row[$key]) : '';

        $data = [
            'name' => $get('الاسم'),
            'phone' => $get('الهاتف'),
            'city' => $get('المدينة') ?: null,
            'guardian_phone' => $get('هاتف ولي الأمر') ?: null,
            'email' => $get('البريد الإلكتروني') ?: null,
            'gender' => self::normalizeGender($get('الجنس')),
        ];

        // Same rules as the Students form (App\Livewire\Students\Index::rules()).
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'gender' => ['nullable', 'in:male,female'],
        ], [], [
            'name' => __('الاسم الكامل'),
            'phone' => __('رقم الهاتف'),
            'city' => __('المدينة'),
            'guardian_phone' => __('هاتف ولي الأمر'),
            'email' => __('البريد الإلكتروني'),
            'gender' => __('الجنس'),
        ]);

        if ($validator->fails()) {
            $this->fail($rowNumber, implode(' ', $validator->errors()->all()));

            return null;
        }

        // Course / group: exact name match (case-insensitive) within this center only.
        $course = null;
        if ($courseName = $get('الدورة')) {
            $course = Course::whereRaw('LOWER(name) = ?', [mb_strtolower($courseName)])->first();
            if (! $course) {
                $this->fail($rowNumber, __('الدورة ":name" غير موجودة — تحقق من الاسم أو أنشئ الدورة أولاً.', ['name' => $courseName]));

                return null;
            }
        }

        $group = null;
        if ($groupName = $get('المجموعة')) {
            $group = Group::whereRaw('LOWER(name) = ?', [mb_strtolower($groupName)])
                ->when($course, fn ($q) => $q->where('course_id', $course->id))
                ->first();
            if (! $group) {
                $this->fail($rowNumber, $course
                    ? __('المجموعة ":name" غير موجودة في دورة ":course".', ['name' => $groupName, 'course' => $course->name])
                    : __('المجموعة ":name" غير موجودة — تحقق من الاسم أو أنشئ المجموعة أولاً.', ['name' => $groupName]));

                return null;
            }
            $course ??= $group->course;
        }

        $registeredAt = self::parseDate($get('تاريخ التسجيل'));
        if ($get('تاريخ التسجيل') !== '' && ! $registeredAt) {
            $this->fail($rowNumber, __('تاريخ التسجيل غير صالح (استعمل YYYY-MM-DD أو DD/MM/YYYY).'));

            return null;
        }

        $this->imported++;

        // tenant_id is stamped explicitly, like every other creation path in the app.
        return new Student([
            'tenant_id' => auth()->user()->tenant_id,
            ...$data,
            'course_id' => $course?->id,
            'group_id' => $group?->id,
            'registered_at' => $registeredAt ?? now()->toDateString(),
            'enrollment_status' => 'نشط',
            'financial_status' => 'غير مؤدي',
        ]);
    }

    /** Validation failures raised by the package itself (none configured, kept for completeness). */
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->fail($failure->row(), implode(' ', $failure->errors()));
        }
    }

    protected function fail(int $row, string $message): void
    {
        $this->failures[] = ['row' => $row, 'message' => $message];
    }

    /** ذكر/أنثى or male/female, empty otherwise (the column is optional). */
    protected static function normalizeGender(string $value): ?string
    {
        return match (mb_strtolower($value)) {
            'ذكر', 'male', 'm', 'homme' => 'male',
            'أنثى', 'انثى', 'female', 'f', 'femme' => 'female',
            '' => null,
            default => $value, // left as-is so validation reports it
        };
    }

    /** Excel serial numbers and common typed formats → Y-m-d, or null when unparseable. */
    protected static function parseDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
        }

        // ISO first, then the day-first formats people type in Morocco.
        foreach (['Y-m-d', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y', 'd.m.Y', 'Y/m/d'] as $format) {
            if (Carbon::hasFormat($value, $format)) {
                return Carbon::createFromFormat($format, $value)->toDateString();
            }
        }

        return null;
    }
}
