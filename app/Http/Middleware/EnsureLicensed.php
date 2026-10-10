<?php

namespace App\Http\Middleware;

use App\Services\License;
use App\Support\Demo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Local edition: when the trial or licence has run out, the modules stop
 * opening and the user is sent to Settings → الترخيص. Login, that page, the
 * language switcher and the backup downloads stay reachable (they are outside
 * this middleware), and no data is ever touched.
 *
 * Online demo: the same gate sends a center whose free trial has ended to the
 * "trial over, contact us" page.
 */
class EnsureLicensed
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->user()?->tenant;

        if ($tenant && Demo::expired($tenant)) {
            return redirect()->route('demo.expired');
        }

        $license = app(License::class);

        if ($license->isValid()) {
            return $next($request);
        }

        return redirect()->route('settings.index', ['tab' => 'license'])
            ->with('toast', $license->status()['message']);
    }
}
