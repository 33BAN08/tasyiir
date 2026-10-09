<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CenterProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\RunsInMode;
use Tests\TestCase;

/** The guarantee the whole multi-tenant design rests on. */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase, RunsInMode;

    protected User $ownerA;

    protected User $ownerB;

    protected Student $studentA;

    protected function setUp(): void
    {
        $this->runInMode('saas');

        parent::setUp();
        config(['tasyiir.mode' => 'saas']);

        $provisioner = app(CenterProvisioner::class);
        ['tenant' => $a, 'owner' => $this->ownerA] = $provisioner->provision('مركز أ', 'مدير أ', 'a@test.test', 'secret1234');
        ['owner' => $this->ownerB] = $provisioner->provision('مركز ب', 'مدير ب', 'b@test.test', 'secret1234');

        $this->studentA = Student::create([
            'tenant_id' => $a->id, 'name' => 'طالب المركز أ', 'phone' => '0600000000',
            'registered_at' => now()->toDateString(), 'enrollment_status' => 'نشط', 'financial_status' => 'غير مؤدي',
        ]);
    }

    protected function tearDown(): void
    {
        $this->restoreMode();

        parent::tearDown();
    }

    public function test_each_center_only_sees_its_own_rows(): void
    {
        $this->actingAs($this->ownerA);
        $this->assertSame(1, Student::count());
        $this->assertSame(1, User::count());

        $this->actingAs($this->ownerB);
        $this->assertSame(0, Student::count());
        $this->assertSame(1, User::count());
        $this->assertSame('b@test.test', User::sole()->email);
    }

    public function test_another_centers_record_is_a_404_even_with_the_right_id(): void
    {
        $this->actingAs($this->ownerA)->get("/students/{$this->studentA->id}")->assertOk();
        $this->actingAs($this->ownerB)->get("/students/{$this->studentA->id}")->assertNotFound();
    }

    public function test_a_new_row_is_stamped_with_the_creators_tenant(): void
    {
        $this->actingAs($this->ownerB);

        $student = Student::create([
            'name' => 'طالب المركز ب', 'phone' => '0611111111',
            'registered_at' => now()->toDateString(), 'enrollment_status' => 'نشط', 'financial_status' => 'غير مؤدي',
        ]);

        $this->assertSame($this->ownerB->tenant_id, $student->tenant_id);
        $this->assertNotSame($this->ownerA->tenant_id, $student->tenant_id);
    }

    public function test_a_platform_admin_sees_no_tenant_data_at_all(): void
    {
        $admin = User::withoutGlobalScopes()->create([
            'name' => 'Ops', 'email' => 'ops@test.test', 'password' => 'secret1234',
            'status' => 'نشط', 'is_platform_admin' => true,
        ]);

        $this->actingAs($admin);

        $this->assertSame(0, Student::count());
        $this->assertSame(0, User::count());
        $this->assertSame(2, Tenant::count(), 'tenants themselves are not tenant-scoped');
    }
}
