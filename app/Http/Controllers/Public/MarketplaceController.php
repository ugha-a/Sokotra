<?php

namespace App\Http\Controllers\Public;

use App\Enums\OrderStatus;
use App\Enums\ProductPlatform;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductReview;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'exists:product_categories,slug'],
            'platform' => ['nullable', Rule::enum(ProductPlatform::class)],
            'sort' => ['nullable', 'in:popular,newest,rating,price_low'],
        ]);

        $sort = $filters['sort'] ?? 'popular';

        return Inertia::render('public/marketplace/index', [
            'filters' => ['category' => $filters['category'] ?? null, 'platform' => $filters['platform'] ?? null, 'sort' => $sort],
            'categories' => ProductCategory::query()->where('is_active', true)->orderBy('name')->get()
                ->map(fn (ProductCategory $category) => ['value' => $category->slug, 'label' => $category->name])->values()->all(),
            'platforms' => ProductPlatform::options(),
            'products' => Product::query()
                ->with(['category', 'seller.traderProfile.rank'])
                ->where('status', ProductStatus::Published)
                ->when($filters['category'] ?? null, fn (Builder $query, string $slug) => $query->whereRelation('category', 'slug', $slug))
                ->when($filters['platform'] ?? null, fn (Builder $query, string $platform) => $query->where('platform', $platform))
                ->when($sort === 'popular', fn (Builder $query) => $query->orderByDesc('sales_count'))
                ->when($sort === 'newest', fn (Builder $query) => $query->orderByDesc('published_at'))
                ->when($sort === 'rating', fn (Builder $query) => $query->orderByDesc('rating_avg'))
                ->when($sort === 'price_low', fn (Builder $query) => $query->orderBy('price'))
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString()
                ->through(fn (Product $product) => $product->summaryCard()),
        ]);
    }

    public function show(Request $request, Product $product): Response
    {
        abort_unless($product->status === ProductStatus::Published, 404);
        $product->load(['category', 'seller.traderProfile.rank']);
        $buyer = $request->user();

        return Inertia::render('public/marketplace/show', [
            'product' => [
                ...$product->summaryCard(),
                'description' => $product->description,
                'screenshot_urls' => $product->screenshotUrls(),
                'published_at' => $product->published_at?->toIso8601String(),
            ],
            'reviews' => ProductReview::query()->with('user:id,name')->where('product_id', $product->id)->latest('id')->limit(20)->get()
                ->map(fn (ProductReview $review) => [
                    'id' => $review->id,
                    'name' => $review->user->name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at?->toIso8601String(),
                ])->values()->all(),
            'owned' => $buyer !== null && Order::where('buyer_id', $buyer->id)->where('product_id', $product->id)->where('status', OrderStatus::Paid)->exists(),
            'isOwnProduct' => $buyer?->id === $product->seller_id,
            'riskDisclaimer' => Settings::get('content.risk_disclaimer'),
        ]);
    }
}
