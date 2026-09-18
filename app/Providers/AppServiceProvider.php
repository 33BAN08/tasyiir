<?php

namespace App\Providers;

use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsureTenantUser;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Livewire replays only a fixed list of the original route's middleware
        // on its /livewire/update requests; make the permission check part of it.
        Livewire::addPersistentMiddleware([
            EnsurePlatformAdmin::class,
            EnsureTenantUser::class,
            PermissionMiddleware::class,
            RoleMiddleware::class,
            RoleOrPermissionMiddleware::class,
        ]);
    }
}
