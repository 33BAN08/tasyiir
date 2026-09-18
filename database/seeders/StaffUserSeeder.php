<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;

/**
 * One reception and one accountant demo account per tenant (password
 * "password", like the owners) so the Team panel has rows on first look and
 * both roles can be tried immediately.
 */
class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            ['name' => 'سعاد بنعمر', 'local' => 'reception', 'role' => Permissions::RECEPTION_ROLE],
            ['name' => 'خالد الصقلي', 'local' => 'accountant', 'role' => Permissions::ACCOUNTANT_ROLE],
        ];

        Tenant::all()->each(function (Tenant $tenant) use ($staff) {
            foreach ($staff as $member) {
                $user = User::firstOrCreate(
                    ['email' => "{$member['local']}@{$tenant->slug}.test"],
                    [
                        'tenant_id' => $tenant->id,
                        'name' => $member['name'],
                        'password' => 'password',
                        'status' => 'نشط',
                        'email_verified_at' => now(),
                    ]
                );

                $user->syncRoles([$member['role']]);
            }
        });
    }
}
