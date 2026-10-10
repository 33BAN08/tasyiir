<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Carbon;

/**
 * Online demo server (hosted edition with TASYIIR_DEMO=true): every center
 * gets a free trial of tasyiir.demo.days days, counted from its creation,
 * plus any extra days the operator grants from /admin/centers.
 */
class Demo
{
    public static function enabled(): bool
    {
        return Mode::isSaas() && (bool) config('tasyiir.demo.enabled');
    }

    public static function endsAt(Tenant $tenant): Carbon
    {
        $extra = (int) ($tenant->settings['demo_extra_days'] ?? 0);

        return $tenant->created_at->copy()
            ->addDays((int) config('tasyiir.demo.days') + $extra)
            ->endOfDay();
    }

    /** Whole days left, 0 on the last day, negative once expired. */
    public static function daysLeft(Tenant $tenant): int
    {
        $end = self::endsAt($tenant);

        return $end->isPast()
            ? -1 * (int) ceil($end->diffInDays(now(), true))
            : (int) floor(now()->diffInDays($end, true));
    }

    public static function expired(Tenant $tenant): bool
    {
        return self::enabled() && self::endsAt($tenant)->isPast();
    }

    public static function whatsappUrl(?string $text = null): ?string
    {
        $number = (string) config('tasyiir.demo.whatsapp');

        if ($number === '') {
            return null;
        }

        return 'https://wa.me/'.$number.($text ? '?text='.rawurlencode($text) : '');
    }

    /** wa.me link to a center's own phone — same number rules as everywhere else. */
    public static function whatsappTo(?string $phone): ?string
    {
        return WhatsApp::link($phone);
    }
}
