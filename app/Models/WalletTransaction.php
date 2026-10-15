<?php

namespace App\Models;

use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * Append-only ledger row (docs/logic/wallet-ledger.md).
 *
 * @property int $id
 * @property int $wallet_id
 * @property WalletTransactionType $type
 * @property WalletBucket $bucket
 * @property int $amount
 * @property int $balance_after
 * @property string $reference
 * @property string|null $description
 * @property Carbon|null $available_at
 * @property Carbon|null $created_at
 * @property-read Wallet $wallet
 */
#[Fillable(['wallet_id', 'type', 'bucket', 'amount', 'balance_after', 'reference', 'description', 'available_at'])]
class WalletTransaction extends Model
{
    public const UPDATED_AT = null;

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('The wallet ledger is append-only.'));
        static::deleting(fn () => throw new LogicException('The wallet ledger is append-only.'));
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => WalletTransactionType::class,
            'bucket' => WalletBucket::class,
            'amount' => 'integer',
            'balance_after' => 'integer',
            'available_at' => 'datetime',
        ];
    }
}
