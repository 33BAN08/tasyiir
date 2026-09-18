<?php

namespace App\Models;

use App\Support\Permissions;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Spatie's Role, plus tenant ownership for custom roles. Built-in roles have
 * tenant_id NULL and are visible to every center; a custom role is created by
 * one center and never listed, assigned or resolved for another.
 *
 * Spatie enforces a global unique (name, guard_name), so custom roles are
 * stored under a namespaced name and shown through `label`.
 */
class Role extends SpatieRole
{
    /** Internal (unique) name for a tenant's custom role. */
    public static function namespacedName(int $tenantId, string $displayName): string
    {
        return "tenant-{$tenantId}:".trim($displayName);
    }

    /** What the UI shows: the plain name for built-in roles, display_name for custom ones. */
    public function getLabelAttribute(): string
    {
        return $this->display_name ?: $this->name;
    }

    public function getIsBuiltInAttribute(): bool
    {
        return $this->tenant_id === null;
    }

    public function getIsOwnerAttribute(): bool
    {
        return $this->name === Permissions::OWNER_ROLE;
    }

    /** Human description: built-in roles have one; custom roles list their permissions. */
    public function getDescriptionAttribute(): string
    {
        if ($this->is_built_in) {
            return Permissions::BUILT_IN_ROLES[$this->name]['description'] ?? '';
        }

        $labels = $this->permissions->pluck('name')->map(fn ($p) => Permissions::LABELS[$p] ?? $p);

        return $labels->isEmpty() ? 'بدون صلاحيات' : $labels->implode('، ');
    }

    /** Built-in roles plus the given tenant's own custom roles. */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where(function (Builder $q) use ($tenantId) {
            $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
        });
    }

    /** Roles an owner may hand to staff: everything visible except the owner role itself. */
    public function scopeAssignable(Builder $query, int $tenantId): Builder
    {
        return $query->forTenant($tenantId)->where('name', '!=', Permissions::OWNER_ROLE);
    }
}
