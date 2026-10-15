<?php

namespace App\Actions\Wallet;

use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientBalance;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * The only way money moves (docs/logic/wallet-ledger.md → Aturan Operasi).
 * Locks the wallet row, is idempotent per reference, and never lets a bucket go negative.
 */
class PostTransaction
{
    public function handle(
        User $user,
        WalletTransactionType $type,
        WalletBucket $bucket,
        int $amount,
        string $reference,
        ?string $description = null,
        ?CarbonInterface $availableAt = null,
    ): WalletTransaction {
        return DB::transaction(function () use ($user, $type, $bucket, $amount, $reference, $description, $availableAt) {
            Wallet::firstOrCreate(['user_id' => $user->id]);
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            $existing = WalletTransaction::where('reference', $reference)->first();

            if ($existing !== null) {
                return $existing;
            }

            $balance = $wallet->balance($bucket) + $amount;

            if ($balance < 0) {
                throw new InsufficientBalance("Saldo {$bucket->label()} tidak cukup.");
            }

            $wallet->update([$bucket->column() => $balance]);

            return $wallet->transactions()->create([
                'type' => $type,
                'bucket' => $bucket,
                'amount' => $amount,
                'balance_after' => $balance,
                'reference' => $reference,
                'description' => $description,
                'available_at' => $availableAt,
            ]);
        });
    }
}
