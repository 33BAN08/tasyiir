<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * The permission catalog and the three built-in (tenant-less) roles. Idempotent:
 * re-running syncs each built-in role's permission set to the catalog.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::keys() as $key) {
            Permission::firstOrCreate(['name' => $key, 'guard_name' => 'web']);
        }

        foreach (array_keys(Permissions::BUILT_IN_ROLES) as $name) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web'], ['tenant_id' => null]);
            $role->syncPermissions(Permissions::forBuiltInRole($name));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
