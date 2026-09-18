<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * A staff account paused by the owner (status = متوقف) is signed out on its
 * next request, so deactivation takes effect even for sessions already open.
 */
class EnsureUserIsActive
{
    public const MESSAGE = 'هذا الحساب متوقف. تواصل مع مدير المركز لإعادة تفعيله.';

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', self::MESSAGE);
        }

        return $next($request);
    }
}
