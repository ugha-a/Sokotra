<?php

namespace App\Models;

use App\Enums\PayrollItemStatus;
use App\Support\Payroll\PayrollCalculator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $payroll_period_id
 * @property int $user_id
 * @property int|null $rank_id
 * @property bool $eligible
 * @property list<string> $ineligible_reasons
 * @property int $closed_signals
 * @property int $best_streak
 * @property string $max_drawdown_r
 * @property int $base_amount
 * @property int $wins_counted
 * @property int $wins_amount
 * @property int $followers_counted
 * @property int $followers_amount
 * @property int $streak_amount
 * @property int $tax_amount
 * @property int $total_amount
 * @property PayrollItemStatus $status
 * @property string|null $hold_note
 * @property-read PayrollPeriod $period
 * @property-read User $user
 * @property-read Rank|null $rank
 */
#[Fillable(['payroll_period_id', 'user_id', 'rank_id', 'eligible', 'ineligible_reasons', 'closed_signals', 'best_streak', 'max_drawdown_r', 'base_amount', 'wins_counted', 'wins_amount', 'followers_counted', 'followers_amount', 'streak_amount', 'tax_amount', 'total_amount', 'status', 'hold_note'])]
class PayrollItem extends Model
{
    /**
     * @return BelongsTo<PayrollPeriod, $this>
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Rank, $this>
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'id' => $this->id,
            'eligible' => $this->eligible,
            'reasons' => array_map(fn (string $key) => PayrollCalculator::REASONS[$key] ?? $key, $this->ineligible_reasons),
            'closed_signals' => $this->closed_signals,
            'best_streak' => $this->best_streak,
            'max_drawdown_r' => (float) $this->max_drawdown_r,
            'base_amount' => $this->base_amount,
            'wins_counted' => $this->wins_counted,
            'wins_amount' => $this->wins_amount,
            'followers_counted' => $this->followers_counted,
            'followers_amount' => $this->followers_amount,
            'streak_amount' => $this->streak_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'hold_note' => $this->hold_note,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'eligible' => 'boolean',
            'ineligible_reasons' => 'array',
            'max_drawdown_r' => 'decimal:2',
            'base_amount' => 'integer',
            'wins_amount' => 'integer',
            'followers_amount' => 'integer',
            'streak_amount' => 'integer',
            'tax_amount' => 'integer',
            'total_amount' => 'integer',
            'status' => PayrollItemStatus::class,
        ];
    }
}
