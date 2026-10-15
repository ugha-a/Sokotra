<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Database\Factories\WithdrawalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $bank_account_id
 * @property int $amount
 * @property int $fee
 * @property int $net_amount
 * @property WithdrawalStatus $status
 * @property bool $name_mismatch
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $paid_at
 * @property string|null $rejection_reason
 * @property string|null $disbursement_reference
 * @property Carbon|null $created_at
 * @property-read User $user
 * @property-read BankAccount $bankAccount
 */
#[Fillable(['user_id', 'bank_account_id', 'amount', 'fee', 'net_amount', 'status', 'name_mismatch', 'reviewed_by', 'reviewed_at', 'paid_at', 'rejection_reason', 'disbursement_reference'])]
class Withdrawal extends Model
{
    /** @use HasFactory<WithdrawalFactory> */
    use HasFactory;

    /** Ledger reference shared by the lock, unlock and paid entries. */
    public function reference(): string
    {
        return "withdrawal:{$this->id}";
    }

    /** Amount moved out of the available balance: the transfer plus the fee. */
    public function total(): int
    {
        return $this->amount + $this->fee;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<BankAccount, $this>
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'fee' => 'integer',
            'net_amount' => 'integer',
            'status' => WithdrawalStatus::class,
            'name_mismatch' => 'boolean',
            'reviewed_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
}
