<?php

use App\Enums\Permission;
use App\Enums\Role;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\KycDocumentController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\PlatformSettingController;
use App\Http\Controllers\Admin\TraderApplicationController;
use App\Http\Controllers\Member\BecomeTraderController;
use App\Models\KycVerification;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('become-trader', [BecomeTraderController::class, 'show'])->name('become-trader.show');
    Route::post('become-trader', [BecomeTraderController::class, 'store'])->name('become-trader.store');

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

            Route::get('traders', [TraderApplicationController::class, 'index'])->name('traders.index');
            Route::get('traders/{profile}', [TraderApplicationController::class, 'show'])->name('traders.show');
            Route::post('traders/{profile}/approve', [TraderApplicationController::class, 'approve'])->name('traders.approve');
            Route::post('traders/{profile}/reject', [TraderApplicationController::class, 'reject'])->name('traders.reject');
            Route::post('traders/{profile}/suspend', [TraderApplicationController::class, 'suspend'])->name('traders.suspend');
            Route::post('traders/{profile}/reinstate', [TraderApplicationController::class, 'reinstate'])->name('traders.reinstate');

            Route::middleware('permission:'.Permission::KycReview->value)->group(function () {
                Route::post('traders/{profile}/kyc/verify', [TraderApplicationController::class, 'verifyKyc'])->name('traders.kyc.verify');
                Route::post('traders/{profile}/kyc/reject', [TraderApplicationController::class, 'rejectKyc'])->name('traders.kyc.reject');
                Route::get('kyc/{kyc}/documents/{document}', KycDocumentController::class)
                    ->whereIn('document', array_keys(KycVerification::DOCUMENTS))
                    ->middleware('signed')
                    ->name('kyc.document');
            });
        });
});

require __DIR__.'/settings.php';
