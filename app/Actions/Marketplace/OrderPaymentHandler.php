<?php

namespace App\Actions\Marketplace;

use App\Actions\Payments\HandlesPayment;
use App\Actions\Wallet\PostTransaction;
use App\Enums\OrderStatus;
use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Models\Order;
use App\Models\Payment;
use App\Notifications\ProductSold;
use App\Support\Settings;
use Illuminate\Support\Str;

class OrderPaymentHandler implements HandlesPayment
{
    public function __construct(private PostTransaction $post) {}

    public function paid(Payment $payment): void
    {
        /** @var Order $order */
        $order = $payment->payable;
        $this->deliver($order);
    }

    public function failed(Payment $payment): void
    {
        $payment->payable->update(['status' => OrderStatus::Cancelled]);
    }

    /**
     * Mark paid, issue the license, credit the seller (held) and count the sale.
     */
    public function deliver(Order $order): void
    {
        $order->update(['status' => OrderStatus::Paid, 'paid_at' => now(), 'license_key' => self::licenseKey()]);
        $order->product->increment('sales_count');

        if ($order->seller_income > 0) {
            $this->post->handle(
                $order->product->seller,
                WalletTransactionType::MarketplaceIncome,
                WalletBucket::Pending,
                $order->seller_income,
                "order:{$order->id}",
                "Penjualan {$order->product->name}",
                now()->addDays(Settings::get('wallet.hold_period_days')),
            );
        }

        $order->product->seller->notify(new ProductSold($order));
    }

    public static function licenseKey(): string
    {
        $blocks = array_map(fn () => Str::upper(Str::random(4)), range(1, 4));

        return 'SKT-'.implode('-', array_map(fn (string $block) => preg_replace('/[^A-Z0-9]/', 'X', $block), $blocks));
    }
}
