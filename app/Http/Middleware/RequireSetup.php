<?php

namespace App\Http\Middleware;

use App\Support\Mode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Local edition, before the first-run wizard has been used: every page leads
 * to /setup. Once a center exists this does nothing and /setup 404s.
 */
class RequireSetup
{
    /** Paths the wizard itself needs. */
    protected array $allowed = ['setup', 'locale', 'livewire/*', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->is(...$this->allowed) && Mode::needsSetup()) {
            return redirect('/setup');
        }

        return $next($request);
    }
}
