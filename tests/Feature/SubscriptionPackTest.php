<?php

namespace Tests\Feature;

use App\Livewire\Enrollments\Index as EnrollmentsIndex;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CenterProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\Concerns\RunsInMode;
use Tests\TestCase;

/**
 * Subscription packs (1, 3, 6, 12 months). The first test is the one that
 * matters most: every enrollment that exists at a client today is monthly and
 * must keep behaving exactly as it did.
 */
class SubscriptionPackTest extends TestCase
{
    use RefreshDatabase, RunsInMode;

    protected Tenant $tenant;

    protected User $owner;

    protected Course $course;

    protected function setUp(): void
    {
        $this->runInMode('saas');

        parent::setUp();
        config(['tasyiir.mode' => 'saas']);

        ['tenant' => $this->tenant, 'owner' => $this->owner] = app(CenterProvisioner::class)
            ->provision('مركز الاشتراكات', 'المدير', 'owner@packs.test', 'secret1234');

        Auth::login($this->owner);

        $this->course = Course::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'الرياضيات',
            'price' => 300,
            'status' => 'نشط',
        ]);
    }

    protected function tearDown(): void
    {
        $this->restoreMode();

        parent::tearDown();
    }

    protected function student(string $name = 'طالب'): Student
    {
        return Student::create([
            'tenant_id' => $this->tenant->id,
            'name' => $name,
            'phone' => '0600000000',
            'registered_at' => now()->toDateString(),
            'enrollment_status' => 'نشط',
            'financial_status' => 'غير مؤدي',
        ]);
    }

    public function test_a_monthly_enrollment_behaves_exactly_as_before(): void
    {
        $student = $this->student();
        $start = today()->toDateString();

        Livewire::test(EnrollmentsIndex::class)
            ->call('openCreate')
            ->set('course_id', $this->course->id)
            ->set('student_id', $student->id)
            ->set('date', $start)
            ->set('paid', 300)
            ->call('save')
            ->assertHasNoErrors();

        $enrollment = Enrollment::firstOrFail();

        $this->assertSame(1, $enrollment->duration_months);
        $this->assertSame(300, (int) $enrollment->price, 'the monthly price is still the course price');
        $this->assertSame(today()->addMonthNoOverflow()->toDateString(), $enrollment->due_date->toDateString(), 'still one month after the start');
        $this->assertSame('مكتمل', $enrollment->status);
        $this->assertSame(0, (int) $enrollment->remaining);
    }

    public function test_a_three_month_pack_uses_the_pack_price_and_due_date(): void
    {
        $student = $this->student();

        Livewire::test(EnrollmentsIndex::class)
            ->call('openCreate')
            ->set('course_id', $this->course->id)
            ->set('duration_months', 3)
            ->set('student_id', $student->id)
            ->set('date', '2026-01-10')
            ->call('save')
            ->assertHasNoErrors();

        $enrollment = Enrollment::firstOrFail();

        $this->assertSame(3, $enrollment->duration_months);
        $this->assertSame(900, (int) $enrollment->price, '300 x 3 when no pack price is set');
        $this->assertSame('2026-04-10', $enrollment->due_date->toDateString());
        $this->assertSame(900, (int) $enrollment->remaining);
        $this->assertSame('غير مؤدي', $enrollment->status);
    }

    public function test_a_custom_pack_price_on_the_course_wins_over_the_fallback(): void
    {
        $this->course->update(['price_6_months' => 1500]);

        $this->assertSame(1500, $this->course->fresh()->priceFor(6), 'the agreed pack rate');
        $this->assertSame(900, $this->course->fresh()->priceFor(3), 'no 3-month rate set: 300 x 3');
        $this->assertSame(3600, $this->course->fresh()->priceFor(12), 'no yearly rate set: 300 x 12');
        $this->assertSame(300, $this->course->fresh()->priceFor(1), 'monthly is always the base price');

        $student = $this->student();

        Livewire::test(EnrollmentsIndex::class)
            ->call('openCreate')
            ->set('course_id', $this->course->id)
            ->set('duration_months', 6)
            ->set('student_id', $student->id)
            ->set('date', '2026-01-31')
            ->call('save')
            ->assertHasNoErrors();

        $enrollment = Enrollment::firstOrFail();

        $this->assertSame(1500, (int) $enrollment->price);
        // No overflow: 31 January + 6 months is the end of July, not 1 August.
        $this->assertSame('2026-07-31', $enrollment->due_date->toDateString());
    }

    public function test_rollover_of_a_paid_six_month_pack_owes_the_pack_price_and_the_next_period_is_six_months_later(): void
    {
        $student = $this->student();

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'course_id' => $this->course->id,
            'date' => today()->subMonths(6)->toDateString(),
            'due_date' => today()->toDateString(),
            'duration_months' => 6,
            'price' => 1800,
            'discount' => 300,
            'remaining' => 0,
            'status' => 'مكتمل',
        ]);

        Enrollment::rolloverDue();
        $enrollment->refresh();

        $this->assertSame('غير مؤدي', $enrollment->status);
        $this->assertSame(1500, (int) $enrollment->remaining, 'a six-month pack owes the pack price, not one month');
        $this->assertSame('غير مؤدي', $student->fresh()->financial_status);

        // Settling the new period is what moves the deadline, and it moves by a
        // full pack length.
        $enrollment->applyPayment(1500);
        $enrollment->refresh();

        $this->assertSame('مكتمل', $enrollment->status);
        $this->assertSame(today()->addMonthsNoOverflow(6)->toDateString(), $enrollment->due_date->toDateString());
    }

    public function test_rollover_of_a_monthly_enrollment_still_moves_by_one_month(): void
    {
        $student = $this->student();

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'course_id' => $this->course->id,
            'date' => today()->subMonth()->toDateString(),
            'due_date' => today()->toDateString(),
            'duration_months' => 1,
            'price' => 300,
            'discount' => 0,
            'remaining' => 0,
            'status' => 'مكتمل',
        ]);

        Enrollment::rolloverDue();
        $enrollment->refresh();

        $this->assertSame(300, (int) $enrollment->remaining);

        $enrollment->applyPayment(300);
        $enrollment->refresh();

        $this->assertSame(today()->addMonthNoOverflow()->toDateString(), $enrollment->due_date->toDateString());
    }

    public function test_a_pack_can_be_paid_in_instalments(): void
    {
        $student = $this->student();

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'course_id' => $this->course->id,
            'date' => today()->toDateString(),
            'due_date' => today()->addMonthsNoOverflow(3)->toDateString(),
            'duration_months' => 3,
            'price' => 900,
            'discount' => 0,
            'remaining' => 900,
            'status' => 'غير مؤدي',
        ]);

        $enrollment->applyPayment(300);
        $enrollment->refresh();
        $this->assertSame('جزئي', $enrollment->status);
        $this->assertSame(600, (int) $enrollment->remaining);
        $this->assertSame(300, $enrollment->paid);

        $enrollment->applyPayment(400);
        $enrollment->refresh();
        $this->assertSame('جزئي', $enrollment->status);
        $this->assertSame(200, (int) $enrollment->remaining);

        $enrollment->applyPayment(200);
        $enrollment->refresh();
        $this->assertSame('مكتمل', $enrollment->status);
        $this->assertSame(0, (int) $enrollment->remaining);
        $this->assertSame('مؤدي', $student->fresh()->financial_status);

        // The period was not due yet, so paying it off early does not shift the
        // deadline — the student simply paid ahead.
        $this->assertSame(today()->addMonthsNoOverflow(3)->toDateString(), $enrollment->due_date->toDateString());
    }

    /**
     * Revenue is counted from recorded payments, so a pack paid in instalments
     * is worth what was actually collected — never the pack price on top of it.
     */
    public function test_a_pack_paid_in_instalments_is_not_counted_twice_in_revenue(): void
    {
        $student = $this->student();

        $enrollment = Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'course_id' => $this->course->id,
            'date' => today()->toDateString(),
            'due_date' => today()->addMonthsNoOverflow(3)->toDateString(),
            'duration_months' => 3,
            'price' => 900,
            'discount' => 0,
            'remaining' => 900,
            'status' => 'غير مؤدي',
        ]);

        foreach ([400, 500] as $amount) {
            \App\Models\Payment::create([
                'tenant_id' => $this->tenant->id,
                'student_id' => $student->id,
                'amount' => $amount,
                'date' => today()->toDateString(),
                'method' => 'نقداً',
                'status' => 'مؤدي بالكامل',
            ]);
            $enrollment->applyPayment($amount);
        }

        $this->assertSame(900, \App\Support\Analytics::revenueThisMonth(), 'the collected 900, not 1800');

        $rate = \App\Support\Analytics::collectionRate();
        $this->assertSame(900, $rate['net'], 'one period of the pack is owed at a time');
        $this->assertSame(900, $rate['paid']);
        $this->assertSame(0, $rate['remaining']);
    }

    public function test_an_enrollment_created_before_packs_existed_is_monthly(): void
    {
        $student = $this->student();

        // Exactly the shape of a pre-migration row: the column is simply absent
        // from the insert and the database default applies.
        $id = \DB::table('enrollments')->insertGetId([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'course_id' => $this->course->id,
            'date' => '2026-01-10',
            'due_date' => '2026-02-10',
            'price' => 300,
            'discount' => 0,
            'remaining' => 0,
            'status' => 'مكتمل',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $enrollment = Enrollment::findOrFail($id);

        $this->assertSame(1, $enrollment->duration_months);
        $this->assertSame(1, $enrollment->months);
        $this->assertSame('شهري', $enrollment->duration_label);
    }

    public function test_the_duration_select_only_accepts_the_four_packs(): void
    {
        $student = $this->student();

        Livewire::test(EnrollmentsIndex::class)
            ->call('openCreate')
            ->set('course_id', $this->course->id)
            ->set('student_id', $student->id)
            ->set('date', '2026-01-10')
            ->set('duration_months', 4)
            ->call('save')
            ->assertHasErrors(['duration_months']);

        $this->assertSame(0, Enrollment::count());
    }

    public function test_choosing_a_pack_refills_the_price_and_the_end_of_the_period(): void
    {
        $this->course->update(['price_12_months' => 3000]);

        Livewire::test(EnrollmentsIndex::class)
            ->call('openCreate')
            ->set('date', '2026-01-10')
            ->set('course_id', $this->course->id)
            ->assertSet('price', 300)
            ->assertSet('due_date', '2026-02-10')
            ->set('duration_months', 12)
            ->assertSet('price', 3000)
            ->assertSet('due_date', '2027-01-10');
    }

    /**
     * What a client install actually goes through: rows already exist, then the
     * column is added. The migration's default is what keeps them monthly.
     */
    public function test_adding_the_column_to_a_database_that_already_has_enrollments_leaves_them_monthly(): void
    {
        $student = $this->student();

        \Schema::table('enrollments', fn ($table) => $table->dropColumn('duration_months'));

        $id = \DB::table('enrollments')->insertGetId([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'course_id' => $this->course->id,
            'date' => '2026-01-10',
            'due_date' => '2026-02-10',
            'price' => 300,
            'discount' => 0,
            'remaining' => 300,
            'status' => 'غير مؤدي',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        require_once database_path('migrations/2026_10_10_000001_add_duration_months_to_enrollments_table.php');
        (require database_path('migrations/2026_10_10_000001_add_duration_months_to_enrollments_table.php'))->up();

        $this->assertSame(1, (int) \DB::table('enrollments')->where('id', $id)->value('duration_months'));
        $this->assertSame(1, Enrollment::findOrFail($id)->months);
    }

    public function test_pack_prices_and_pack_enrollments_do_not_leak_between_centers(): void
    {
        ['tenant' => $other, 'owner' => $otherOwner] = app(CenterProvisioner::class)
            ->provision('مركز آخر', 'مدير آخر', 'other@packs.test', 'secret1234');

        $this->course->update(['price_6_months' => 1500]);

        $student = $this->student();
        Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'student_id' => $student->id,
            'course_id' => $this->course->id,
            'date' => today()->toDateString(),
            'due_date' => today()->addMonthsNoOverflow(6)->toDateString(),
            'duration_months' => 6,
            'price' => 1500,
            'discount' => 0,
            'remaining' => 1500,
            'status' => 'غير مؤدي',
        ]);

        Auth::login($otherOwner);

        $this->assertSame(0, Course::count(), 'the other center sees no courses');
        $this->assertSame(0, Enrollment::count(), 'nor the pack enrollment');
        $this->assertNull(Course::find($this->course->id), 'not even by id');
    }

    /**
     * The import stays a student importer: only a row that fills the optional
     * duration column also gets an enrollment.
     */
    public function test_the_import_creates_a_pack_enrollment_only_for_rows_that_ask_for_one(): void
    {
        $this->course->update(['price_3_months' => 800]);

        $import = new \App\Imports\StudentsImport;

        $rows = [
            // name, phone, course, duration
            ['بدون اشتراك', '0600000001', 'الرياضيات', ''],
            ['باشتراك ثلاثي', '0600000002', 'الرياضيات', '3'],
            ['مدة خاطئة', '0600000003', 'الرياضيات', '4'],
            ['مدة بلا دورة', '0600000004', '', '6'],
        ];

        foreach ($rows as $i => [$name, $phone, $course, $duration]) {
            // The package assigns the row number while streaming the sheet.
            $import->rememberRowNumber($i + 2);
            $model = $import->model([
                'الاسم' => $name,
                'الهاتف' => $phone,
                'الدورة' => $course,
                'مدة الاشتراك' => $duration,
            ]);
            $model?->save();
        }

        $import->createPendingEnrollments();

        $this->assertSame(2, $import->imported, 'two valid rows');
        $this->assertCount(2, $import->failures, 'bad duration and duration without a course are reported');
        $this->assertSame(1, $import->enrolled);

        $enrollment = Enrollment::firstOrFail();
        $this->assertSame('باشتراك ثلاثي', $enrollment->student->name);
        $this->assertSame(3, $enrollment->duration_months);
        $this->assertSame(800, (int) $enrollment->price, 'the course pack rate, not 3 x monthly');
        $this->assertSame(800, (int) $enrollment->remaining);
        $this->assertSame('غير مؤدي', $enrollment->status);
        $this->assertSame(today()->addMonthsNoOverflow(3)->toDateString(), $enrollment->due_date->toDateString());

        $this->assertNull(
            Student::where('name', 'بدون اشتراك')->firstOrFail()->currentEnrollment,
            'a blank duration imports the student only, exactly as before'
        );
    }

    public function test_the_student_form_hands_the_course_and_pack_to_the_enrollment_form(): void
    {
        $this->course->update(['price_3_months' => 800]);

        Livewire::test(\App\Livewire\Students\Index::class)
            ->call('openCreate')
            ->set('name', 'طالب جديد')
            ->set('phone', '0611111111')
            ->set('course_id', $this->course->id)
            ->set('duration_months', 3)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('enrollPrompt.course', $this->course->id)
            ->assertSet('enrollPrompt.duration', 3);

        // Nothing financial was created by the student form itself.
        $this->assertSame(0, Enrollment::count());

        $student = Student::firstOrFail();

        // The prompt links here; the form arrives pre-filled.
        Livewire::withQueryParams(['student' => $student->id, 'course' => $this->course->id, 'duration' => 3])
            ->test(EnrollmentsIndex::class)
            ->assertSet('duration_months', 3)
            ->assertSet('course_id', $this->course->id)
            ->assertSet('price', 800);
    }
}
