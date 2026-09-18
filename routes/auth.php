<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('login', 'pages.auth.login')->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', Logout::class)->name('logout');
});
