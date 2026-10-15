<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $buyer_id
 * @property int $product_id
 * @property int $price_paid
 * @property int $commission
 * @property int $seller_income
 * @property OrderStatus $status
 * @property string|null $license_key
 * @property Carbon|null $paid_at
 * @property Carbon|null $refunded_at
 * @property string|null $refund_reason
 * @property Carbon|null $created_at
 * @property-read User $buyer
 * @property-read Product $product
 * @property-read Payment|null $payment
 * @property-read ProductReview|null $review
 */
#[Fillable(['buyer_id', 'product_id', 'price_paid', 'commission', 'seller_income', 'status', 'license_key', 'paid_at', 'refunded_at', 'refund_reason'])]
class Order extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return MorphOne<Payment, $this>
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable')->latestOfMany();
    }

    /**
     * @return HasOne<ProductReview, $this>
     */
    public function review(): HasOne
    {
        return $this->hasOne(ProductReview::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_paid' => 'integer',
            'commission' => 'integer',
            'seller_income' => 'integer',
            'status' => OrderStatus::class,
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }
}
