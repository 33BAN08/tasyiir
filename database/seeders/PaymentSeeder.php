<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Database\Seeder;

/**
 * Historical payment rows matching what EnrollmentSeeder already implied as
 * paid (price - discount - remaining). Deliberately does NOT call
 * applyPayment(): the enrollments' remaining/status are already correct and
 * applying again would double-count.
 */
class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        Enrollment::withoutGlobalScopes()->orderBy('id')->get()->each(function (Enrollment $enrollment, int $i) {
            $paid = $enrollment->paid;
            if ($paid <= 0) {
                return;
            }

            Payment::create([
                'tenant_id' => $enrollment->tenant_id,
                'student_id' => $enrollment->student_id,
                'amount' => $paid,
                'method' => Payment::METHODS[$i % count(Payment::METHODS)],
                'date' => $enrollment->date->copy()->addDays($i % 5),
                'status' => $enrollment->remaining === 0 ? 'مؤدي بالكامل' : 'دفعة جزئية',
            ]);
        });
    }
}
