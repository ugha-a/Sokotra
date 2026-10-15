<?php

namespace App\Actions\Marketplace;

use App\Actions\Audit\RecordAdminAudit;
use App\Actions\Wallet\PostTransaction;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientBalance;
use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Same rule as subscriptions: refunds only while the seller income is still held.
 */
class RefundOrder
{
    public function __construct(private PostTransaction $post, private RecordAdminAudit $audit) {}

    public function handle(User $admin, Order $order, string $reason): Order
    {
        if ($order->status !== OrderStatus::Paid) {
            throw ValidationException::withMessages(['order' => 'Hanya pesanan yang sudah dibayar yang bisa di-refund.']);
        }

        $income = WalletTransaction::where('reference', "order:{$order->id}")->first();

        if ($income && WalletTransaction::where('reference', "release:{$income->id}:in")->exists()) {
            throw ValidationException::withMessages(['order' => 'Pendapatan penjual sudah dirilis, refund otomatis tidak tersedia.']);
        }

        DB::transaction(function () use ($admin, $order, $reason, $income) {
            if ($income) {
                try {
                    $this->post->handle($order->product->seller, WalletTransactionType::RefundReversal, WalletBucket::Pending, -$income->amount, "reverse:{$income->id}", "Refund pesanan #{$order->id}");
                } catch (InsufficientBalance) {
                    throw ValidationException::withMessages(['order' => 'Saldo tertahan penjual tidak cukup untuk refund.']);
                }
            }

            $order->update(['status' => OrderStatus::Refunded, 'refunded_at' => now(), 'refund_reason' => $reason]);
            $order->payment?->update(['status' => PaymentStatus::Refunded]);
            $order->product->decrement('sales_count');
            $this->audit->handle($admin, 'order.refunded', $order, ['status' => 'paid'], ['status' => 'refunded', 'reason' => $reason]);
        });

        return $order;
    }
}
