<?php

namespace Tests\Feature;

use App\Livewire\Admin\DemoCenters;
use App\Livewire\Public\CenterSignup;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CenterProvisioner;
use App\Support\Demo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\Concerns\RunsInMode;
use Tests\TestCase;

/** Online demo server: free trial signup, sample data, expiry, operator view. */
class DemoModeTest extends TestCase
{
    use RefreshDatabase, RunsInMode;

    protected function setUp(): void
    {
        $this->runInMode('saas');

        parent::setUp();
        config([
            'tasyiir.mode' => 'saas',
            'tasyiir.signup_requires_approval' => false,
            'tasyiir.demo.enabled' => true,
            'tasyiir.demo.days' => 7,
            'tasyiir.demo.whatsapp' => '212600000000',
        ]);
    }

    protected function tearDown(): void
    {
        $this->restoreMode();

        parent::tearDown();
    }

    public function test_demo_signup_fills_only_the_new_center_with_sample_data(): void
    {
        ['tenant' => $other] = app(CenterProvisioner::class)->provision('مركز آخر', 'Owner', 'other@test.ma', 'secret1234');

        $this->signupForm()->call('submit')->assertHasNoErrors()->assertRedirect(route('dashboard'));

        $tenant = Tenant::where('name', 'أكاديمية النور')->sole();
        $this->assertAuthenticatedAs(User::withoutGlobalScopes()->where('email', 'leila@nour.test')->sole());

        $this->assertSame(45, Student::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(45, Enrollment::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertGreaterThan(0, Payment::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());

        // History is slid forward so the dashboard's "this month" is not empty.
        $this->assertStringStartsWith(today()->toDateString(), Student::withoutGlobalScopes()->where('tenant_id', $tenant->id)->max('registered_at'));
        $this->assertLessThanOrEqual(today()->endOfDay()->toDateTimeString(), Payment::withoutGlobalScopes()->where('tenant_id', $tenant->id)->max('date'));

        // Nothing leaks into other centers, and no shared "password" logins are created.
        $this->assertSame(0, Student::withoutGlobalScopes()->where('tenant_id', $other->id)->count());
        $this->assertSame(1, User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());

        $this->get('/dashboard')->assertOk()->assertSee('نسخة تجريبية مجانية');
    }

    public function test_sample_data_can_be_skipped(): void
    {
        $this->signupForm()->set('sample_data', false)->call('submit')->assertHasNoErrors();

        $this->assertSame(0, Student::withoutGlobalScopes()->count());
    }

    public function test_the_phone_number_is_required_on_the_demo(): void
    {
        $this->signupForm()->set('owner_phone', '')->call('submit')->assertHasErrors('owner_phone');
        $this->assertSame(0, Tenant::count());
    }

    public function test_an_expired_trial_is_sent_to_the_contact_page(): void
    {
        ['tenant' => $tenant, 'owner' => $owner] = app(CenterProvisioner::class)->provision('مركز', 'Owner', 'o@test.ma', 'secret1234');

        $this->actingAs($owner)->get('/students')->assertOk();
        $this->get('/demo-expired')->assertRedirect(route('dashboard'));

        $this->travel(8)->days();

        $this->get('/students')->assertRedirect(route('demo.expired'));
        $this->get('/dashboard')->assertRedirect(route('demo.expired'));
        $this->get('/demo-expired')->assertOk()
            ->assertSee('انتهت الفترة التجريبية')
            ->assertSee('https://wa.me/212600000000', false);
    }

    public function test_the_operator_sees_the_centers_and_can_extend_a_trial(): void
    {
        ['tenant' => $tenant, 'owner' => $owner] = app(CenterProvisioner::class)->provision('مركز الأمل', 'Owner', 'o@test.ma', 'secret1234', '0612-34-56-78');
        $admin = User::withoutGlobalScopes()->create([
            'name' => 'Ops', 'email' => 'ops@tasyiir.test', 'password' => 'secret1234',
            'status' => 'نشط', 'is_platform_admin' => true,
        ]);

        $this->actingAs($admin)->get('/admin')->assertRedirect('/admin/centers');
        $this->get('/admin/centers')->assertOk()->assertSee('مركز الأمل')->assertSee('https://wa.me/212612345678', false);

        $this->travel(8)->days();
        $this->assertTrue(Demo::expired($tenant->fresh()));

        Livewire::actingAs($admin)->test(DemoCenters::class)->call('extend', $tenant->id);

        $this->assertFalse(Demo::expired($tenant->fresh()));
        $this->actingAs($owner)->get('/students')->assertOk();
    }

    public function test_guests_land_on_the_free_signup(): void
    {
        $this->get('/')->assertRedirect('/register-center');
        $this->get('/register-center')->assertOk()->assertSee('جرّب TASYIIR مجاناً');
        $this->get('/login')->assertOk()->assertSee('/register-center', false);
    }

    public function test_a_normal_hosted_install_has_no_trial_limit(): void
    {
        config(['tasyiir.demo.enabled' => false]);
        ['owner' => $owner] = app(CenterProvisioner::class)->provision('مركز', 'Owner', 'o@test.ma', 'secret1234');

        $this->travel(60)->days();

        $this->actingAs($owner)->get('/students')->assertOk()->assertDontSee('نسخة تجريبية مجانية');
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_https_links_behind_a_tunnel_when_proxies_are_trusted(): void
    {
        Route::get('/__scheme', fn () => request()->getScheme().'|'.request()->ip());

        $headers = ['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '41.250.1.2'];

        $this->get('/__scheme', $headers)->assertSee('http|127.0.0.1');

        config(['tasyiir.trusted_proxies' => '*']);
        $this->get('/__scheme', $headers)->assertSee('https|41.250.1.2');
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
