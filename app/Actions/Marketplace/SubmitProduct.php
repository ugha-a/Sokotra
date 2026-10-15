<?php

namespace App\Actions\Marketplace;

use App\Enums\ProductPlatform;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Support\ProhibitedClaims;
use Illuminate\Validation\ValidationException;

class SubmitProduct
{
    public function handle(Product $product): Product
    {
        if (! in_array($product->status, [ProductStatus::Draft, ProductStatus::Rejected], true)) {
            throw ValidationException::withMessages(['product' => 'Hanya draft atau produk yang ditolak yang bisa diajukan.']);
        }

        if ($product->platform !== ProductPlatform::TradingView && ! $product->file_path) {
            throw ValidationException::withMessages(['product' => 'Unggah file produk sebelum mengajukan review.']);
        }

        $claims = ProhibitedClaims::find("{$product->name} {$product->summary} {$product->description}");

        if ($claims !== []) {
            throw ValidationException::withMessages(['product' => 'Hapus klaim yang dilarang: '.implode(', ', $claims).'.']);
        }

        $product->update(['status' => ProductStatus::InReview, 'submitted_at' => now(), 'rejection_reason' => null]);

        return $product;
    }
}
