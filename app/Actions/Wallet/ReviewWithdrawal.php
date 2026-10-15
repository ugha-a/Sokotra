<?php

namespace App\Actions\Wallet;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\WithdrawalUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * requested ─approve→ processing ─paid→ paid; requested ─reject→ rejected; processing ─reject→ failed.
 * Fees become platform revenue when a withdrawal is paid.
 */
class ReviewWithdrawal
{
    public function __construct(
        private PostTransaction $post,
        private MoveBetweenBuckets $move,
        private RecordAdminAudit $audit,
    ) {}

    public function approve(User $admin, Withdrawal $withdrawal): Withdrawal
    {
        $this->ensure($withdrawal, [WithdrawalStatus::Requested], 'Hanya penarikan yang diajukan yang bisa disetujui.');

        $withdrawal->update(['status' => WithdrawalStatus::Processing, 'reviewed_by' => $admin->id, 'reviewed_at' => now()]);
        $this->audit->handle($admin, 'withdrawal.approved', $withdrawal, ['status' => 'requested'], ['status' => 'processing']);
        $withdrawal->user->notify(new WithdrawalUpdated($withdrawal));

        return $withdrawal;
    }

    public function markPaid(User $admin, Withdrawal $withdrawal, string $reference): Withdrawal
    {
        $this->ensure($withdrawal, [WithdrawalStatus::Processing], 'Penarikan harus disetujui sebelum ditandai dibayar.');

        DB::transaction(function () use ($admin, $withdrawal, $reference) {
            $this->post->handle($withdrawal->user, WalletTransactionType::WithdrawPaid, WalletBucket::Locked, -$withdrawal->total(), "{$withdrawal->reference()}:paid", 'Penarikan dibayar');

            $withdrawal->update([
                'status' => WithdrawalStatus::Paid,
                'disbursement_reference' => $reference,
                'paid_at' => now(),
                'reviewed_by' => $admin->id,
            ]);

            $this->audit->handle($admin, 'withdrawal.paid', $withdrawal, ['status' => 'processing'], ['status' => 'paid', 'reference' => $reference]);
        });

        $withdrawal->user->notify(new WithdrawalUpdated($withdrawal));

        return $withdrawal;
    }

    public function reject(User $admin, Withdrawal $withdrawal, string $reason): Withdrawal
    {
        $this->ensure($withdrawal, [WithdrawalStatus::Requested, WithdrawalStatus::Processing], 'Penarikan ini sudah selesai.');

        $from = $withdrawal->status;
        $to = $from === WithdrawalStatus::Requested ? WithdrawalStatus::Rejected : WithdrawalStatus::Failed;

        DB::transaction(function () use ($admin, $withdrawal, $reason, $from, $to) {
            $this->move->handle($withdrawal->user, WalletTransactionType::WithdrawUnlock, WalletBucket::Locked, WalletBucket::Available, $withdrawal->total(), "{$withdrawal->reference()}:unlock", 'Penarikan dibatalkan');

            $withdrawal->update(['status' => $to, 'rejection_reason' => $reason, 'reviewed_by' => $admin->id, 'reviewed_at' => now()]);
            $this->audit->handle($admin, "withdrawal.{$to->value}", $withdrawal, ['status' => $from->value], ['status' => $to->value, 'reason' => $reason]);
        });

        $withdrawal->user->notify(new WithdrawalUpdated($withdrawal));

        return $withdrawal;
    }

    /**
     * @param  list<WithdrawalStatus>  $allowed
     */
    private function ensure(Withdrawal $withdrawal, array $allowed, string $message): void
    {
        if (! in_array($withdrawal->status, $allowed, true)) {
            throw ValidationException::withMessages(['withdrawal' => $message]);
        }
    }
}
