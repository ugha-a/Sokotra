<?php

namespace App\Actions\Marketplace;

use App\Actions\Payments\StartCheckout;
use App\Enums\ConsentType;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\ContentPage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Returns the payment to redirect to, or null when the product is free and already delivered.
 */
class CreateOrderCheckout
{
    public function __construct(private StartCheckout $checkout, private OrderPaymentHandler $fulfil) {}

    public function handle(User $buyer, Product $product, ?string $ip): ?Payment
    {
        if ($product->status !== ProductStatus::Published) {
            abort(404);
        }

        if ($product->seller_id === $buyer->id) {
            throw ValidationException::withMessages(['order' => 'Kamu tidak bisa membeli produk sendiri.']);
        }

        if (Order::where('buyer_id', $buyer->id)->where('product_id', $product->id)->where('status', OrderStatus::Paid)->exists()) {
            throw ValidationException::withMessages(['order' => 'Kamu sudah memiliki produk ini. Lihat di Pembelian Saya.']);
        }

        $commission = (int) floor($product->price * Settings::get('marketplace.commission_percent') / 100);

        return DB::transaction(function () use ($buyer, $product, $commission, $ip) {
            $order = Order::create([
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
                'price_paid' => $product->price,
                'commission' => $commission,
                'seller_income' => $product->price - $commission,
                'status' => OrderStatus::PendingPayment,
            ]);

            $buyer->consents()->create([
                'type' => ConsentType::RiskDisclaimer,
                'version' => ContentPage::where('slug', 'disclaimer-risiko')->first()?->updated_at?->format('YmdHis') ?? 'unversioned',
                'accepted_at' => now(),
                'ip' => $ip,
            ]);

            if ($product->price === 0) {
                $this->fulfil->deliver($order);

                return null;
            }

            return $this->checkout->handle($buyer, $order, $product->price);
        });
    }
}
