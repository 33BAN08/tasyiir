<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Services\CenterProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\RunsInMode;
use Tests\TestCase;

/** What an expired licence does — and, just as important, does not do. */
class LicenseGateTest extends TestCase
{
    use RefreshDatabase, RunsInMode;

    protected User $owner;

    protected function setUp(): void
    {
        $this->runInMode('local');

        parent::setUp();

        if (! extension_loaded('sodium')) {
            $this->markTestSkipped('The sodium extension is required for licence checks.');
        }

        $pair = sodium_crypto_sign_keypair();

        config([
            'tasyiir.mode' => 'local',
            // A real key with no licence file: the trial is the only thing keeping it open.
            'tasyiir.license.public_key' => base64_encode(sodium_crypto_sign_publickey($pair)),
            'tasyiir.license.file' => storage_path('framework/testing/licence-gate-'.getmypid().'.key'),
            'tasyiir.license.trial_days' => 14,
        ]);

        Cache::flush();

        ['tenant' => $tenant, 'owner' => $this->owner] = app(CenterProvisioner::class)
            ->provision('مركز الاختبار', 'المدير', 'owner@test.test', 'secret1234');

        Student::create(['tenant_id' => $tenant->id, 'name' => 'طالب', 'phone' => '0600000000',
            'registered_at' => now()->toDateString(), 'enrollment_status' => 'نشط', 'financial_status' => 'غير مؤدي']);
    }

    protected function tearDown(): void
    {
        $this->restoreMode();

        parent::tearDown();
    }

    public function test_modules_work_during_the_trial(): void
    {
        $this->actingAs($this->owner)->get('/dashboard')->assertOk();
        $this->actingAs($this->owner)->get('/students')->assertOk();
    }

    public function test_an_expired_trial_blocks_the_modules_but_not_the_essentials(): void
    {
        $this->travelTo(now()->addDays(20));

        // Modules are closed...
        foreach (['/dashboard', '/students', '/payments', '/reports', '/notifications'] as $path) {
            $this->actingAs($this->owner)->get($path)
                ->assertRedirect(route('settings.index', ['tab' => 'license']));
        }

        // ...while login, the licence page and the data exports stay open.
        $this->actingAs($this->owner)->get('/settings')->assertOk();
        $this->actingAs($this->owner)->get(route('settings.backup'))->assertOk();
        $this->post('/logout')->assertRedirect();
        $this->post('/login', ['email' => 'owner@test.test', 'password' => 'secret1234']);

        $this->travelBack();
    }

    public function test_an_expired_trial_never_touches_the_data(): void
    {
        $before = Student::withoutGlobalScopes()->get()->toArray();

        $this->travelTo(now()->addDays(20));
        $this->actingAs($this->owner)->get('/dashboard');
        $this->travelBack();

        $this->assertEquals($before, Student::withoutGlobalScopes()->get()->toArray());
        $this->assertSame(1, User::withoutGlobalScopes()->count());
    }

    public function test_hosted_mode_is_never_gated(): void
    {
        config(['tasyiir.mode' => 'saas']);
        $this->travelTo(now()->addYears(2));

        $this->actingAs($this->owner)->get('/dashboard')->assertOk();

        $this->travelBack();
    }
}
