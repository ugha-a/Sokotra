<?php

namespace App\Actions\Fraud;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\FraudFlagStatus;
use App\Enums\FraudFlagType;
use App\Models\FraudFlag;
use App\Models\Signal;
use App\Models\TraderProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Confirm: hedging → traders flagged (out of payroll & leaderboard); follower farm →
 * followers flagged (no longer valid followers). Dismiss: hedging signals count again.
 */
class ResolveFraudFlag
{
    public function __construct(private RecordAdminAudit $audit) {}

    public function confirm(User $admin, FraudFlag $flag, ?string $notes): FraudFlag
    {
        return $this->resolve($admin, $flag, FraudFlagStatus::Confirmed, $notes, function (FraudFlag $flag) {
            if ($flag->type === FraudFlagType::Hedging) {
                TraderProfile::whereIn('user_id', $flag->subject_user_ids)->update(['is_flagged' => true]);
            } else {
                User::whereIn('id', $flag->subject_user_ids)->update(['is_fraud_flagged' => true]);
            }
        });
    }

    public function dismiss(User $admin, FraudFlag $flag, ?string $notes): FraudFlag
    {
        return $this->resolve($admin, $flag, FraudFlagStatus::Dismissed, $notes, function (FraudFlag $flag) {
            if ($flag->type === FraudFlagType::Hedging && $flag->signal_ids) {
                Signal::whereIn('id', $flag->signal_ids)->update(['excluded_from_payroll' => false]);
            }
        });
    }

    /**
     * @param  callable(FraudFlag): void  $effect
     */
    private function resolve(User $admin, FraudFlag $flag, FraudFlagStatus $status, ?string $notes, callable $effect): FraudFlag
    {
        if ($flag->status !== FraudFlagStatus::Open) {
            throw ValidationException::withMessages(['flag' => 'Flag ini sudah diputuskan.']);
        }

        DB::transaction(function () use ($admin, $flag, $status, $notes, $effect) {
            $effect($flag);
            $flag->update(['status' => $status, 'reviewed_by' => $admin->id, 'reviewed_at' => now(), 'notes' => $notes]);
            $this->audit->handle($admin, "fraud_flag.{$status->value}", $flag, ['status' => 'open'], ['status' => $status->value, 'notes' => $notes]);
        });

        return $flag;
    }
}
