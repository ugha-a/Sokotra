<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $level
 * @property int $min_closed_signals
 * @property string $min_win_rate
 * @property string $min_total_r
 * @property int $min_valid_followers
 * @property string|null $max_drawdown_r
 * @property bool $can_sell_premium
 * @property int|null $subscription_min_price
 * @property int|null $subscription_max_price
 * @property bool $can_sell_products
 * @property string $color
 * @property bool $is_active
 */
#[Fillable(['name', 'slug', 'level', 'min_closed_signals', 'min_win_rate', 'min_total_r', 'min_valid_followers', 'max_drawdown_r', 'can_sell_premium', 'subscription_min_price', 'subscription_max_price', 'can_sell_products', 'color', 'is_active'])]
class Rank extends Model
{
    /** Design-system colour tokens a rank badge may use (docs/ui-ux/design-system.md). */
    public const COLORS = ['rank-1', 'rank-2', 'rank-3', 'rank-4', 'rank-5', 'rank-6'];

    /**
     * @return HasOne<PayrollRule, $this>
     */
    public function payrollRule(): HasOne
    {
        return $this->hasOne(PayrollRule::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'min_closed_signals' => 'integer',
            'min_win_rate' => 'decimal:2',
            'min_total_r' => 'decimal:2',
            'min_valid_followers' => 'integer',
            'max_drawdown_r' => 'decimal:2',
            'can_sell_premium' => 'boolean',
            'subscription_min_price' => 'integer',
            'subscription_max_price' => 'integer',
            'can_sell_products' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
