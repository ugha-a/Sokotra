<?php

use App\Enums\Role;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('role:'.Role::Trader->value)
        ->prefix('trader')
        ->name('trader.')
        ->group(function () {
            Route::inertia('/', 'trader/dashboard')->name('dashboard');
        });

    Route::middleware('role:'.Role::Admin->value)
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::inertia('/', 'admin/dashboard')->name('dashboard');
        });
});

require __DIR__.'/settings.php';
