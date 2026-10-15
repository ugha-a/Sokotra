<?php

namespace App\Actions\Trader;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\KycStatus;
use App\Enums\Role;
use App\Enums\TraderStatus;
use App\Models\Rank;
use App\Models\TraderProfile;
use App\Models\User;
use App\Notifications\TraderApplicationReviewed;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveTraderApplication
{
    use ReviewsTraderProfiles;

    public function __construct(private RecordAdminAudit $audit) {}

    public function handle(User $admin, TraderProfile $profile): TraderProfile
    {
        $this->ensureStatus($profile, TraderStatus::Applied, 'Hanya aplikasi yang menunggu review yang bisa disetujui.');

        if ($profile->user->kycVerification?->status !== KycStatus::Verified) {
            throw ValidationException::withMessages(['application' => 'KYC harus terverifikasi sebelum aplikasi disetujui.']);
        }

        DB::transaction(function () use ($admin, $profile) {
            $profile->update([
                'status' => TraderStatus::Approved,
                'rank_id' => Rank::query()->where('is_active', true)->orderBy('level')->value('id'),
                'approved_at' => now(),
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
                'rejection_reason' => null,
            ]);

            $profile->user->assignRole(Role::Trader->value);

            $this->audit->handle($admin, 'trader.approved', $profile, ['status' => TraderStatus::Applied->value], ['status' => TraderStatus::Approved->value]);
        });

        $profile->user->notify(new TraderApplicationReviewed($profile));

        return $profile;
    }
}
