<?php

namespace App\Actions\Kyc;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\KycStatus;
use App\Models\KycVerification;
use App\Models\User;
use App\Notifications\KycReviewed;
use Illuminate\Validation\ValidationException;

/**
 * Verify or reject a submitted KYC (docs/logic/compliance.md).
 */
class ReviewKyc
{
    public function __construct(private RecordAdminAudit $audit) {}

    public function verify(User $admin, KycVerification $kyc): KycVerification
    {
        return $this->review($admin, $kyc, KycStatus::Verified, null);
    }

    public function reject(User $admin, KycVerification $kyc, string $reason): KycVerification
    {
        return $this->review($admin, $kyc, KycStatus::Rejected, $reason);
    }

    private function review(User $admin, KycVerification $kyc, KycStatus $status, ?string $reason): KycVerification
    {
        if ($kyc->status !== KycStatus::Submitted) {
            throw ValidationException::withMessages(['kyc' => 'Hanya KYC yang menunggu review yang bisa diproses.']);
        }

        $kyc->update([
            'status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
            'rejection_reason' => $reason,
        ]);

        $this->audit->handle(
            $admin,
            "kyc.{$status->value}",
            $kyc,
            ['status' => KycStatus::Submitted->value],
            array_filter(['status' => $status->value, 'reason' => $reason]),
        );

        $kyc->user->notify(new KycReviewed($kyc));

        return $kyc;
    }
}
