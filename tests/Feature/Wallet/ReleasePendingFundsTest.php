<?php

use App\Actions\Wallet\PostTransaction;
use App\Actions\Wallet\ReleasePendingFunds;
use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Scheduling\Schedule;

it('releases matured pending income exactly once', function () {
    $user = User::factory()->create();
    $post = app(PostTransaction::class);
    $post->handle($user, WalletTransactionType::SubscriptionIncome, WalletBucket::Pending, 80000, 'subscription:1', null, now()->subDay());
    $post->handle($user, WalletTransactionType::MarketplaceIncome, WalletBucket::Pending, 50000, 'order:1', null, now()->addDays(3));

    app(ReleasePendingFunds::class)->handle();
    app(ReleasePendingFunds::class)->handle();

    $wallet = Wallet::firstOrFail();

    expect($wallet->available_balance)->toBe(80000)
        ->and($wallet->pending_balance)->toBe(50000);
});

it('runs hourly', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($event) => str_contains((string) $event->command, 'wallets:release'));

    expect($event?->expression)->toBe('0 * * * *');
});
