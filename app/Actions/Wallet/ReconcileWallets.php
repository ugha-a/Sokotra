<?php

namespace App\Actions\Wallet;

use App\Enums\WalletBucket;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Verifies that each cached bucket balance equals the sum of its ledger rows.
 */
class ReconcileWallets
{
    /**
     * @return list<array{user_id: int, bucket: string, cached: int, ledger: int}>
     */
    public function handle(): array
    {
        $sums = WalletTransaction::query()
            ->selectRaw('wallet_id, bucket, sum(amount) as total')
            ->groupBy('wallet_id', 'bucket')
            ->get()
            ->mapWithKeys(fn ($row) => ["{$row->wallet_id}:{$row->bucket->value}" => (int) $row->getAttribute('total')]);

        $mismatches = [];

        Wallet::query()->chunkById(500, function (Collection $wallets) use ($sums, &$mismatches) {
            foreach ($wallets as $wallet) {
                foreach (WalletBucket::cases() as $bucket) {
                    $ledger = $sums["{$wallet->id}:{$bucket->value}"] ?? 0;

                    if ($ledger !== $wallet->balance($bucket)) {
                        $mismatches[] = ['user_id' => $wallet->user_id, 'bucket' => $bucket->value, 'cached' => $wallet->balance($bucket), 'ledger' => $ledger];
                    }
                }
            }
        });

        if ($mismatches !== []) {
            Log::critical('Wallet balances differ from the ledger.', ['mismatches' => $mismatches]);
        }

        return $mismatches;
    }
}
