<?php

namespace App\Actions\Subscription;

use App\Enums\KycStatus;
use App\Enums\TraderStatus;
use App\Models\ChannelPlan;
use App\Models\KycVerification;
use App\Models\Rank;
use App\Models\TraderProfile;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SaveChannelPlan
{
    /**
     * Why this trader cannot sell a premium channel, or null.
     */
    public function blockReason(User $trader): ?string
    {
        $profile = TraderProfile::with('rank')->where('user_id', $trader->id)->first();

        return match (true) {
            $profile?->status !== TraderStatus::Approved => 'Akun trader kamu belum aktif.',
            ! ($profile->rank->can_sell_premium ?? false) => 'Channel premium tersedia mulai rank '.$this->firstPremiumRank().'.',
            KycVerification::where('user_id', $trader->id)->first()?->status !== KycStatus::Verified => 'Verifikasi identitas (KYC) wajib untuk menjual langganan.',
            default => null,
        };
    }

    /**
     * @param  array{monthly_price: int, description: string|null, is_open: bool}  $data
     */
    public function handle(User $trader, array $data): ChannelPlan
    {
        if ($reason = $this->blockReason($trader)) {
            throw ValidationException::withMessages(['channel' => $reason]);
        }

        $rank = TraderProfile::with('rank')->where('user_id', $trader->id)->firstOrFail()->rank;
        $min = $rank->subscription_min_price ?? 0;
        $max = $rank->subscription_max_price ?? PHP_INT_MAX;

        if ($data['monthly_price'] < $min || $data['monthly_price'] > $max) {
            throw ValidationException::withMessages([
                'monthly_price' => 'Harga untuk rank '.$rank?->name.' harus antara Rp'.number_format($min, 0, ',', '.').' dan Rp'.number_format($max, 0, ',', '.').'.',
            ]);
        }

        return ChannelPlan::updateOrCreate(['trader_id' => $trader->id], $data);
    }

    private function firstPremiumRank(): string
    {
        return (string) Rank::query()->where('can_sell_premium', true)->where('is_active', true)->orderBy('level')->value('name');
    }
}
