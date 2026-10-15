<?php

namespace App\Http\Controllers\Trader;

use App\Actions\Subscription\SaveChannelPlan;
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

class ChannelController extends Controller
{
    public function show(Request $request, SaveChannelPlan $plans): Response
    {
        $trader = $this->user($request);
        $rank = TraderProfile::with('rank')->where('user_id', $trader->id)->first()?->rank;
        $plan = ChannelPlan::where('trader_id', $trader->id)->first();

        return Inertia::render('trader/channel', [
            'canSell' => $plans->blockReason($trader) === null,
            'blockReason' => $plans->blockReason($trader),
            'range' => ['min' => $rank?->subscription_min_price, 'max' => $rank?->subscription_max_price],
            'plan' => $plan ? ['monthly_price' => $plan->monthly_price, 'description' => $plan->description, 'is_open' => $plan->is_open] : null,
            'feePercent' => Settings::get('subscription.fee_percent'),
            'durations' => Settings::get('subscription.durations'),
            'activeSubscribers' => Subscription::where('trader_id', $trader->id)->currentlyActive()->distinct()->count('subscriber_id'),
            'subscribers' => Subscription::query()
                ->with('subscriber:id,name')
                ->where('trader_id', $trader->id)
                ->whereNotNull('starts_at')
                ->latest('id')
                ->limit(50)
                ->get()
                ->map(fn (Subscription $subscription) => [
                    'id' => $subscription->id,
                    'name' => $subscription->subscriber->name,
                    'months' => $subscription->months,
                    'trader_income' => $subscription->trader_income,
                    'status' => $subscription->status->value,
                    'status_label' => $subscription->status->label(),
                    'starts_at' => $subscription->starts_at?->toIso8601String(),
                    'ends_at' => $subscription->ends_at?->toIso8601String(),
                ])->values()->all(),
        ]);
    }

    public function update(Request $request, SaveChannelPlan $plans): RedirectResponse
    {
        $validated = $request->validate([
            'monthly_price' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_open' => ['required', 'boolean'],
        ], [], ['monthly_price' => 'harga per bulan', 'description' => 'deskripsi']);

        $plans->handle($this->user($request), [
            'monthly_price' => (int) $validated['monthly_price'],
            'description' => $validated['description'] ?? null,
            'is_open' => (bool) $validated['is_open'],
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Channel premium disimpan.']);

        return back();
    }

    private function user(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
