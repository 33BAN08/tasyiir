<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Enrollment;
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
 *
 * The optional "مدة الاشتراك" column is the one thing that creates more than a
 * student: filling it in (1, 3, 6 or 12, alongside a course) also creates the
 * matching unpaid enrollment, because a pack length is meaningless without one.
 * Leaving it empty behaves exactly as the importer always has.
 */
class StudentsImport implements SkipsEmptyRows, SkipsOnError, SkipsOnFailure, ToModel, WithHeadingRow
{
    use Importable, RemembersRowNumber, SkipsErrors;

    /** Template columns, in order. Kept in one place so the template and the import can't drift. */
    public const HEADINGS = ['الاسم', 'الجنس', 'الهاتف', 'البريد الإلكتروني', 'المدينة', 'هاتف ولي الأمر', 'الدورة', 'المجموعة', 'تاريخ التسجيل', 'مدة الاشتراك'];

    public int $imported = 0;

    /** Enrollments created because a row filled in the optional duration column. */
    public int $enrolled = 0;

    /** @var list<array{row: int, message: string}> */
    public array $failures = [];

    /**
     * Rows that asked for a pack, held back until the students have been
     * saved and have ids. Leaving the duration column empty keeps the original
     * behaviour exactly: a student is imported and nothing is billed.
     *
     * @var list<array{student: Student, course: Course, months: int, date: string}>
     */
    protected array $pendingEnrollments = [];

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

        // Optional pack. Empty is the default (1 = monthly) and, as before,
        // imports the student without creating anything financial.
        $durationRaw = $get('مدة الاشتراك');
        $months = 1;
        if ($durationRaw !== '') {
            $months = (int) filter_var($durationRaw, FILTER_SANITIZE_NUMBER_INT);
            if (! in_array($months, Enrollment::DURATIONS, true)) {
                $this->fail($rowNumber, __('مدة الاشتراك ":value" غير صالحة — استعمل 1 أو 3 أو 6 أو 12.', ['value' => $durationRaw]));

                return null;
            }
            if (! $course) {
                $this->fail($rowNumber, __('مدة الاشتراك تتطلب تحديد الدورة في نفس السطر.'));

                return null;
            }
        }

        $this->imported++;

        // tenant_id is stamped explicitly, like every other creation path in the app.
        $student = new Student([
            'tenant_id' => auth()->user()->tenant_id,
            ...$data,
            'course_id' => $course?->id,
            'group_id' => $group?->id,
            'registered_at' => $registeredAt ?? now()->toDateString(),
            'enrollment_status' => 'نشط',
            'financial_status' => 'غير مؤدي',
        ]);

        if ($durationRaw !== '' && $course) {
            $this->pendingEnrollments[] = [
                'student' => $student,
                'course' => $course,
                'group' => $group,
                'months' => $months,
                'date' => $registeredAt ?? now()->toDateString(),
            ];
        }

        return $student;
    }

    /**
     * Creates the enrollments the duration column asked for. Called by the
     * importer once the sheet has been read, because a student only has an id
     * after the package has saved it. Each one starts unpaid for the pack
     * price, exactly like one created by hand in التسجيلات.
     */
    public function createPendingEnrollments(): int
    {
        foreach ($this->pendingEnrollments as $pending) {
            $student = $pending['student'];
            if (! $student->exists) {
                continue;
            }

            $price = $pending['course']->priceFor($pending['months']);

            Enrollment::create([
                'tenant_id' => $student->tenant_id,
                'student_id' => $student->id,
                'course_id' => $pending['course']->id,
                'group_id' => $pending['group']?->id,
                'date' => $pending['date'],
                'due_date' => Carbon::parse($pending['date'])->addMonthsNoOverflow($pending['months'])->toDateString(),
                'duration_months' => $pending['months'],
                'price' => $price,
                'discount' => 0,
                'remaining' => $price,
                'status' => 'غير مؤدي',
            ]);

            $this->enrolled++;
        }

        $this->pendingEnrollments = [];

        return $this->enrolled;
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
