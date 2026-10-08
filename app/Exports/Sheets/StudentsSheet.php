<?php

namespace App\Exports\Sheets;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;

class StudentsSheet extends ModelSheet
{
    public function title(): string
    {
        return 'Students';
    }

    protected function query(): Builder
    {
        return Student::with(['course', 'group'])->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            'ID' => 'id',
            'الاسم' => 'name',
            'الجنس' => 'gender',
            'الهاتف' => 'phone',
            'البريد الإلكتروني' => 'email',
            'المدينة' => 'city',
            'هاتف ولي الأمر' => 'guardian_phone',
            'الدورة' => fn ($s) => $s->course?->name,
            'المجموعة' => fn ($s) => $s->group?->name,
            'تاريخ التسجيل' => 'registered_at',
            'حالة التسجيل' => 'enrollment_status',
            'الحالة المالية' => 'financial_status',
        ];
    }
}
