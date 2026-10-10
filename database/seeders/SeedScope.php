<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Lets the sample-data seeders run for a chosen set of tenants (the online
 * demo fills one new center) instead of every tenant in the database.
 * Null = all tenants, the normal `db:seed` behaviour.
 */
class SeedScope
{
    /** @var array<int>|null */
    public static ?array $tenantIds = null;

    /** @return Collection<int, Tenant> */
    public static function tenants(): Collection
    {
        return Tenant::query()
            ->when(self::$tenantIds !== null, fn (Builder $q) => $q->whereIn('id', self::$tenantIds))
            ->orderBy('id')
            ->get();
    }

    /** Restrict a tenant-owned query to the scoped tenants. */
    public static function apply(Builder $query): Builder
    {
        return $query->when(self::$tenantIds !== null, fn (Builder $q) => $q->whereIn($q->getModel()->getTable().'.tenant_id', self::$tenantIds));
    }
}
