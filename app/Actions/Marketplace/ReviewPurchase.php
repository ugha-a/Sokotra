<?php

namespace App\Actions\Marketplace;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * One review per paid order; the product rating is recomputed from all reviews.
 */
class ReviewPurchase
{
    public function handle(Order $order, int $rating, ?string $comment): ProductReview
    {
        if ($order->status !== OrderStatus::Paid) {
            throw ValidationException::withMessages(['review' => 'Ulasan hanya untuk pembelian yang sudah dibayar.']);
        }

        if (ProductReview::where('order_id', $order->id)->exists()) {
            throw ValidationException::withMessages(['review' => 'Kamu sudah memberi ulasan untuk pembelian ini.']);
        }

        return DB::transaction(function () use ($order, $rating, $comment) {
            $review = ProductReview::create([
                'order_id' => $order->id,
                'product_id' => $order->product_id,
                'user_id' => $order->buyer_id,
                'rating' => $rating,
                'comment' => $comment,
            ]);

            $order->product->update([
                'rating_avg' => round((float) ProductReview::where('product_id', $order->product_id)->avg('rating'), 2),
                'rating_count' => ProductReview::where('product_id', $order->product_id)->count(),
            ]);

            return $review;
        });
    }
}
