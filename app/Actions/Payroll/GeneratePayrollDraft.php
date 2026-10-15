<?php

namespace App\Actions\Payroll;

use App\Enums\PayrollItemStatus;
use App\Enums\PayrollPeriodStatus;
use App\Enums\TraderStatus;
use App\Models\PayrollPeriod;
use App\Models\TraderProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Builds (or rebuilds) the draft payroll for a month (docs/logic/payroll-rewards.md → Proses).
 */
class GeneratePayrollDraft
{
    public function __construct(private CalculateTraderPayroll $calculate) {}

    public function handle(string $month): PayrollPeriod
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            throw ValidationException::withMessages(['month' => 'Format bulan harus YYYY-MM.']);
        }

        return DB::transaction(function () use ($month) {
            $period = PayrollPeriod::firstOrCreate(['month' => $month], ['status' => PayrollPeriodStatus::Draft]);

            if ($period->status !== PayrollPeriodStatus::Draft) {
                throw ValidationException::withMessages(['payroll' => "Payroll {$month} sudah disetujui dan tidak bisa dibuat ulang."]);
            }

            $held = $period->items()->where('status', PayrollItemStatus::Held)->pluck('hold_note', 'user_id');
            $period->items()->delete();

            TraderProfile::query()
                ->whereIn('status', [TraderStatus::Approved, TraderStatus::Suspended])
                ->with('user')
                ->chunkById(200, function (Collection $profiles) use ($period, $month, $held) {
                    foreach ($profiles as $profile) {
                        /** @var TraderProfile $profile */
                        $result = $this->calculate->handle($profile, $month);

                        $period->items()->create([
                            ...$result->toColumns(),
                            'user_id' => $profile->user_id,
                            'rank_id' => $profile->rank_id,
                            'status' => $held->has($profile->user_id) ? PayrollItemStatus::Held : PayrollItemStatus::Pending,
                            'hold_note' => $held[$profile->user_id] ?? null,
                        ]);
                    }
                });

            $period->update([
                'total_amount' => (int) $period->items()->where('eligible', true)->sum('total_amount'),
                'generated_at' => now(),
            ]);

            return $period->fresh() ?? $period;
        });
    }
}
