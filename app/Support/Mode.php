<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;

/**
 * Which edition this installation runs as. Every mode-dependent decision in
 * the app goes through here so there is one place to read (and one place to
 * fake in tests).
 */
class Mode
{
    public const LOCAL = 'local';

    public const SAAS = 'saas';

    public static function current(): string
    {
        return config('tasyiir.mode') === self::SAAS ? self::SAAS : self::LOCAL;
    }

    public static function isLocal(): bool
    {
        return self::current() === self::LOCAL;
    }

    public static function isSaas(): bool
    {
        return self::current() === self::SAAS;
    }

    /** SaaS only, and only while approval is switched on. */
    public static function signupRequiresApproval(): bool
    {
        return self::isSaas() && (bool) config('tasyiir.signup_requires_approval');
    }

    /**
     * True on a local install that has not run its first-run wizard yet.
     * Tolerates a database that is missing or not migrated (the installer
     * runs migrations before the first request, but a half-finished install
     * must not produce a stack trace).
     */
    public static function needsSetup(): bool
    {
        if (! self::isLocal()) {
            return false;
        }

        try {
            return Schema::hasTable('tenants') && ! Tenant::query()->exists();
        } catch (\Throwable) {
            return false;
        }
    }
}
