<?php

namespace App\Http\Controllers\Member;

use App\Actions\Marketplace\CreateOrderCheckout;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function store(Request $request, Product $product, CreateOrderCheckout $checkout): RedirectResponse|Response
    {
        $request->validate(['accept_risk' => ['accepted']], ['accept_risk.accepted' => 'Kamu perlu menyetujui disclaimer risiko.']);

        /** @var User $buyer */
        $buyer = $request->user();
        $payment = $checkout->handle($buyer, $product, $request->ip());

        if ($payment === null) {
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Produk gratis sudah ditambahkan ke Pembelian Saya.']);

            return to_route('purchases.index');
        }

        return Inertia::location((string) $payment->checkout_url);
    }
}
