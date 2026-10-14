<?php

use App\Enums\Role;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\PlatformSettingController;
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

            Route::get('master/{resource}', [MasterDataController::class, 'index'])->name('master.index');
            Route::post('master/{resource}', [MasterDataController::class, 'store'])->name('master.store');
            Route::put('master/{resource}/{record}', [MasterDataController::class, 'update'])->whereNumber('record')->name('master.update');
            Route::patch('master/{resource}/{record}/toggle', [MasterDataController::class, 'toggle'])->whereNumber('record')->name('master.toggle');

            Route::get('settings', [PlatformSettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [PlatformSettingController::class, 'update'])->name('settings.update');

            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        });
});

require __DIR__.'/settings.php';
