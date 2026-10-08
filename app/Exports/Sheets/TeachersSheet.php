<?php

namespace App\Exports\Sheets;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;

class TeachersSheet extends ModelSheet
{
    public function title(): string
    {
        return 'Teachers';
    }

    protected function query(): Builder
    {
        return Teacher::query()->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            'ID' => 'id',
            'الاسم' => 'name',
            'التخصص' => 'specialty',
            'الهاتف' => 'phone',
            'البريد الإلكتروني' => 'email',
            'ساعات التدريس' => 'hours',
            'نوع الأجر' => 'salary_type',
            'الراتب الثابت' => 'fixed_salary',
            'نسبة العمولة' => 'commission_rate',
            'الحالة' => 'status',
        ];
    }
}
