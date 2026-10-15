<?php

use App\Actions\Wallet\MoveBetweenBuckets;
use App\Actions\Wallet\PostTransaction;
use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientBalance;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->post = app(PostTransaction::class);
});

it('creates the wallet on first use and records the balance after', function () {
    $tx = $this->post->handle($this->user, WalletTransactionType::PayrollCredit, WalletBucket::Available, 500000, 'payroll_item:1', 'Gaji Oktober');

    $wallet = Wallet::where('user_id', $this->user->id)->firstOrFail();

    expect($wallet->available_balance)->toBe(500000)
        ->and($wallet->pending_balance)->toBe(0)
        ->and($tx->balance_after)->toBe(500000)
        ->and($tx->amount)->toBe(500000)
        ->and($tx->type)->toBe(WalletTransactionType::PayrollCredit);
});

it('is idempotent per reference', function () {
    $first = $this->post->handle($this->user, WalletTransactionType::PayrollCredit, WalletBucket::Available, 500000, 'payroll_item:1');
    $second = $this->post->handle($this->user, WalletTransactionType::PayrollCredit, WalletBucket::Available, 500000, 'payroll_item:1');

    expect($second->id)->toBe($first->id)
        ->and(WalletTransaction::count())->toBe(1)
        ->and(Wallet::firstOrFail()->available_balance)->toBe(500000);
});

it('never lets a bucket go negative', function () {
    credit($this->user, 1000);

    $this->post->handle($this->user, WalletTransactionType::AdminAdjustment, WalletBucket::Available, -2000, 'adjust:1', 'Koreksi');
})->throws(InsufficientBalance::class);

it('rolls back a failed debit', function () {
    credit($this->user, 1000);

    try {
        $this->post->handle($this->user, WalletTransactionType::AdminAdjustment, WalletBucket::Available, -2000, 'adjust:1', 'Koreksi');
    } catch (InsufficientBalance) {
    }

    expect(WalletTransaction::count())->toBe(1)
        ->and(Wallet::firstOrFail()->available_balance)->toBe(1000);
});

it('holds income in the pending bucket until it is available', function () {
    $tx = $this->post->handle($this->user, WalletTransactionType::SubscriptionIncome, WalletBucket::Pending, 80000, 'subscription:5', null, now()->addDays(7));

    expect(Wallet::firstOrFail()->pending_balance)->toBe(80000)
        ->and($tx->available_at->isFuture())->toBeTrue();
});

it('moves money between buckets with two ledger rows', function () {
    credit($this->user, 300000);

    app(MoveBetweenBuckets::class)->handle($this->user, WalletTransactionType::WithdrawLock, WalletBucket::Available, WalletBucket::Locked, 106500, 'withdrawal:1');

    $wallet = Wallet::firstOrFail();

    expect($wallet->available_balance)->toBe(193500)
        ->and($wallet->locked_balance)->toBe(106500)
        ->and(WalletTransaction::where('reference', 'like', 'withdrawal:1:%')->pluck('reference')->sort()->values()->all())
        ->toBe(['withdrawal:1:in', 'withdrawal:1:out']);
});

it('keeps the ledger append-only', function () {
    credit($this->user, 1000)->update(['amount' => 999999]);
})->throws(LogicException::class);
