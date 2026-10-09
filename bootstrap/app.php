<?php

use App\Http\Middleware\EnsureLicensed;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsureTenantUser;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RequireSetup;
use App\Http\Middleware\RunDailyBackup;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Prepended: Laravel sorts `auth` by middleware priority, so an appended
        // guard would run after it and a fresh install would be sent to /login
        // instead of the first-run wizard.
        $middleware->web(prepend: [
            RequireSetup::class,
        ], append: [
            EnsureUserIsActive::class,
            SetLocale::class,
            RunDailyBackup::class,
        ]);

        $middleware->alias([
            'platform-admin' => EnsurePlatformAdmin::class,
            'licensed' => EnsureLicensed::class,
            'tenant-user' => EnsureTenantUser::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
