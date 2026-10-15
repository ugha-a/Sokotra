<?php

namespace App\Actions\Wallet;

use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Collection;

/**
 * Moves held income from pending to available once its hold period ends.
 * A transaction counts as released when "release:{id}:in" exists, and as reversed
 * when "reverse:{id}" exists — the ledger itself is never updated.
 */
class ReleasePendingFunds
{
    public function __construct(private MoveBetweenBuckets $move) {}

    public function handle(): int
    {
        $released = 0;

        WalletTransaction::query()
            ->with('wallet.user')
            ->where('bucket', WalletBucket::Pending)
            ->whereIn('type', [WalletTransactionType::SubscriptionIncome, WalletTransactionType::MarketplaceIncome])
            ->where('amount', '>', 0)
            ->where('available_at', '<=', now())
            ->chunkById(200, function (Collection $transactions) use (&$released) {
                $settled = WalletTransaction::query()
                    ->whereIn('reference', $transactions->flatMap(fn (WalletTransaction $tx) => ["release:{$tx->id}:in", "reverse:{$tx->id}"]))
                    ->pluck('reference')
                    ->flip();

                foreach ($transactions as $transaction) {
                    /** @var WalletTransaction $transaction */
                    if ($settled->has("release:{$transaction->id}:in") || $settled->has("reverse:{$transaction->id}")) {
                        continue;
                    }

                    $this->move->handle(
                        $transaction->wallet->user,
                        WalletTransactionType::PendingRelease,
                        WalletBucket::Pending,
                        WalletBucket::Available,
                        $transaction->amount,
                        "release:{$transaction->id}",
                        $transaction->description,
                    );
                    $released++;
                }
            });

        return $released;
    }
}
