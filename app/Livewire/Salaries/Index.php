<?php

namespace App\Livewire\Salaries;

use App\Models\Notification;
use App\Models\SalaryPayment;
use App\Models\Teacher;
use Livewire\Component;

class Index extends Component
{
    public ?int $payingTeacherId = null;

    public $payAmount = 0;

    public function openPay(int $teacherId): void
    {
        $row = SalaryPayment::where('teacher_id', $teacherId)->firstOrFail();
        $this->payingTeacherId = $teacherId;
        $this->payAmount = (int) $row->remaining;
        $this->resetValidation();
    }

    public function closePay(): void
    {
        $this->payingTeacherId = null;
        $this->payAmount = 0;
        $this->resetValidation();
    }

    public function recordPayment(): void
    {
        $row = SalaryPayment::where('teacher_id', $this->payingTeacherId)->firstOrFail();

        $this->validate(
            ['payAmount' => ['required', 'integer', 'min:1', 'max:'.max(1, (int) $row->remaining)]],
            ['payAmount.max' => 'المبلغ يتجاوز المتبقي للأستاذ (:max MAD).'],
            ['payAmount' => 'المبلغ'],
        );

        $row->recordPayment((int) $this->payAmount);

        Notification::notify([
            'title' => 'تم دفع أجرة الأستاذ '.$row->teacher->name,
            'body' => 'تم صرف '.mad((int) $this->payAmount).' من أجرة الأستاذ.',
            'icon' => 'banknote',
            'tone' => 'brand',
            'category' => 'المالية',
        ]);

        $this->dispatch('notification-created');

        $this->dispatch('toast', message: 'تم تسجيل دفعة الأجرة بنجاح');
        $this->closePay();
    }

    public function render()
    {
        // Balances are recomputed from each teacher's salary structure on every load.
        Teacher::with('students')->get()->each(fn (Teacher $t) => SalaryPayment::refreshFor($t));

        $rows = SalaryPayment::with('teacher')
            ->whereHas('teacher')
            ->get()
            ->sortBy(fn ($r) => $r->teacher->name, SORT_NATURAL)
            ->values();

        $stats = [
            'total' => (int) $rows->sum('salary'),
            'paid' => (int) $rows->sum('paid'),
            'remaining' => (int) $rows->sum('remaining'),
        ];

        $paying = $this->payingTeacherId ? $rows->firstWhere('teacher_id', $this->payingTeacherId) : null;

        return view('livewire.salaries.index', [
            'rows' => $rows,
            'stats' => $stats,
            'paying' => $paying,
        ])->extends('layouts.app')->section('content')->title('أجور الأساتذة');
    }
}
