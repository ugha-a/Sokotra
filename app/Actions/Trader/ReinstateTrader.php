<?php

namespace App\Actions\Trader;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\TraderStatus;
use App\Models\TraderProfile;
use App\Models\User;

class ReinstateTrader
{
    use ReviewsTraderProfiles;

    public function __construct(private RecordAdminAudit $audit) {}

    public function handle(User $admin, TraderProfile $profile): TraderProfile
    {
        $this->ensureStatus($profile, TraderStatus::Suspended, 'Hanya trader yang ditangguhkan yang bisa diaktifkan kembali.');

        $profile->update([
            'status' => TraderStatus::Approved,
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        $this->audit->handle($admin, 'trader.reinstated', $profile, ['status' => TraderStatus::Suspended->value], ['status' => TraderStatus::Approved->value]);

        return $profile;
    }
}
