<?php

namespace App\Models;

use App\Enums\WalletBucket;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cached balances; the ledger (wallet_transactions) is the source of truth
 * (docs/logic/wallet-ledger.md). Only App\Actions\Wallet\PostTransaction writes here.
 *
 * @property int $id
 * @property int $user_id
 * @property int $available_balance
 * @property int $pending_balance
 * @property int $locked_balance
 * @property-read User $user
 */
#[Fillable(['user_id', 'available_balance', 'pending_balance', 'locked_balance'])]
class Wallet extends Model
{
    public function balance(WalletBucket $bucket): int
    {
        return (int) $this->getAttribute($bucket->column());
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<WalletTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'available_balance' => $this->available_balance,
            'pending_balance' => $this->pending_balance,
            'locked_balance' => $this->locked_balance,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'available_balance' => 'integer',
            'pending_balance' => 'integer',
            'locked_balance' => 'integer',
        ];
    }
}
