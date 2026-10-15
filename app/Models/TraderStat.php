<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Cached TraderStats per period: week, month, rolling (score.window_days) and all_time.
 *
 * @property int $id
 * @property int $user_id
 * @property string $period
 * @property Carbon|null $period_start
 * @property int $closed_signals
 * @property int $wins
 * @property int $losses
 * @property string $win_rate
 * @property string $total_r
 * @property string $avg_rr
 * @property string $total_pips
 * @property int $max_consecutive_wins
 * @property int $max_consecutive_losses
 * @property int $current_streak
 * @property int $best_streak
 * @property string $max_drawdown_r
 * @property string|null $profit_factor
 * @property Carbon|null $computed_at
 * @property-read User $user
 */
#[Fillable(['user_id', 'period', 'period_start', 'closed_signals', 'wins', 'losses', 'win_rate', 'total_r', 'avg_rr', 'total_pips', 'max_consecutive_wins', 'max_consecutive_losses', 'current_streak', 'best_streak', 'max_drawdown_r', 'profit_factor', 'computed_at'])]
class TraderStat extends Model
{
    public const PERIODS = ['week', 'month', 'rolling', 'all_time'];

    public $timestamps = false;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'period' => $this->period,
            'period_start' => $this->period_start?->toDateString(),
            'closed_signals' => $this->closed_signals,
            'wins' => $this->wins,
            'losses' => $this->losses,
            'win_rate' => (float) $this->win_rate,
            'total_r' => (float) $this->total_r,
            'avg_rr' => (float) $this->avg_rr,
            'total_pips' => (float) $this->total_pips,
            'max_consecutive_wins' => $this->max_consecutive_wins,
            'max_consecutive_losses' => $this->max_consecutive_losses,
            'current_streak' => $this->current_streak,
            'best_streak' => $this->best_streak,
            'max_drawdown_r' => (float) $this->max_drawdown_r,
            'profit_factor' => $this->profit_factor === null ? null : (float) $this->profit_factor,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'win_rate' => 'decimal:2',
            'total_r' => 'decimal:2',
            'avg_rr' => 'decimal:2',
            'total_pips' => 'decimal:1',
            'max_drawdown_r' => 'decimal:2',
            'profit_factor' => 'decimal:2',
            'computed_at' => 'datetime',
        ];
    }
}
