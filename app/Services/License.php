<?php

namespace App\Services;

use App\Models\Tenant;
use App\Support\Mode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Offline licence check for the local edition.
 *
 * A licence is a base64 blob holding a JSON payload and an Ed25519 signature
 * made with the vendor's private key (tools/license-issuer, never shipped).
 * The app only ever holds the public half, so a licence cannot be forged or
 * edited, and nothing ever leaves the machine — no activation server, no
 * telemetry. A licence is bound to a machine code derived from the
 * motherboard UUID, which is what stops an install being copied to another
 * center's PC.
 *
 * Failure is always soft: an expired or wrong licence blocks the modules but
 * never touches data, and login, this page and the backup download keep
 * working.
 */
class License
{
    public const LICENSED = 'licensed';

    public const TRIAL = 'trial';

    public const TRIAL_EXPIRED = 'trial_expired';

    public const EXPIRED = 'expired';

    public const INVALID = 'invalid';

    public const WRONG_MACHINE = 'wrong_machine';

    public const UNSIGNED_BUILD = 'unsigned_build';

    public const NOT_REQUIRED = 'not_required';

    /** States that let the modules run. */
    public const ALLOWED = [self::LICENSED, self::TRIAL, self::NOT_REQUIRED, self::UNSIGNED_BUILD];

    /**
     * @return array{state: string, expires_at: ?Carbon, days_left: ?int, plan: ?string, center_name: ?string, message: string}
     */
    public function status(): array
    {
        if (! Mode::isLocal()) {
            return $this->result(self::NOT_REQUIRED);
        }

        $text = $this->storedLicence();

        if ($text === null) {
            return $this->trialStatus();
        }

        $data = $this->parse($text);

        if ($data === null) {
            return $this->result(self::INVALID);
        }

        if (($data['machine_id'] ?? null) !== $this->machineCode()) {
            return $this->result(self::WRONG_MACHINE, centerName: $data['center_name'] ?? null, plan: $data['plan'] ?? null);
        }

        $expiresAt = isset($data['expires_at']) && $data['expires_at']
            ? Carbon::parse($data['expires_at'])->endOfDay()
            : null;

        if ($expiresAt && $expiresAt->isPast()) {
            return $this->result(self::EXPIRED, $expiresAt, $data['plan'] ?? null, $data['center_name'] ?? null);
        }

        return $this->result(self::LICENSED, $expiresAt, $data['plan'] ?? null, $data['center_name'] ?? null);
    }

    public function isValid(): bool
    {
        return in_array($this->status()['state'], self::ALLOWED, true);
    }

    /** True while the licence/trial is close enough to expiry to warn the owner. */
    public function isExpiringSoon(): bool
    {
        $status = $this->status();

        return $status['days_left'] !== null
            && $status['days_left'] <= (int) config('tasyiir.license.warn_before_days')
            && in_array($status['state'], [self::LICENSED, self::TRIAL], true);
    }

    /**
     * Stable per-machine code. On Windows this is the motherboard UUID, which
     * survives a reinstall of the app but differs on another PC; elsewhere
     * (dev machines) it falls back to the host identity.
     */
    public function machineCode(): string
    {
        return Cache::remember('tasyiir.machine_code', now()->addDay(), function () {
            $raw = $this->rawMachineId();

            $hash = strtoupper(substr(hash('sha256', $raw), 0, 16));

            return implode('-', str_split($hash, 4));
        });
    }

    protected function rawMachineId(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $uuid = @shell_exec('powershell -NoProfile -NonInteractive -Command "(Get-CimInstance Win32_ComputerSystemProduct).UUID" 2>NUL');
            $uuid = trim((string) $uuid);

            if ($uuid !== '' && ! str_contains(strtolower($uuid), 'error')) {
                return 'win:'.$uuid;
            }
        }

