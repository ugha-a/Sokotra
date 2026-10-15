<?php

namespace App\Actions\Broker;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\BrokerAccountStatus;
use App\Models\BrokerAccount;
use App\Models\User;
use App\Notifications\BrokerAccountReviewed;
use Illuminate\Validation\ValidationException;

class ReviewBrokerAccount
{
    public function __construct(private RecordAdminAudit $audit) {}

    public function link(User $admin, BrokerAccount $account): BrokerAccount
    {
        return $this->review($admin, $account, BrokerAccountStatus::Linked, null);
    }

    public function reject(User $admin, BrokerAccount $account, string $reason): BrokerAccount
    {
        return $this->review($admin, $account, BrokerAccountStatus::Rejected, $reason);
    }

    private function review(User $admin, BrokerAccount $account, BrokerAccountStatus $status, ?string $reason): BrokerAccount
    {
        if ($account->status !== BrokerAccountStatus::Submitted) {
            throw ValidationException::withMessages(['broker_account' => 'Akun ini sudah diverifikasi.']);
        }

        $account->update(['status' => $status, 'verified_by' => $admin->id, 'verified_at' => now(), 'notes' => $reason]);
        $this->audit->handle($admin, "broker_account.{$status->value}", $account, ['status' => 'submitted'], array_filter(['status' => $status->value, 'reason' => $reason]));
        $account->user->notify(new BrokerAccountReviewed($account));

        return $account;
    }
}
