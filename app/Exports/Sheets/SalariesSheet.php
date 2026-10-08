<?php

namespace App\Exports\Sheets;

use App\Models\SalaryPayment;
use Illuminate\Database\Eloquent\Builder;

class SalariesSheet extends ModelSheet
{
    public function title(): string
    {
        return 'Salaries';
    }

    protected function query(): Builder
    {
        return SalaryPayment::with('teacher')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            'ID' => 'id',
            'الأستاذ' => fn ($s) => $s->teacher?->name,
            'ساعات التدريس' => 'hours',
            'السعر / ساعة' => 'rate',
            'الراتب' => 'salary',
            'المدفوع' => 'paid',
            'المتبقي' => 'remaining',
            'الحالة' => 'status',
        ];
    }
}
