<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Student;
use App\Services\CenterProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * A center working late must not see yesterday's date on tonight's payment:
 * the app runs on Morocco time, not UTC.
 */
class TimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_app_runs_on_the_configured_timezone(): void
    {
        $this->assertSame('Africa/Casablanca', config('app.timezone'));
    }

    public function test_a_late_evening_action_is_stored_on_the_local_date(): void
    {
        ['tenant' => $tenant, 'owner' => $owner] = app(CenterProvisioner::class)
            ->provision('مركز الاختبار', 'المدير', 'owner@test.test', 'secret1234');
        $this->actingAs($owner);

        // 23:30 in Casablanca is already the next day in some zones and the
        // previous one in others; only the local date may be recorded.
        $moroccoEvening = Carbon::parse('2026-03-10 23:30', 'Africa/Casablanca');
        $this->travelTo($moroccoEvening);

        $student = Student::create([
            'tenant_id' => $tenant->id, 'name' => 'طالب', 'phone' => '0600000000',
            'registered_at' => now()->toDateString(), 'enrollment_status' => 'نشط', 'financial_status' => 'غير مؤدي',
        ]);

        $payment = Payment::create([
            'tenant_id' => $tenant->id, 'student_id' => $student->id, 'amount' => 500,
            'method' => 'نقداً', 'date' => now()->toDateString(), 'status' => 'مؤدي بالكامل',
        ]);

        $this->assertSame('2026-03-10', $payment->date->toDateString());
        $this->assertSame('2026-03-10', $student->registered_at->toDateString());
        $this->assertSame('2026-03-10', today()->toDateString());

        $this->travelBack();
    }
}
