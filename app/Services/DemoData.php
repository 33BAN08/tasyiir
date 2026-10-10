<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use Database\Seeders\AttendanceSeeder;
use Database\Seeders\EnrollmentSeeder;
use Database\Seeders\ExpenseSeeder;
use Database\Seeders\NotificationSeeder;
use Database\Seeders\PaymentSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\SalaryPaymentSeeder;
use Database\Seeders\ScheduleSlotSeeder;
use Database\Seeders\SeedScope;
use Database\Seeders\StudentSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Online demo only: fills ONE freshly created center with the same realistic
 * sample data as `db:seed` (teachers, courses, groups, 45 students,
 * enrollments, schedule, attendance, payments, expenses, salaries), so a
 * prospect sees a living dashboard instead of empty pages.
 *
 * No logins are created (the staff seeder is skipped on purpose: shared
 * "password" accounts must never exist on a public server).
 */
class DemoData
{
    public const SEEDERS = [
        ReferenceDataSeeder::class,
        StudentSeeder::class,
        EnrollmentSeeder::class,
        ScheduleSlotSeeder::class,
        AttendanceSeeder::class,
        PaymentSeeder::class,
        ExpenseSeeder::class,
        SalaryPaymentSeeder::class,
        NotificationSeeder::class,
    ];

    public function fill(Tenant $tenant): void
    {
        $previous = SeedScope::$tenantIds;
        SeedScope::$tenantIds = [$tenant->id];

        try {
            DB::transaction(function () use ($tenant) {
                foreach (self::SEEDERS as $seeder) {
                    app($seeder)->run();
                }

                $this->bringUpToDate($tenant);
            });
        } finally {
            SeedScope::$tenantIds = $previous;
        }
    }

    /**
     * The seeders spread registrations over fixed months of the year. Slide
     * the history forward so the newest registration is today: the dashboard
     * then shows this month's registrations and revenue instead of zeros.
     */
    protected function bringUpToDate(Tenant $tenant): void
    {
        $latest = Student::withoutGlobalScopes()->where('tenant_id', $tenant->id)->max('registered_at');

        if (! $latest) {
            return;
        }

        $shift = (int) \Illuminate\Support\Carbon::parse($latest)->startOfDay()->diffInDays(today(), false);

        if ($shift <= 0) {
            return;
        }

        $today = today();
        $move = function (string $model, string $column) use ($tenant, $shift, $today) {
            $model::withoutGlobalScopes()->where('tenant_id', $tenant->id)->get()->each(function ($row) use ($column, $shift, $today) {
                $date = $row->{$column}->copy()->addDays($shift);
                $row->forceFill([$column => $date->greaterThan($today) ? $today : $date])->saveQuietly();
            });
        };

        $move(Student::class, 'registered_at');
        $move(Enrollment::class, 'date');
        $move(Payment::class, 'date');
    }
}
