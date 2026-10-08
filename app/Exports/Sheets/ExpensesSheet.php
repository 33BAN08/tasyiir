<?php

namespace App\Exports\Sheets;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Builder;

class ExpensesSheet extends ModelSheet
{
    public function title(): string
    {
        return 'Expenses';
    }

    protected function query(): Builder
    {
        return Expense::query()->orderBy('date')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            'ID' => 'id',
            'الفئة' => 'category',
            'البيان' => 'label',
            'المبلغ' => 'amount',
            'التاريخ' => 'date',
            'طريقة الدفع' => 'method',
        ];
    }
}
