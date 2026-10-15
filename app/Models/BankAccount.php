<?php

namespace App\Models;

use App\Support\Nik;
use Database\Factories\BankAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $bank_id
 * @property string $account_number
 * @property string $account_name
 * @property bool $is_verified
 * @property-read User $user
 * @property-read Bank $bank
 */
#[Fillable(['user_id', 'bank_id', 'account_number', 'account_name', 'is_verified'])]
#[Hidden(['account_number'])]
class BankAccount extends Model
{
    /** @use HasFactory<BankAccountFactory> */
    use HasFactory;

    public function maskedNumber(): string
    {
        return Nik::mask($this->account_number);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Bank, $this>
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'id' => $this->id,
            'bank' => $this->bank->name,
            'account_number' => $this->maskedNumber(),
            'account_name' => $this->account_name,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_number' => 'encrypted',
            'is_verified' => 'boolean',
        ];
    }
}
