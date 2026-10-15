<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $subscriber_id
 * @property int $trader_id
 * @property int $months
 * @property int $price_paid
 * @property int $platform_fee
 * @property int $trader_income
 * @property SubscriptionStatus $status
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $refunded_at
 * @property string|null $refund_reason
 * @property Carbon|null $created_at
 * @property-read User $subscriber
 * @property-read User $trader
 * @property-read Payment|null $payment
 */
#[Fillable(['subscriber_id', 'trader_id', 'months', 'price_paid', 'platform_fee', 'trader_income', 'status', 'starts_at', 'ends_at', 'refunded_at', 'refund_reason'])]
class Subscription extends Model
{
    public static function isActive(?User $subscriber, int $traderId): bool
    {
        return $subscriber !== null && self::query()->where('subscriber_id', $subscriber->id)->where('trader_id', $traderId)->currentlyActive()->exists();
    }

    /**
     * Paid and inside its period: starts_at ≤ now < ends_at.
     *
     * @param  Builder<Subscription>  $query
     */
    public function scopeCurrentlyActive(Builder $query): void
    {
        $query->where('status', SubscriptionStatus::Active)->where('starts_at', '<=', now())->where('ends_at', '>', now());
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    /**
     * @return MorphOne<Payment, $this>
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable')->latestOfMany();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'months' => 'integer',
            'price_paid' => 'integer',
            'platform_fee' => 'integer',
            'trader_income' => 'integer',
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }
}
