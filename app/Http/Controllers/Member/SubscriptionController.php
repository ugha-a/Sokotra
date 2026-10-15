<?php

namespace App\Http\Controllers\Member;

use App\Actions\Subscription\CreateSubscriptionCheckout;
use App\Enums\SubscriptionStatus;
use App\Enums\TraderStatus;
use App\Http\Controllers\Controller;
use App\Models\ChannelPlan;
use App\Models\Subscription;
use App\Models\TraderProfile;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class SubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('member/subscriptions', [
            'subscriptions' => Subscription::query()
                ->with('trader.traderProfile')
                ->where('subscriber_id', $request->user()?->id)
                ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Expired, SubscriptionStatus::Refunded])
                ->latest('id')
                ->get()
                ->map(fn (Subscription $subscription) => [
                    'id' => $subscription->id,
                    'trader' => [
                        'name' => $subscription->trader->traderProfile->display_name ?? $subscription->trader->name,
                        'slug' => $subscription->trader->traderProfile?->slug,
                        'profile_id' => $subscription->trader->traderProfile?->id,
                    ],
                    'months' => $subscription->months,
                    'price_paid' => $subscription->price_paid,
                    'status' => $subscription->status->value,
                    'status_label' => $subscription->status->label(),
                    'starts_at' => $subscription->starts_at?->toIso8601String(),
                    'ends_at' => $subscription->ends_at?->toIso8601String(),
                ])->values()->all(),
        ]);
    }

    public function create(TraderProfile $profile): Response
    {
        abort_unless($profile->status === TraderStatus::Approved, 404);
        $plan = ChannelPlan::where('trader_id', $profile->user_id)->first();

        return Inertia::render('member/subscribe', [
            'trader' => ['id' => $profile->id, 'name' => $profile->display_name, 'slug' => $profile->slug],
            'plan' => $plan ? ['monthly_price' => $plan->monthly_price, 'description' => $plan->description, 'is_open' => $plan->is_open] : null,
            'durations' => Settings::get('subscription.durations'),
            'riskDisclaimer' => Settings::get('content.risk_disclaimer'),
        ]);
    }

    public function store(Request $request, TraderProfile $profile, CreateSubscriptionCheckout $checkout): RedirectResponse|HttpResponse
    {
        abort_unless($profile->status === TraderStatus::Approved, 404);

        $validated = $request->validate([
            'months' => ['required', 'integer'],
            'accept_risk' => ['accepted'],
        ], ['accept_risk.accepted' => 'Kamu perlu menyetujui disclaimer risiko.'], ['months' => 'durasi']);

        /** @var User $member */
        $member = $request->user();
        $payment = $checkout->handle($member, $profile->user, (int) $validated['months'], $request->ip());

        return Inertia::location((string) $payment->checkout_url);
    }
}
