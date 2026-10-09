<?php

namespace Tests\Feature;

use App\Services\CenterProvisioner;
use App\Services\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\Concerns\RunsInMode;
use Tests\TestCase;

/** Offline licence states: trial, valid, expired, wrong machine, tampered. */
class LicenseTest extends TestCase
{
    use RefreshDatabase, RunsInMode;

    protected string $secretKey;

    protected string $licencePath;

    protected function setUp(): void
    {
        $this->runInMode('local');

        parent::setUp();

        if (! extension_loaded('sodium')) {
            $this->markTestSkipped('The sodium extension is required for licence checks.');
        }

        $pair = sodium_crypto_sign_keypair();
        $this->secretKey = sodium_crypto_sign_secretkey($pair);
        $this->licencePath = storage_path('framework/testing/licence-'.getmypid().'.key');
        File::delete($this->licencePath);

        config([
            'tasyiir.mode' => 'local',
            'tasyiir.license.public_key' => base64_encode(sodium_crypto_sign_publickey($pair)),
            'tasyiir.license.file' => $this->licencePath,
            'tasyiir.license.trial_days' => 14,
        ]);

        Cache::flush();

        app(CenterProvisioner::class)->provision('مركز الاختبار', 'المدير', 'owner@test.test', 'secret1234');
    }

    protected function tearDown(): void
    {
        $this->restoreMode();

        File::delete($this->licencePath);

        parent::tearDown();
    }

    /** Signs a licence the way tools/license-issuer/issue.php does. */
    protected function issue(array $overrides = []): string
    {
        $payload = array_merge([
            'center_name' => 'مركز الاختبار',
            'machine_id' => app(License::class)->machineCode(),
            'issued_at' => now()->toDateString(),
            'expires_at' => now()->addYear()->toDateString(),
            'plan' => 'yearly',
        ], $overrides);

        $p = base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $s = base64_encode(sodium_crypto_sign_detached($p, $this->secretKey));

        return base64_encode(json_encode(['p' => $p, 's' => $s]));
    }

    public function test_a_fresh_install_is_on_trial_and_can_use_the_modules(): void
    {
        $license = app(License::class);

        $this->assertSame(License::TRIAL, $license->status()['state']);
        $this->assertTrue($license->isValid());
        $this->assertSame(14, $license->status()['days_left']);
    }

    public function test_the_trial_runs_out_after_the_configured_days(): void
    {
        $this->travelTo(now()->addDays(15));

        $this->assertSame(License::TRIAL_EXPIRED, app(License::class)->status()['state']);
        $this->assertFalse(app(License::class)->isValid());

        $this->travelBack();
    }

    public function test_a_valid_licence_activates(): void
    {
        $license = app(License::class);

        $status = $license->install($this->issue());

        $this->assertSame(License::LICENSED, $status['state']);
        $this->assertTrue($license->isValid());
        $this->assertFileExists($this->licencePath);
        $this->assertSame('yearly', $license->status()['plan']);
    }

    public function test_a_licence_with_no_expiry_never_runs_out(): void
    {
        app(License::class)->install($this->issue(['expires_at' => null, 'plan' => 'lifetime']));

        $this->travelTo(now()->addYears(5));
        $this->assertSame(License::LICENSED, app(License::class)->status()['state']);
        $this->travelBack();
    }

    public function test_an_expired_licence_is_refused(): void
    {
        File::put($this->licencePath, $this->issue(['expires_at' => now()->subDay()->toDateString()]));

        $this->assertSame(License::EXPIRED, app(License::class)->status()['state']);
        $this->assertFalse(app(License::class)->isValid());
    }

    public function test_a_licence_issued_for_another_machine_is_refused(): void
    {
        $status = app(License::class)->install($this->issue(['machine_id' => 'AAAA-BBBB-CCCC-DDDD']));

        $this->assertSame(License::WRONG_MACHINE, $status['state']);
        $this->assertFileDoesNotExist($this->licencePath, 'a foreign licence must not be stored');
    }

    public function test_a_tampered_licence_is_refused(): void
    {
        // Re-sign nothing: swap the payload of a correctly signed licence.
        $licence = $this->issue(['expires_at' => now()->subDay()->toDateString()]);
        $envelope = json_decode(base64_decode($licence), true);
        $payload = json_decode(base64_decode($envelope['p']), true);
        $payload['expires_at'] = now()->addYears(10)->toDateString();
        $envelope['p'] = base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));

        File::put($this->licencePath, base64_encode(json_encode($envelope)));

        $this->assertSame(License::INVALID, app(License::class)->status()['state']);
        $this->assertFalse(app(License::class)->isValid());
    }

    public function test_a_licence_signed_by_the_wrong_key_is_refused(): void
    {
        $otherPair = sodium_crypto_sign_keypair();
        $this->secretKey = sodium_crypto_sign_secretkey($otherPair);

        $status = app(License::class)->install($this->issue());

        $this->assertSame(License::INVALID, $status['state']);
    }

    public function test_licences_are_skipped_entirely_in_hosted_mode(): void
    {
        config(['tasyiir.mode' => 'saas']);
        $this->travelTo(now()->addYears(3));

        $this->assertSame(License::NOT_REQUIRED, app(License::class)->status()['state']);
        $this->assertTrue(app(License::class)->isValid());

        $this->travelBack();
    }

    public function test_the_owner_is_warned_before_expiry(): void
    {
        app(License::class)->install($this->issue(['expires_at' => now()->addDays(10)->toDateString()]));

        $this->assertTrue(app(License::class)->isExpiringSoon());
    }

    public function test_the_machine_code_is_stable_and_readable(): void
    {
        $code = app(License::class)->machineCode();

        $this->assertMatchesRegularExpression('/^[A-F0-9]{4}(-[A-F0-9]{4}){3}$/', $code);
        $this->assertSame($code, app(License::class)->machineCode());
    }
}
