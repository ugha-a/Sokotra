<?php

namespace App\Actions\Wallet;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientBalance;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Manual correction of the available balance by an admin, always with a note.
 */
class AdjustWallet
{
    public function __construct(private PostTransaction $post, private RecordAdminAudit $audit) {}

    public function handle(User $admin, User $user, int $amount, string $note): WalletTransaction
    {
        try {
            $transaction = $this->post->handle($user, WalletTransactionType::AdminAdjustment, WalletBucket::Available, $amount, 'adjust:'.Str::uuid(), $note);
        } catch (InsufficientBalance) {
            throw ValidationException::withMessages(['amount' => 'Koreksi ini membuat saldo tersedia negatif.']);
        }

        $this->audit->handle($admin, 'wallet.adjusted', $transaction->wallet, null, ['amount' => $amount, 'note' => $note]);

        return $transaction;
    }
}
