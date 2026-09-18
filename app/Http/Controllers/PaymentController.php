<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class PaymentController extends Controller
{
    /** Printable receipt for one payment (tenant-scoped via route model binding). */
    public function receipt(Payment $payment)
    {
        $payment->load(['student.currentEnrollment.course', 'student.currentEnrollment.group', 'tenant']);

        $enrollment = $payment->student?->currentEnrollment;

        // Everything paid on this enrollment up to and including this receipt.
        $paidToDate = $payment->student
            ? (int) $payment->student->payments()
                ->where(fn ($q) => $q->whereDate('date', '<', $payment->date)
                    ->orWhere(fn ($w) => $w->whereDate('date', $payment->date)->where('id', '<=', $payment->id)))
                ->sum('amount')
            : (int) $payment->amount;

        return view('payments.receipt', [
            'payment' => $payment,
            'student' => $payment->student,
            'enrollment' => $enrollment,
            'tenant' => $payment->tenant,
            'paidToDate' => $paidToDate,
            'number' => str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
        ]);
    }
}
