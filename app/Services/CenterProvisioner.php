<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;

/**
 * The one place a center comes into existence. Used by the local first-run
 * wizard, by instant SaaS signup and by the platform admin's approval action,
 * so those three can never drift apart.
 *
 * A provisioned center is EMPTY — no demo data is ever seeded here.
 */
class CenterProvisioner
{
    /**
     * @param  string  $password  plain text, or an existing bcrypt hash (the
     *                            User model's 'hashed' cast leaves a hash alone)
     * @return array{tenant: Tenant, owner: User}
     */
    public function provision(
        string $name,
        string $ownerName,
        string $ownerEmail,
        string $password,
        ?string $ownerPhone = null,
        ?string $locale = null,
    ): array {
        $name = trim($name);
        $ownerEmail = mb_strtolower(trim($ownerEmail));

        return DB::transaction(function () use ($name, $ownerName, $ownerEmail, $password, $ownerPhone, $locale) {
            $this->ensureRolesExist();

            $tenant = Tenant::create([
                'name' => $name,
                'slug' => Tenant::uniqueSlug($name),
                'settings' => array_filter(['phone' => $ownerPhone ? trim($ownerPhone) : null]),
            ]);

            $owner = User::create([
                'tenant_id' => $tenant->id,
                'name' => trim($ownerName),
                'email' => $ownerEmail,
                'password' => $password,
                'status' => 'نشط',
                'locale' => $locale,
                'email_verified_at' => now(),
            ]);
            $owner->assignRole(Permissions::OWNER_ROLE);

            return ['tenant' => $tenant, 'owner' => $owner];
        });
    }

    /**
     * A brand-new local install has an empty roles table. Seeding the catalog
     * is idempotent, so this is safe to call on every provision.
     */
    public function ensureRolesExist(): void
    {
        if (Role::query()->where('name', Permissions::OWNER_ROLE)->exists()) {
            return;
        }

        (new RolePermissionSeeder)->run();
    }
}
