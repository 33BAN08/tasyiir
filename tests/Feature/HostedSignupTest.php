<?php

namespace Tests\Feature;

use App\Livewire\Public\CenterSignup;
use App\Models\CenterSignupRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\RunsInMode;
use Tests\TestCase;

/** Hosted edition: instant signup by default, reviewed signup when switched on. */
class HostedSignupTest extends TestCase
{
    use RefreshDatabase, RunsInMode;

    protected function setUp(): void
    {
        $this->runInMode('saas');

        parent::setUp();
        config(['tasyiir.mode' => 'saas']);
    }

    protected function tearDown(): void
    {
        $this->restoreMode();

        parent::tearDown();
    }

    public function test_instant_signup_creates_the_center_and_signs_the_owner_in(): void
    {
        config(['tasyiir.signup_requires_approval' => false]);

        $this->signupForm()->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $owner = User::withoutGlobalScopes()->sole();

        $this->assertSame('أكاديمية النور', Tenant::sole()->name);
        $this->assertTrue($owner->hasRole(Permissions::OWNER_ROLE));
        $this->assertSame(0, CenterSignupRequest::count());
        $this->assertAuthenticatedAs($owner);
    }

    public function test_reviewed_signup_only_files_a_request(): void
    {
        config(['tasyiir.signup_requires_approval' => true]);

        $this->signupForm()->call('submit')->assertHasNoErrors()->assertSet('submitted', true);

        $this->assertSame(0, Tenant::count());
        $this->assertSame(0, User::withoutGlobalScopes()->count());
        $this->assertSame('pending', CenterSignupRequest::sole()->status);
        $this->assertGuest();
    }

    public function test_an_approved_request_provisions_the_center(): void
    {
        config(['tasyiir.signup_requires_approval' => true]);
        $this->signupForm()->call('submit');

        $admin = User::withoutGlobalScopes()->create([
            'name' => 'Ops', 'email' => 'ops@tasyiir.test', 'password' => 'secret1234',
            'status' => 'نشط', 'is_platform_admin' => true,
        ]);

        Livewire::actingAs($admin)->test(\App\Livewire\Admin\SignupRequests::class)
            ->call('approve', CenterSignupRequest::sole()->id);

        $this->assertSame('approved', CenterSignupRequest::sole()->status);
        $owner = User::withoutGlobalScopes()->where('email', 'leila@nour.test')->sole();
        $this->assertTrue($owner->hasRole(Permissions::OWNER_ROLE));
        $this->assertSame(Tenant::sole()->id, $owner->tenant_id);
    }

    public function test_setup_does_not_exist_in_hosted_mode(): void
    {
        $this->get('/setup')->assertNotFound();
    }

    public function test_signup_rejects_an_email_that_already_has_a_login(): void
    {
        config(['tasyiir.signup_requires_approval' => false]);
        $this->signupForm()->call('submit');
        auth()->logout();

        $this->signupForm()->call('submit')->assertHasErrors('owner_email');
        $this->assertSame(1, Tenant::count());
    }

    protected function signupForm()
    {
        return Livewire::test(CenterSignup::class)
            ->set('center_name', 'أكاديمية النور')
            ->set('owner_name', 'ليلى بنعمر')
            ->set('owner_email', 'leila@nour.test')
            ->set('owner_phone', '0655-55-55-55')
            ->set('password', 'secret1234')
            ->set('password_confirmation', 'secret1234');
    }
}
