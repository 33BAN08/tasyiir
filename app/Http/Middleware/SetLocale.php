<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Picks the UI language for this request: the signed-in user's saved choice,
 * else the session's (guests on the login/signup pages), else the app default.
 */
class SetLocale
{
    public const SUPPORTED = ['ar', 'fr', 'en'];

    /** Carbon locale per UI locale: Moroccan Arabic month names (شتنبر, not سبتمبر). */
    public const CARBON = ['ar' => 'ar_MA', 'fr' => 'fr', 'en' => 'en'];

    public static function isRtl(?string $locale = null): bool
    {
        return ($locale ?? App::getLocale()) === 'ar';
    }

    public static function carbonLocale(?string $locale = null): string
    {
        return self::CARBON[$locale ?? App::getLocale()] ?? 'en';
    }

    public function handle(Request $request, Closure $next): Response
    {
        $locale = Auth::user()?->locale
            ?? $request->session()->get('locale')
            ?? config('app.locale');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);
        Carbon::setLocale(self::carbonLocale($locale));

        return $next($request);
    }
}
