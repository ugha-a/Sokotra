<?php

namespace App\Actions\Marketplace;

use App\Enums\KycStatus;
use App\Enums\ProductStatus;
use App\Enums\TraderStatus;
use App\Models\KycVerification;
use App\Models\Product;
use App\Models\TraderProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Create or edit a seller's product (docs/logic/marketplace.md → Alur Penjual).
 */
class SaveProduct
{
    public const FILE_DISK = 'local';

    public const IMAGE_DISK = 'public';

    public function blockReason(User $seller): ?string
    {
        $profile = TraderProfile::with('rank')->where('user_id', $seller->id)->first();

        return match (true) {
            $profile?->status !== TraderStatus::Approved => 'Akun trader kamu belum aktif.',
            ! ($profile->rank->can_sell_products ?? false) => 'Rank kamu belum bisa menjual produk di marketplace.',
            KycVerification::where('user_id', $seller->id)->first()?->status !== KycStatus::Verified => 'Verifikasi identitas (KYC) wajib untuk berjualan.',
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data  Validated fields without files.
     * @param  list<UploadedFile>  $screenshots
     */
    public function handle(User $seller, array $data, ?UploadedFile $file, array $screenshots, ?Product $product = null): Product
    {
        if ($product === null && ($reason = $this->blockReason($seller))) {
            throw ValidationException::withMessages(['product' => $reason]);
        }

        $attributes = $data;

        if ($file) {
            $attributes['file_path'] = $file->storeAs("products/{$seller->id}", Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), self::FILE_DISK);
        }

        if ($screenshots !== []) {
            $attributes['screenshots'] = array_map(fn (UploadedFile $image) => (string) $image->store("products/{$seller->id}/screenshots", self::IMAGE_DISK), $screenshots);
        }

        if ($product === null) {
            return Product::create([
                ...$attributes,
                'seller_id' => $seller->id,
                'slug' => $this->uniqueSlug((string) $data['name']),
                'screenshots' => $attributes['screenshots'] ?? [],
                'status' => ProductStatus::Draft,
            ]);
        }

        $previousFile = $product->file_path;
        $product->fill($attributes);
        $contentChanged = $product->isDirty(Product::CONTENT_FIELDS) || $file !== null || $screenshots !== [];

        if ($contentChanged && in_array($product->status, [ProductStatus::Published, ProductStatus::Unlisted], true)) {
            $product->fill(['status' => ProductStatus::InReview, 'submitted_at' => now()]);
        }

        $product->save();

        if ($file && $previousFile && $previousFile !== $product->file_path) {
            Storage::disk(self::FILE_DISK)->delete($previousFile);
        }

        return $product;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;

        for ($i = 2; Product::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
