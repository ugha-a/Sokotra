<?php

namespace App\Actions\Payroll;

use App\Actions\Audit\RecordAdminAudit;
use App\Enums\PayrollItemStatus;
use App\Enums\PayrollPeriodStatus;
use App\Models\PayrollItem;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class HoldPayrollItem
{
    public function __construct(private RecordAdminAudit $audit) {}

    public function hold(User $admin, PayrollItem $item, string $note): PayrollItem
    {
        $this->ensureDraft($item);
        $item->update(['status' => PayrollItemStatus::Held, 'hold_note' => $note]);
        $this->audit->handle($admin, 'payroll.item_held', $item, null, ['note' => $note]);

        return $item;
    }

    public function release(User $admin, PayrollItem $item): PayrollItem
    {
        $this->ensureDraft($item);
        $item->update(['status' => PayrollItemStatus::Pending, 'hold_note' => null]);
        $this->audit->handle($admin, 'payroll.item_released', $item);

        return $item;
    }

    private function ensureDraft(PayrollItem $item): void
    {
        if ($item->period->status !== PayrollPeriodStatus::Draft) {
            throw ValidationException::withMessages(['payroll' => 'Payroll yang sudah disetujui tidak bisa diubah.']);
        }
    }
}
