<?php

use App\Enums\Permission;
use App\Enums\Role;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BrokerAccountController as AdminBrokerAccountController;
use App\Http\Controllers\Admin\BrokerRebateController;
use App\Http\Controllers\Admin\KycDocumentController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PayrollController as AdminPayrollController;
use App\Http\Controllers\Admin\PlatformSettingController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\TraderApplicationController;
use App\Http\Controllers\Admin\WalletController as AdminWalletController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Member\BecomeTraderController;
use App\Http\Controllers\Member\BrokerAccountController as MemberBrokerAccountController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\FollowController;
use App\Http\Controllers\Member\FollowingController;
use App\Http\Controllers\Member\OrderController;
use App\Http\Controllers\Member\PurchaseController;
use App\Http\Controllers\Member\SubscriptionController as MemberSubscriptionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Public\BrokerController;
use App\Http\Controllers\Public\LeaderboardController;
use App\Http\Controllers\Public\MarketplaceController;
use App\Http\Controllers\Public\TraderProfileController;
use App\Http\Controllers\SignalFeedController;
use App\Http\Controllers\Trader\ChannelController;
use App\Http\Controllers\Trader\DashboardController as TraderDashboardController;
use App\Http\Controllers\Trader\PayrollController as TraderPayrollController;
use App\Http\Controllers\Trader\ProductController as TraderProductController;
use App\Http\Controllers\Trader\QuoteController;
use App\Http\Controllers\Trader\SignalController;
use App\Http\Controllers\Wallet\BankAccountController;
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\Wallet\WithdrawalController;
use App\Models\KycVerification;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::get('leaderboard', LeaderboardController::class)->name('leaderboard');
Route::get('traders/{profile:slug}', [TraderProfileController::class, 'show'])->name('traders.show');
Route::post('webhooks/payments/{gateway}', [PaymentController::class, 'webhook'])->name('webhooks.payments');
Route::get('brokers', [BrokerController::class, 'index'])->name('brokers.index');
Route::get('marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('marketplace/{product:slug}', [MarketplaceController::class, 'show'])->name('marketplace.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', MemberDashboardController::class)->name('dashboard');
    Route::get('following', [FollowingController::class, 'index'])->name('following.index');
    Route::post('traders/{profile}/follow', [FollowController::class, 'store'])->name('traders.follow');
    Route::delete('traders/{profile}/follow', [FollowController::class, 'destroy'])->name('traders.unfollow');

    Route::get('subscriptions', [MemberSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('traders/{profile}/subscribe', [MemberSubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('traders/{profile}/subscribe', [MemberSubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('payments/{payment}/simulate', [PaymentController::class, 'simulate'])->name('payments.simulate.show');
    Route::post('payments/{payment}/simulate', [PaymentController::class, 'completeSimulation'])->name('payments.simulate.complete');

    Route::get('brokers/{broker}/open', [BrokerController::class, 'open'])->name('brokers.open');
    Route::get('broker-accounts', [MemberBrokerAccountController::class, 'index'])->name('broker-accounts.index');
    Route::post('broker-accounts', [MemberBrokerAccountController::class, 'store'])->name('broker-accounts.store');

    Route::post('marketplace/{product}/buy', [OrderController::class, 'store'])->name('orders.store');
    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('purchases/{order}/download', [PurchaseController::class, 'download'])->name('purchases.download');
    Route::get('purchases/{order}/file', [PurchaseController::class, 'file'])->middleware('signed')->name('purchases.file');
    Route::post('purchases/{order}/review', [PurchaseController::class, 'review'])->name('purchases.review');

    Route::get('wallet', [WalletController::class, 'show'])->name('wallet.show');
    Route::post('wallet/bank-accounts', [BankAccountController::class, 'store'])->name('wallet.bank-accounts.store');
    Route::delete('wallet/bank-accounts/{bankAccount}', [BankAccountController::class, 'destroy'])->name('wallet.bank-accounts.destroy');
    Route::post('wallet/withdrawals', [WithdrawalController::class, 'store'])->name('wallet.withdrawals.store');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::get('signals', [SignalFeedController::class, 'index'])->name('signals.index');
    Route::get('signals/{signal}/chart', [SignalFeedController::class, 'chart'])->name('signals.chart');

    Route::get('become-trader', [BecomeTraderController::class, 'show'])->name('become-trader.show');
    Route::post('become-trader', [BecomeTraderController::class, 'store'])->name('become-trader.store');

    Route::middleware('role:'.Role::Trader->value)
        ->prefix('trader')
        ->name('trader.')
        ->group(function () {
            Route::get('/', TraderDashboardController::class)->name('dashboard');

            Route::get('signals', [SignalController::class, 'index'])->name('signals.index');
            Route::get('signals/create', [SignalController::class, 'create'])->name('signals.create');
            Route::post('signals', [SignalController::class, 'store'])->name('signals.store');
            Route::post('signals/{signal}/cancel', [SignalController::class, 'cancel'])->name('signals.cancel');

            Route::get('quotes/{instrument}', [QuoteController::class, 'show'])->name('quotes.show');
            Route::get('payroll', TraderPayrollController::class)->name('payroll');
            Route::get('products', [TraderProductController::class, 'index'])->name('products.index');
            Route::get('products/create', [TraderProductController::class, 'create'])->name('products.create');
            Route::post('products', [TraderProductController::class, 'store'])->name('products.store');
            Route::get('products/{product}/edit', [TraderProductController::class, 'edit'])->name('products.edit');
            Route::put('products/{product}', [TraderProductController::class, 'update'])->name('products.update');
            Route::post('products/{product}/submit', [TraderProductController::class, 'submit'])->name('products.submit');

            Route::get('channel', [ChannelController::class, 'show'])->name('channel');
            Route::put('channel', [ChannelController::class, 'update'])->name('channel.update');
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

            Route::get('payroll', [AdminPayrollController::class, 'index'])->name('payroll.index');
            Route::post('payroll', [AdminPayrollController::class, 'generate'])->name('payroll.generate');
            Route::get('payroll/{period}', [AdminPayrollController::class, 'show'])->name('payroll.show');
            Route::post('payroll/{period}/approve', [AdminPayrollController::class, 'approve'])->name('payroll.approve');
            Route::post('payroll/items/{item}/hold', [AdminPayrollController::class, 'hold'])->name('payroll.items.hold');
            Route::post('payroll/items/{item}/release', [AdminPayrollController::class, 'release'])->name('payroll.items.release');

            Route::get('broker-accounts', [AdminBrokerAccountController::class, 'index'])->name('broker-accounts.index');
            Route::post('broker-accounts/{account}/link', [AdminBrokerAccountController::class, 'link'])->name('broker-accounts.link');
            Route::post('broker-accounts/{account}/reject', [AdminBrokerAccountController::class, 'reject'])->name('broker-accounts.reject');
            Route::get('broker-rebates', [BrokerRebateController::class, 'index'])->name('broker-rebates.index');
            Route::post('broker-rebates', [BrokerRebateController::class, 'import'])->name('broker-rebates.import');

            Route::get('products', [AdminProductController::class, 'index'])->name('products.index');
            Route::post('products/{product}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
            Route::post('products/{product}/reject', [AdminProductController::class, 'reject'])->name('products.reject');
            Route::get('products/{product}/file', [AdminProductController::class, 'file'])->middleware('signed')->name('products.file');
            Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::post('orders/{order}/refund', [AdminOrderController::class, 'refund'])->name('orders.refund');

            Route::get('subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
            Route::post('subscriptions/{subscription}/refund', [AdminSubscriptionController::class, 'refund'])->name('subscriptions.refund');

            Route::get('withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
            Route::post('withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
            Route::post('withdrawals/{withdrawal}/paid', [AdminWithdrawalController::class, 'paid'])->name('withdrawals.paid');
            Route::post('withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
            Route::get('wallets', [AdminWalletController::class, 'index'])->name('wallets.index');
            Route::post('wallets/{user}/adjust', [AdminWalletController::class, 'adjust'])->name('wallets.adjust');

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
