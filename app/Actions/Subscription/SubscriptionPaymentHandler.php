<?php

namespace App\Actions\Subscription;

use App\Actions\Payments\HandlesPayment;
use App\Actions\Wallet\PostTransaction;
use App\Enums\SubscriptionStatus;
use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Models\Payment;
use App\Models\Subscription;
use App\Notifications\SubscriptionActivated;
use App\Support\Settings;
use Carbon\CarbonImmutable;

/**
 * Paid → active (renewals start when the current period ends) and trader income held.
 * Failed/expired → cancelled.
 */
class SubscriptionPaymentHandler implements HandlesPayment
{
    public function __construct(private PostTransaction $post) {}

    public function paid(Payment $payment): void
    {
        /** @var Subscription $subscription */
        $subscription = $payment->payable;

        $currentEnd = Subscription::query()
            ->where('subscriber_id', $subscription->subscriber_id)
            ->where('trader_id', $subscription->trader_id)
            ->whereKeyNot($subscription->id)
            ->currentlyActive()
            ->max('ends_at');

        $startsAt = $currentEnd ? CarbonImmutable::parse($currentEnd) : CarbonImmutable::now();

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMonthsNoOverflow($subscription->months),
        ]);

        $this->post->handle(
            $subscription->trader,
            WalletTransactionType::SubscriptionIncome,
            WalletBucket::Pending,
            $subscription->trader_income,
            "subscription:{$subscription->id}",
            "Langganan {$subscription->months} bulan dari {$subscription->subscriber->name}",
            now()->addDays(Settings::get('wallet.hold_period_days')),
        );

        $subscription->subscriber->notify(new SubscriptionActivated($subscription, forTrader: false));
        $subscription->trader->notify(new SubscriptionActivated($subscription, forTrader: true));
    }

    public function failed(Payment $payment): void
    {
        $payment->payable->update(['status' => SubscriptionStatus::Cancelled]);
    }
}
