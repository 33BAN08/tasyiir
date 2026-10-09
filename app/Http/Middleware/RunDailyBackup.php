<?php

namespace App\Http\Middleware;

use App\Services\DatabaseBackup;
use App\Support\Mode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Local edition: take the day's database snapshot on the first page a signed-in
 * user opens, so an offline install needs no cron or Task Scheduler. start.bat
 * also triggers one when the PC is switched on.
 */
class RunDailyBackup
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Mode::isLocal() && $request->isMethod('GET') && $request->user()) {
            app(DatabaseBackup::class)->runDailyIfDue();
        }

        return $next($request);
    }
}
