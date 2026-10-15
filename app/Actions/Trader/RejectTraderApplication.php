<?php

namespace App\Actions\Trader;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\TraderStatus;
use App\Models\TraderProfile;
use App\Models\User;
use App\Notifications\TraderApplicationReviewed;

class RejectTraderApplication
{
    use ReviewsTraderProfiles;

    public function __construct(private RecordAdminAudit $audit) {}

    public function handle(User $admin, TraderProfile $profile, string $reason): TraderProfile
    {
        $this->ensureStatus($profile, TraderStatus::Applied, 'Hanya aplikasi yang menunggu review yang bisa ditolak.');

        $profile->update([
            'status' => TraderStatus::Rejected,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
            'rejection_reason' => $reason,
        ]);

        $this->audit->handle($admin, 'trader.rejected', $profile, ['status' => TraderStatus::Applied->value], ['status' => TraderStatus::Rejected->value, 'reason' => $reason]);

        $profile->user->notify(new TraderApplicationReviewed($profile));

        return $profile;
    }
}
