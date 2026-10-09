<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\RunsInMode;
use Tests\TestCase;

/** Local edition: the owner creates their own center, with nobody to approve it. */
class LocalSetupTest extends TestCase
{
    use RefreshDatabase, RunsInMode;

    protected function setUp(): void
    {
        $this->runInMode('local');

        parent::setUp();
        config(['tasyiir.mode' => 'local']);
    }

    protected function tearDown(): void
    {
        $this->restoreMode();

        parent::tearDown();
    }

    public function test_every_page_leads_to_setup_while_no_center_exists(): void
    {
        $this->get('/dashboard')->assertRedirect('/setup');
        $this->get('/login')->assertRedirect('/setup');
        $this->get('/setup')->assertOk();
    }

    public function test_setup_creates_the_center_the_owner_and_the_roles_then_signs_in(): void
    {
        Livewire::test(\App\Livewire\Public\CenterSetup::class)
            ->set('center_name', 'مركز الأمل')
            ->set('owner_name', 'أمين الراشدي')
            ->set('owner_phone', '0612-34-56-78')
            ->set('owner_email', 'amine@amal.test')
            ->set('password', 'secret1234')
            ->set('password_confirmation', 'secret1234')
            ->set('locale', 'ar')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $tenant = Tenant::sole();
        $this->assertSame('مركز الأمل', $tenant->name);
        $this->assertNotSame('', $tenant->slug);
        $this->assertSame('0612-34-56-78', $tenant->setting('phone'));

        $owner = User::withoutGlobalScopes()->sole();
        $this->assertSame('amine@amal.test', $owner->email);
        $this->assertSame($tenant->id, $owner->tenant_id);
        $this->assertTrue($owner->hasRole(Permissions::OWNER_ROLE));
        $this->assertCount(count(Permissions::keys()), $owner->getAllPermissions());
        $this->assertAuthenticatedAs($owner);
    }

    public function test_the_new_center_is_empty(): void
    {
        $this->provisionCenter();

        $this->assertSame(0, \App\Models\Student::withoutGlobalScopes()->count());
        $this->assertSame(0, \App\Models\Teacher::withoutGlobalScopes()->count());
        $this->assertSame(0, \App\Models\Course::withoutGlobalScopes()->count());
        $this->assertSame(1, User::withoutGlobalScopes()->count());
    }

    public function test_setup_is_gone_once_a_center_exists(): void
    {
        $this->provisionCenter();

        $this->get('/setup')->assertNotFound();

        Livewire::test(\App\Livewire\Public\CenterSetup::class)->assertStatus(404);
    }

    public function test_hosted_only_routes_do_not_exist_in_local_mode(): void
    {
        $this->provisionCenter();

        $this->get('/register-center')->assertNotFound();
        $this->get('/admin')->assertNotFound();
        $this->get('/admin/signups')->assertNotFound();
    }

    public function test_the_owner_can_work_right_after_setup(): void
    {
        ['owner' => $owner] = $this->provisionCenter();

        $this->actingAs($owner)->get('/dashboard')->assertOk();
        $this->actingAs($owner)->get('/students')->assertOk();
        $this->actingAs($owner)->get('/settings')->assertOk();
    }

    /** @return array{tenant: Tenant, owner: User} */
    protected function provisionCenter(): array
    {
        return app(\App\Services\CenterProvisioner::class)
            ->provision('مركز الأمل', 'أمين الراشدي', 'amine@amal.test', 'secret1234');
    }
}
