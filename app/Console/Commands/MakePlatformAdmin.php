<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Bootstraps the SaaS operator's own login. There is no UI for this on
 * purpose: nothing can approve the first admin, so it is a one-time CLI step.
 */
class MakePlatformAdmin extends Command
{
    protected $signature = 'make:platform-admin {email} {name} {password}';

    protected $description = 'Create a platform admin (no tenant, no roles) who can review center signups at /admin';

    public function handle(): int
    {
        $data = [
            'email' => $this->argument('email'),
            'name' => $this->argument('name'),
            'password' => $this->argument('password'),
        ];

        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'tenant_id' => null,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => 'نشط',
            'is_platform_admin' => true,
            'email_verified_at' => now(),
        ]);

        $this->info("Platform admin #{$user->id} created: {$user->email}. Sign in and open /admin.");

        return self::SUCCESS;
    }
}
