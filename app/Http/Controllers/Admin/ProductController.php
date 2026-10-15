<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Marketplace\ReviewProduct;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $status = ProductStatus::from($request->validate(['status' => ['nullable', Rule::enum(ProductStatus::class)]])['status'] ?? 'in_review');
        $counts = Product::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return Inertia::render('admin/products/index', [
            'status' => $status->value,
            'statuses' => array_map(fn (ProductStatus $case) => ['value' => $case->value, 'label' => $case->label(), 'count' => (int) ($counts[$case->value] ?? 0)], ProductStatus::cases()),
            'products' => Product::query()
                ->with(['category', 'seller.traderProfile.rank'])
                ->where('status', $status)
                ->oldest('submitted_at')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Product $product) => [
                    ...$product->summaryCard(),
                    'description' => $product->description,
                    'tradingview_url' => $product->tradingview_url,
                    'screenshot_urls' => $product->screenshotUrls(),
                    'rejection_reason' => $product->rejection_reason,
                    'file_url' => $product->file_path ? URL::temporarySignedRoute('admin.products.file', now()->addMinutes(30), ['product' => $product->id]) : null,
                ]),
        ]);
    }

    public function approve(Request $request, Product $product, ReviewProduct $review): RedirectResponse
    {
        $review->approve($this->admin($request), $product);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Produk ditayangkan.']);

        return back();
    }

    public function reject(Request $request, Product $product, ReviewProduct $review): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:500']], [], ['reason' => 'alasan'])['reason'];
        $review->reject($this->admin($request), $product, $reason);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Produk ditolak.']);

        return back();
    }

    public function file(Product $product): StreamedResponse
    {
        abort_unless($product->file_path && Storage::disk('local')->exists($product->file_path), 404);

        return Storage::disk('local')->download($product->file_path);
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
