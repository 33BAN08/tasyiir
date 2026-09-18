<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Single-database multi-tenancy: every tenant-owned model is automatically
 * scoped to the authenticated user's tenant_id, so no query anywhere in the
 * app can accidentally leak another tenant's rows.
 *
 * A signed-in user WITHOUT a tenant (a platform admin) matches nothing: the
 * scope adds an always-false clause instead of no clause, so even if such a
 * user reached a tenant page, every tenant-owned query would come back empty.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // User carries this scope too. Until the guard has resolved the current
        // user, a User query IS the guard's own lookup (session id, remember
        // token, or login credentials) and must not be tenant-filtered —
        // otherwise a tenant-less platform admin could never be loaded.
        // hasUser() does not trigger resolution, so it cannot recurse.
        if ($model instanceof User && ! Auth::hasUser()) {
            return;
        }

        $user = Auth::check() ? Auth::user() : null;

        if (! $user) {
            return;
        }

        if ($user->tenant_id) {
            $builder->where($model->getTable().'.tenant_id', $user->tenant_id);
        } else {
            $builder->whereRaw('1 = 0');
        }
    }
}
