<?php

namespace App\Exports\Sheets;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class PaymentsSheet extends ModelSheet
{
    public function title(): string
    {
        return 'Payments';
    }

    protected function query(): Builder
    {
        return Payment::with('student')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            'ID' => 'id',
            'الطالب' => fn ($p) => $p->student?->name,
            'المبلغ' => 'amount',
            'طريقة الدفع' => 'method',
            'التاريخ' => 'date',
            'الحالة' => 'status',
        ];
    }
}
