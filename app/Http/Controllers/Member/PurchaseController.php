<?php

namespace App\Http\Controllers\Member;

use App\Actions\Marketplace\ReviewPurchase;
use App\Enums\OrderStatus;
use App\Enums\ProductPlatform;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseController extends Controller
{
    private const LINK_MINUTES = 10;

    public function index(Request $request): Response
    {
        return Inertia::render('member/purchases', [
            'orders' => Order::query()
                ->with(['product.category', 'product.seller.traderProfile.rank', 'review'])
                ->where('buyer_id', $request->user()?->id)
                ->whereIn('status', [OrderStatus::Paid, OrderStatus::Refunded])
                ->latest('id')
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'product' => $order->product->summaryCard(),
                    'price_paid' => $order->price_paid,
                    'status' => $order->status->value,
                    'status_label' => $order->status->label(),
                    'license_key' => $order->status === OrderStatus::Paid ? $order->license_key : null,
                    'paid_at' => $order->paid_at?->toIso8601String(),
                    'review' => $order->review ? ['rating' => $order->review->rating, 'comment' => $order->review->comment] : null,
                ])->values()->all(),
        ]);
    }

    /** Fresh short-lived link on every click (docs/logic/marketplace.md → Alur Pembeli). */
    public function download(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeBuyer($request, $order);

        if ($order->product->platform === ProductPlatform::TradingView) {
            return redirect()->away((string) $order->product->tradingview_url);
        }

        return redirect()->to(URL::temporarySignedRoute('purchases.file', now()->addMinutes(self::LINK_MINUTES), ['order' => $order->id]));
    }

    public function file(Request $request, Order $order): StreamedResponse
    {
        $this->authorizeBuyer($request, $order);
        $path = $order->product->file_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        $name = str($order->product->name)->slug().'-'.$order->product->version.'.'.pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($path, $name);
    }

    public function review(Request $request, Order $order, ReviewPurchase $review): RedirectResponse
    {
        abort_unless($order->buyer_id === $request->user()?->id, 403);
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [], ['rating' => 'rating', 'comment' => 'ulasan']);

        $review->handle($order, (int) $validated['rating'], $validated['comment'] ?? null);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Terima kasih atas ulasanmu.']);

        return back();
    }

    private function authorizeBuyer(Request $request, Order $order): void
    {
        abort_unless($order->buyer_id === $request->user()?->id && $order->status === OrderStatus::Paid, 403);
    }
}
