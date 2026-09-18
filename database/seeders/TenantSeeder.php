<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Two tenants with separate admin users, so tenant isolation can be
     * verified end to end (log in as tenant A, confirm tenant B's students
     * never appear).
     */
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'مركز النجاح للتكوين',
                'slug' => 'najah',
                'admin_name' => 'محمد الفاسي',
                'admin_email' => 'admin@najah.test',
            ],
            [
                'name' => 'أكاديمية المستقبل للغات',
                'slug' => 'moustaqbal',
                'admin_name' => 'سارة بنعلي',
                'admin_email' => 'admin@moustaqbal.test',
            ],
        ];

        foreach ($tenants as $data) {
            $tenant = Tenant::firstOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name'], 'settings' => []]
            );

            $user = User::firstOrCreate(
                ['email' => $data['admin_email']],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $data['admin_name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole('مدير المركز')) {
                $user->assignRole('مدير المركز');
            }
        }
    }
}
