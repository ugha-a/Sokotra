<?php

namespace App\Actions\Trader;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\TraderStatus;
use App\Models\TraderProfile;
use App\Models\User;

/**
 * Suspended traders keep their role and track record but cannot post signals
 * and are hidden from the leaderboard (docs/logic/trader-onboarding.md).
 */
class SuspendTrader
{
    use ReviewsTraderProfiles;

    public function __construct(private RecordAdminAudit $audit) {}

    public function handle(User $admin, TraderProfile $profile, string $reason): TraderProfile
    {
        $this->ensureStatus($profile, TraderStatus::Approved, 'Hanya trader yang aktif yang bisa ditangguhkan.');

        $profile->update([
            'status' => TraderStatus::Suspended,
            'suspended_at' => now(),
            'suspension_reason' => $reason,
        ]);

        $this->audit->handle($admin, 'trader.suspended', $profile, ['status' => TraderStatus::Approved->value], ['status' => TraderStatus::Suspended->value, 'reason' => $reason]);

        return $profile;
    }
}
