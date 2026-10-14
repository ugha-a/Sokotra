<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $rank_id
 * @property int $base_salary
 * @property int $per_win_amount
 * @property int $required_streak
 * @property int $min_closed_signals_month
 * @property string|null $max_drawdown_r_month
 * @property int $streak_bonus_amount
 * @property int $max_weekly_wins_counted
 * @property bool $is_active
 * @property-read Rank $rank
 * @property-read string $rank_name
 */
#[Fillable(['rank_id', 'base_salary', 'per_win_amount', 'required_streak', 'min_closed_signals_month', 'max_drawdown_r_month', 'streak_bonus_amount', 'max_weekly_wins_counted', 'is_active'])]
class PayrollRule extends Model
{
    /**
     * @return BelongsTo<Rank, $this>
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function rankName(): Attribute
    {
        return Attribute::get(fn () => $this->rank->name);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rank_id' => 'integer',
            'base_salary' => 'integer',
            'per_win_amount' => 'integer',
            'required_streak' => 'integer',
            'min_closed_signals_month' => 'integer',
            'max_drawdown_r_month' => 'decimal:2',
            'streak_bonus_amount' => 'integer',
            'max_weekly_wins_counted' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
