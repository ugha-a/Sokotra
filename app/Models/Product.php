<?php

namespace App\Models;

use App\Enums\ProductPlatform;
use App\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Indicator / EA listing (docs/logic/marketplace.md).
 *
 * @property int $id
 * @property int $seller_id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string $summary
 * @property string $description
 * @property ProductPlatform $platform
 * @property int $price
 * @property string $version
 * @property string|null $file_path
 * @property string|null $tradingview_url
 * @property list<string> $screenshots
 * @property ProductStatus $status
 * @property string|null $rejection_reason
 * @property int $sales_count
 * @property string $rating_avg
 * @property int $rating_count
 * @property Carbon|null $submitted_at
 * @property Carbon|null $published_at
 * @property-read User $seller
 * @property-read ProductCategory $category
 */
#[Fillable(['seller_id', 'category_id', 'name', 'slug', 'summary', 'description', 'platform', 'price', 'version', 'file_path', 'tradingview_url', 'screenshots', 'status', 'rejection_reason', 'sales_count', 'rating_avg', 'rating_count', 'submitted_at', 'published_at'])]
#[Hidden(['file_path'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /** Fields whose change sends a published product back to review. */
    public const CONTENT_FIELDS = ['category_id', 'name', 'summary', 'description', 'platform', 'version', 'tradingview_url'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * @return HasMany<ProductReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * @return list<string>
     */
    public function screenshotUrls(): array
    {
        return array_map(fn (string $path) => Storage::disk('public')->url($path), $this->screenshots);
    }

    /**
     * Card data for listings.
     *
     * @return array<string, mixed>
     */
    public function summaryCard(): array
    {
        $profile = $this->seller->traderProfile;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'category' => $this->category->name,
            'platform' => $this->platform->value,
            'platform_label' => $this->platform->label(),
            'price' => $this->price,
            'version' => $this->version,
            'rating_avg' => (float) $this->rating_avg,
            'rating_count' => $this->rating_count,
            'sales_count' => $this->sales_count,
            'cover_url' => $this->screenshotUrls()[0] ?? null,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'seller' => [
                'name' => $profile->display_name ?? $this->seller->name,
                'slug' => $profile?->slug,
                'rank' => $profile?->rank ? ['name' => $profile->rank->name, 'color' => $profile->rank->color] : null,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => ProductPlatform::class,
            'price' => 'integer',
            'screenshots' => 'array',
            'status' => ProductStatus::class,
            'sales_count' => 'integer',
            'rating_avg' => 'decimal:2',
            'rating_count' => 'integer',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }
}
