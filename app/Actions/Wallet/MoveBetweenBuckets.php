<?php

namespace App\Actions\Wallet;

use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * A bucket transfer is two ledger rows: "{reference}:out" and "{reference}:in".
 */
class MoveBetweenBuckets
{
    public function __construct(private PostTransaction $post) {}

    public function handle(
        User $user,
        WalletTransactionType $type,
        WalletBucket $from,
        WalletBucket $to,
        int $amount,
        string $reference,
        ?string $description = null,
    ): void {
        DB::transaction(function () use ($user, $type, $from, $to, $amount, $reference, $description) {
            $this->post->handle($user, $type, $from, -$amount, "{$reference}:out", $description);
            $this->post->handle($user, $type, $to, $amount, "{$reference}:in", $description);
        });
    }
}