        // Dev/non-Windows fallback: stable for a given machine + install path.
        return 'fallback:'.php_uname('n').'|'.gethostname().'|'.base_path();
    }

    /** Validate and store a pasted licence. Returns the resulting status. */
    public function install(string $text): array
    {
        $text = trim($text);
        $data = $this->parse($text);

        if ($data === null) {
            return $this->result(self::INVALID);
        }

        if (($data['machine_id'] ?? null) !== $this->machineCode()) {
            return $this->result(self::WRONG_MACHINE, centerName: $data['center_name'] ?? null);
        }

        File::ensureDirectoryExists(dirname($this->path()));
        File::put($this->path(), $text);

        return $this->status();
    }

    public function remove(): void
    {
        File::delete($this->path());
    }

    public function path(): string
    {
        return (string) config('tasyiir.license.file');
    }

    public function hasLicenceFile(): bool
    {
        return $this->storedLicence() !== null;
    }

    protected function storedLicence(): ?string
    {
        $path = $this->path();

        return is_file($path) ? trim((string) File::get($path)) : null;
    }

    /**
     * Decode a licence and verify its signature against the shipped public
     * key. Returns null for anything malformed, tampered with, or signed by
     * the wrong key.
     *
     * @return array<string, mixed>|null
     */
    public function parse(string $text): ?array
    {
        $publicKey = (string) config('tasyiir.license.public_key');

        if ($publicKey === '' || ! extension_loaded('sodium')) {
            return null;
        }

        $envelope = json_decode((string) base64_decode(trim($text), true), true);

        if (! is_array($envelope) || ! isset($envelope['p'], $envelope['s'])) {
            return null;
        }

        $signature = base64_decode((string) $envelope['s'], true);
        $key = base64_decode($publicKey, true);

        if ($signature === false || $key === false || strlen($key) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            return null;
        }

        try {
            if (! sodium_crypto_sign_verify_detached($signature, (string) $envelope['p'], $key)) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $payload = json_decode((string) base64_decode((string) $envelope['p'], true), true);

        return is_array($payload) ? $payload : null;
    }

    /** Trial clock: starts when the center was created by the setup wizard. */
    protected function trialStatus(): array
    {
        // A build with no public key cannot verify any licence, so it would
        // lock itself out forever. Such a build simply never expires.
        if ((string) config('tasyiir.license.public_key') === '') {
            return $this->result(self::UNSIGNED_BUILD);
        }

        $start = Tenant::query()->oldest('created_at')->value('created_at');

        if (! $start) {
            return $this->result(self::TRIAL, now()->addDays((int) config('tasyiir.license.trial_days'))->endOfDay());
        }

        $endsAt = Carbon::parse($start)->addDays((int) config('tasyiir.license.trial_days'))->endOfDay();

        return $this->result($endsAt->isPast() ? self::TRIAL_EXPIRED : self::TRIAL, $endsAt);
    }

    protected function result(string $state, ?Carbon $expiresAt = null, ?string $plan = null, ?string $centerName = null): array
    {
        return [
            'state' => $state,
            'expires_at' => $expiresAt,
            'days_left' => $expiresAt ? max(0, (int) now()->startOfDay()->diffInDays($expiresAt->copy()->startOfDay(), false)) : null,
            'plan' => $plan,
            'center_name' => $centerName,
            'message' => match ($state) {
                self::LICENSED => __('الترخيص مفعّل.'),
                self::TRIAL => __('نسخة تجريبية.'),
                self::TRIAL_EXPIRED => __('انتهت الفترة التجريبية. فعّل ترخيصاً لمتابعة استعمال البرنامج.'),
                self::EXPIRED => __('انتهت صلاحية الترخيص. جدّد الترخيص لمتابعة استعمال البرنامج.'),
                self::WRONG_MACHINE => __('هذا الترخيص صادر لجهاز آخر.'),
                self::INVALID => __('ملف الترخيص غير صالح أو تم تعديله.'),
                default => '',
            },
        ];
    }
}
