<?php

namespace App\Actions\Subscription;

use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Enums\SignalVisibility;
use App\Enums\SubscriptionStatus;
use App\Models\Payment;
use App\Models\Signal;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\InactivePremiumChannels;
use App\Notifications\SubscriptionExpiring;
use App\Support\BusinessCalendar;
use App\Support\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

/**
 * Daily: expire finished subscriptions and stale checkouts, send reminders, and warn
 * admins about premium channels that stopped posting premium signals.
 */
class RunSubscriptionLifecycle
{
    public function handle(): void
    {
        Subscription::query()->where('status', SubscriptionStatus::Active)->where('ends_at', '<=', now())
            ->update(['status' => SubscriptionStatus::Expired]);

        Payment::query()->where('status', PaymentStatus::Pending)->where('expires_at', '<=', now())->with('payable')->get()
            ->each(function (Payment $payment) {
                $payment->update(['status' => PaymentStatus::Expired]);

                if ($payment->payable instanceof Subscription) {
                    $payment->payable->update(['status' => SubscriptionStatus::Cancelled]);
                }
            });

        $this->sendReminders();
        $this->alertInactiveChannels();
    }

    private function sendReminders(): void
    {
        $today = BusinessCalendar::now()->startOfDay();

        foreach (Settings::get('subscription.reminder_days') as $days) {
            $day = $today->addDays($days);

            Subscription::query()
                ->currentlyActive()
                ->with(['subscriber', 'trader.traderProfile'])
                ->whereBetween('ends_at', [$day->utc(), $day->endOfDay()->utc()])
                ->get()
                // Skip if another period continues right after this one.
                ->reject(fn (Subscription $subscription) => Subscription::query()
                    ->where('subscriber_id', $subscription->subscriber_id)
                    ->where('trader_id', $subscription->trader_id)
                    ->where('status', SubscriptionStatus::Active)
                    ->where('starts_at', '>=', $subscription->ends_at)
                    ->exists())
                ->each(fn (Subscription $subscription) => $subscription->subscriber->notify(new SubscriptionExpiring($subscription, $days)));
        }
    }

    private function alertInactiveChannels(): void
    {
        $threshold = CarbonImmutable::now()->subDays(Settings::get('subscription.inactive_alert_days'));

        $inactive = Subscription::query()
            ->currentlyActive()
            ->selectRaw('trader_id, min(starts_at) as since')
            ->groupBy('trader_id')
            ->get()
            ->filter(fn ($row) => CarbonImmutable::parse($row->getAttribute('since'))->lte($threshold))
            ->pluck('trader_id')
            ->reject(fn (int $traderId) => Signal::query()
                ->where('trader_id', $traderId)
                ->where('visibility', SignalVisibility::Premium)
                ->where('published_at', '>=', $threshold)
                ->exists())
            ->values();

        if ($inactive->isEmpty()) {
            return;
        }

        $traders = array_values(array_map(
            fn (User $trader) => $trader->traderProfile->display_name ?? $trader->name,
            User::query()->with('traderProfile')->whereIn('id', $inactive)->get()->all(),
        ));

        Notification::send(User::role(Role::Admin->value)->get(), new InactivePremiumChannels($traders, Settings::get('subscription.inactive_alert_days')));
    }
}
