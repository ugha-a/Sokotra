<?php

namespace App\Actions\Subscription;

use App\Actions\Payments\StartCheckout;
use App\Enums\ConsentType;
use App\Enums\SubscriptionStatus;
use App\Models\ChannelPlan;
use App\Models\ContentPage;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateSubscriptionCheckout
{
    public function __construct(private StartCheckout $checkout) {}

    public function handle(User $member, User $trader, int $months, ?string $ip): Payment
    {
        $plan = ChannelPlan::where('trader_id', $trader->id)->first();

        if ($member->id === $trader->id) {
            throw ValidationException::withMessages(['subscription' => 'Kamu tidak bisa berlangganan channel sendiri.']);
        }

        if ($plan === null || ! $plan->is_open) {
            throw ValidationException::withMessages(['subscription' => 'Channel ini sedang tidak menerima subscriber baru.']);
        }

        if (! in_array($months, Settings::get('subscription.durations'), true)) {
            throw ValidationException::withMessages(['months' => 'Durasi tidak tersedia.']);
        }

        $price = $plan->monthly_price * $months;
        $fee = (int) floor($price * Settings::get('subscription.fee_percent') / 100);

        return DB::transaction(function () use ($member, $trader, $months, $price, $fee, $ip) {
            $subscription = Subscription::create([
                'subscriber_id' => $member->id,
                'trader_id' => $trader->id,
                'months' => $months,
                'price_paid' => $price,
                'platform_fee' => $fee,
                'trader_income' => $price - $fee,
                'status' => SubscriptionStatus::PendingPayment,
            ]);

            $member->consents()->create([
                'type' => ConsentType::RiskDisclaimer,
                'version' => ContentPage::where('slug', 'disclaimer-risiko')->first()?->updated_at?->format('YmdHis') ?? 'unversioned',
                'accepted_at' => now(),
                'ip' => $ip,
            ]);

            return $this->checkout->handle($member, $subscription, $price);
        });
    }
}
