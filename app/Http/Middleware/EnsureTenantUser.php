<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The tenant app is for users that belong to a center. A platform admin has
 * no tenant, so it is sent to its own area instead of ever rendering a tenant
 * page (TenantScope would return nothing for it anyway — this keeps the two
 * areas cleanly apart rather than relying on that).
 */
class EnsureTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isPlatformAdmin()) {
            return redirect()->route('admin.signups');
        }

        // A tenant-less non-admin account should not exist; never let one in.
        if ($user && ! $user->tenant_id) {
            abort(403);
        }

        return $next($request);
    }
}
