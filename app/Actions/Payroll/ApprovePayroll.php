<?php

namespace App\Actions\Payroll;

use App\Actions\Audit\RecordAdminAudit;
use App\Actions\Wallet\PostTransaction;
use App\Enums\PayrollItemStatus;
use App\Enums\PayrollPeriodStatus;
use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Notifications\PayrollPaid;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Credits every eligible, non-held item to the trader's available balance, once.
 */
class ApprovePayroll
{
    public function __construct(private PostTransaction $post, private RecordAdminAudit $audit) {}

    public function handle(User $admin, PayrollPeriod $period): PayrollPeriod
    {
        $paid = DB::transaction(function () use ($admin, $period) {
            $period = PayrollPeriod::whereKey($period->id)->lockForUpdate()->firstOrFail();

            if ($period->status !== PayrollPeriodStatus::Draft) {
                throw ValidationException::withMessages(['payroll' => 'Payroll ini sudah disetujui.']);
            }

            $paid = [];

            foreach ($period->items()->with('user')->where('status', PayrollItemStatus::Pending)->get() as $item) {
                /** @var PayrollItem $item */
                if (! $item->eligible || $item->total_amount <= 0) {
                    $item->update(['status' => PayrollItemStatus::Skipped]);

                    continue;
                }

                $this->post->handle($item->user, WalletTransactionType::PayrollCredit, WalletBucket::Available, $item->total_amount, "payroll_item:{$item->id}", "Gaji & reward {$period->label()}");
                $item->update(['status' => PayrollItemStatus::Paid]);
                $paid[] = $item;
            }

            $total = array_sum(array_map(fn (PayrollItem $item) => $item->total_amount, $paid));
            $period->update(['status' => PayrollPeriodStatus::Approved, 'approved_by' => $admin->id, 'approved_at' => now()]);
            $this->audit->handle($admin, 'payroll.approved', $period, ['status' => 'draft'], ['status' => 'approved', 'paid_items' => count($paid), 'paid_total' => $total]);

            return $paid;
        });

        foreach ($paid as $item) {
            $item->user->notify(new PayrollPaid($item));
        }

        return $period->fresh() ?? $period;
    }
}
