<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Marketplace\RefundOrder;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/orders/index', [
            'orders' => Order::query()
                ->with(['buyer:id,name,email', 'product:id,name,seller_id', 'product.seller.traderProfile', 'payment'])
                ->whereIn('status', [OrderStatus::PendingPayment, OrderStatus::Paid, OrderStatus::Refunded])
                ->latest('id')
                ->paginate(25)
                ->through(fn (Order $order) => [
                    'id' => $order->id,
                    'buyer' => ['name' => $order->buyer->name, 'email' => $order->buyer->email],
                    'product' => $order->product->name,
                    'seller' => $order->product->seller->traderProfile->display_name ?? $order->product->seller->name,
                    'price_paid' => $order->price_paid,
                    'commission' => $order->commission,
                    'status' => $order->status->value,
                    'status_label' => $order->status->label(),
                    'paid_at' => $order->paid_at?->toIso8601String(),
                    'refund_reason' => $order->refund_reason,
                    'payment' => $order->payment ? [
                        'id' => $order->payment->id,
                        'gateway' => $order->payment->gateway,
                        'gateway_reference' => $order->payment->gateway_reference,
                        'status' => $order->payment->status->value,
                        'has_proof' => ! empty($order->payment->raw_payload['proof_path']),
                        'sender_name' => $order->payment->raw_payload['sender_name'] ?? null,
                        'sender_bank' => $order->payment->raw_payload['sender_bank'] ?? null,
                        'proof_url' => ! empty($order->payment->raw_payload['proof_path']) ? route('admin.payments.proof', $order->payment) : null,
                    ] : null,
                ]),
            'revenue' => ['commission' => (int) Order::where('status', OrderStatus::Paid)->sum('commission'), 'orders' => Order::where('status', OrderStatus::Paid)->count()],
        ]);
    }

    public function refund(Request $request, Order $order, RefundOrder $refund): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:255']], [], ['reason' => 'alasan'])['reason'];

        /** @var User $admin */
        $admin = $request->user();
        $refund->handle($admin, $order, $reason);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pesanan di-refund dan akses download dicabut.']);

        return back();
    }
}
