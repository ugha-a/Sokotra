<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Subscription\RefundSubscription;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/subscriptions/index', [
            'subscriptions' => Subscription::query()
                ->with(['subscriber:id,name,email', 'trader.traderProfile', 'payment'])
                ->whereIn('status', [SubscriptionStatus::PendingPayment, SubscriptionStatus::Active, SubscriptionStatus::Expired, SubscriptionStatus::Refunded])
                ->latest('id')
                ->paginate(25)
                ->through(fn (Subscription $subscription) => [
                    'id' => $subscription->id,
                    'subscriber' => ['name' => $subscription->subscriber->name, 'email' => $subscription->subscriber->email],
                    'trader' => $subscription->trader->traderProfile->display_name ?? $subscription->trader->name,
                    'months' => $subscription->months,
                    'price_paid' => $subscription->price_paid,
                    'platform_fee' => $subscription->platform_fee,
                    'trader_income' => $subscription->trader_income,
                    'status' => $subscription->status->value,
                    'status_label' => $subscription->status->label(),
                    'starts_at' => $subscription->starts_at?->toIso8601String(),
                    'ends_at' => $subscription->ends_at?->toIso8601String(),
                    'refund_reason' => $subscription->refund_reason,
                    'payment' => $subscription->payment ? [
                        'id' => $subscription->payment->id,
                        'gateway' => $subscription->payment->gateway,
                        'gateway_reference' => $subscription->payment->gateway_reference,
                        'status' => $subscription->payment->status->value,
                        'has_proof' => ! empty($subscription->payment->raw_payload['proof_path']),
                        'sender_name' => $subscription->payment->raw_payload['sender_name'] ?? null,
                        'sender_bank' => $subscription->payment->raw_payload['sender_bank'] ?? null,
                        'proof_url' => ! empty($subscription->payment->raw_payload['proof_path']) ? route('admin.payments.proof', $subscription->payment) : null,
                    ] : null,
                ]),
            'revenue' => [
                'platform_fee' => (int) Subscription::whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Expired])->sum('platform_fee'),
                'active' => Subscription::query()->currentlyActive()->count(),
            ],
        ]);
    }

    public function refund(Request $request, Subscription $subscription, RefundSubscription $refund): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:255']], [], ['reason' => 'alasan'])['reason'];

        /** @var User $admin */
        $admin = $request->user();
        $refund->handle($admin, $subscription, $reason);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Langganan di-refund dan akses dicabut.']);

        return back();
    }
}
