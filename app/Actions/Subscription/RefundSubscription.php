<?php

namespace App\Actions\Subscription;

use App\Actions\Audit\RecordAdminAudit;
use App\Actions\Wallet\PostTransaction;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientBalance;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\SubscriptionRefunded;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Admin refund while the trader's income is still held (docs/logic/subscription.md → Refund).
 */
class RefundSubscription
{
    public function __construct(private PostTransaction $post, private RecordAdminAudit $audit) {}

    public function handle(User $admin, Subscription $subscription, string $reason): Subscription
    {
        if ($subscription->status !== SubscriptionStatus::Active) {
            throw ValidationException::withMessages(['subscription' => 'Hanya langganan aktif yang bisa di-refund.']);
        }

        $income = WalletTransaction::where('reference', "subscription:{$subscription->id}")->first();
        $released = $income && WalletTransaction::where('reference', "release:{$income->id}:in")->exists();

        if ($income === null || $released) {
            throw ValidationException::withMessages(['subscription' => 'Pendapatan trader sudah dirilis, refund otomatis tidak tersedia. Gunakan koreksi saldo manual.']);
        }

        DB::transaction(function () use ($admin, $subscription, $reason, $income) {
            try {
                $this->post->handle($subscription->trader, WalletTransactionType::RefundReversal, WalletBucket::Pending, -$income->amount, "reverse:{$income->id}", "Refund langganan #{$subscription->id}");
            } catch (InsufficientBalance) {
                throw ValidationException::withMessages(['subscription' => 'Saldo tertahan trader tidak cukup untuk refund.']);
            }

            $subscription->update(['status' => SubscriptionStatus::Refunded, 'refunded_at' => now(), 'refund_reason' => $reason, 'ends_at' => now()]);
            $subscription->payment?->update(['status' => PaymentStatus::Refunded]);
            $this->audit->handle($admin, 'subscription.refunded', $subscription, ['status' => 'active'], ['status' => 'refunded', 'reason' => $reason]);
        });

        $subscription->subscriber->notify(new SubscriptionRefunded($subscription));

        return $subscription;
    }
}
