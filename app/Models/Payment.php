<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * One gateway charge, shared by subscriptions and marketplace orders.
 *
 * @property int $id
 * @property int $user_id
 * @property string $payable_type
 * @property int $payable_id
 * @property int $amount
 * @property string $gateway
 * @property string $gateway_reference
 * @property string|null $checkout_url
 * @property PaymentStatus $status
 * @property Carbon|null $paid_at
 * @property Carbon|null $expires_at
 * @property array<string, mixed>|null $raw_payload
 * @property-read User $user
 * @property-read Model $payable
 */
#[Fillable(['user_id', 'payable_type', 'payable_id', 'amount', 'gateway', 'gateway_reference', 'checkout_url', 'status', 'paid_at', 'expires_at', 'raw_payload'])]
class Payment extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }
}
