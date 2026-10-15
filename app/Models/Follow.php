<?php

namespace App\Models;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Member follows trader (docs/logic/scoring-ranking.md → Follow).
 *
 * @property int $id
 * @property int $follower_id
 * @property int $trader_id
 * @property Carbon|null $created_at
 * @property-read User $follower
 * @property-read User $trader
 */
#[Fillable(['follower_id', 'trader_id'])]
class Follow extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<User, $this>
     */
    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    /**
     * Followers that count for rank and payroll: verified email, active within
     * score.follower_active_days, and not flagged for fraud.
     *
     * @param  Builder<Follow>  $query
     */
    public function scopeValid(Builder $query): void
    {
        $query->whereHas('follower', fn (Builder $follower) => $follower
            ->whereNotNull('email_verified_at')
            ->where('is_fraud_flagged', false)
            ->where('last_active_at', '>=', now()->subDays(Settings::get('score.follower_active_days'))));
    }
}
