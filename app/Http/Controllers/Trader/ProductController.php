<?php

namespace App\Http\Controllers\Trader;

use App\Actions\Marketplace\SaveProduct;
use App\Actions\Marketplace\SubmitProduct;
use App\Enums\ProductPlatform;
use App\Http\Controllers\Controller;
use App\Http\Requests\Trader\SaveProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request, SaveProduct $save): Response
    {
        $seller = $this->user($request);

        return Inertia::render('trader/products/index', [
            'canSell' => $save->blockReason($seller) === null,
            'blockReason' => $save->blockReason($seller),
            'products' => Product::query()->with(['category', 'seller.traderProfile.rank'])->where('seller_id', $seller->id)->latest('id')->get()
                ->map(fn (Product $product) => [...$product->summaryCard(), 'rejection_reason' => $product->rejection_reason])
                ->values()->all(),
            'commissionPercent' => Settings::get('marketplace.commission_percent'),
        ]);
    }

    public function create(Request $request, SaveProduct $save): Response
    {
        return $this->form($request, $save, null);
    }

    public function edit(Request $request, Product $product, SaveProduct $save): Response
    {
        $this->authorizeOwner($request, $product);

        return $this->form($request, $save, $product);
    }

    public function store(SaveProductRequest $request, SaveProduct $save): RedirectResponse
    {
        $product = $save->handle($this->user($request), $this->fields($request), $request->file('file'), $this->screenshots($request));
        $this->flash('Produk disimpan sebagai draft. Ajukan review saat siap.');

        return to_route('trader.products.edit', $product);
    }

    public function update(SaveProductRequest $request, Product $product, SaveProduct $save): RedirectResponse
    {
        $this->authorizeOwner($request, $product);
        $save->handle($this->user($request), $this->fields($request), $request->file('file'), $this->screenshots($request), $product);
        $this->flash('Produk diperbarui.');

        return back();
    }

    public function submit(Request $request, Product $product, SubmitProduct $submit): RedirectResponse
    {
        $this->authorizeOwner($request, $product);
        $submit->handle($product);
        $this->flash('Produk diajukan untuk review admin.');

        return back();
    }

    private function form(Request $request, SaveProduct $save, ?Product $product): Response
    {
        return Inertia::render('trader/products/edit', [
            'product' => $product ? [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'name' => $product->name,
                'summary' => $product->summary,
                'description' => $product->description,
                'platform' => $product->platform->value,
                'price' => $product->price,
                'version' => $product->version,
                'tradingview_url' => $product->tradingview_url,
                'has_file' => $product->file_path !== null,
                'screenshot_urls' => $product->screenshotUrls(),
                'status' => $product->status->value,
                'status_label' => $product->status->label(),
                'rejection_reason' => $product->rejection_reason,
            ] : null,
            'blockReason' => $product ? null : $save->blockReason($this->user($request)),
            'categories' => ProductCategory::query()->where('is_active', true)->orderBy('name')->get()
                ->map(fn (ProductCategory $category) => ['value' => $category->id, 'label' => $category->name])->values()->all(),
            'platforms' => ProductPlatform::options(),
            'maxFileMb' => Settings::get('marketplace.max_file_mb'),
            'commissionPercent' => Settings::get('marketplace.commission_percent'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fields(SaveProductRequest $request): array
    {
        return $request->safe()->except(['file', 'screenshots']);
    }

    /**
     * @return list<UploadedFile>
     */
    private function screenshots(SaveProductRequest $request): array
    {
        return array_values(array_filter((array) $request->file('screenshots', [])));
    }

    private function authorizeOwner(Request $request, Product $product): void
    {
        abort_unless($product->seller_id === $request->user()?->id, 403);
    }

    private function user(Request $request): User
    {
        /** @var User */
        return $request->user();
    }

    private function flash(string $message): void
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);
    }
}
