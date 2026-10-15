<?php

namespace App\Actions\Marketplace;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductReviewed;
use Illuminate\Validation\ValidationException;

class ReviewProduct
{
    public function __construct(private RecordAdminAudit $audit) {}

    public function approve(User $admin, Product $product): Product
    {
        $this->ensureInReview($product);
        $product->update(['status' => ProductStatus::Published, 'published_at' => $product->published_at ?? now(), 'rejection_reason' => null]);
        $this->audit->handle($admin, 'product.approved', $product, ['status' => 'in_review'], ['status' => 'published']);
        $product->seller->notify(new ProductReviewed($product));

        return $product;
    }

    public function reject(User $admin, Product $product, string $reason): Product
    {
        $this->ensureInReview($product);
        $product->update(['status' => ProductStatus::Rejected, 'rejection_reason' => $reason]);
        $this->audit->handle($admin, 'product.rejected', $product, ['status' => 'in_review'], ['status' => 'rejected', 'reason' => $reason]);
        $product->seller->notify(new ProductReviewed($product));

        return $product;
    }

    private function ensureInReview(Product $product): void
    {
        if ($product->status !== ProductStatus::InReview) {
            throw ValidationException::withMessages(['product' => 'Produk ini tidak sedang menunggu review.']);
        }
    }
}
