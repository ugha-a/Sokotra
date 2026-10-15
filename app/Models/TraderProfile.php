<?php

namespace App\Models;

use App\Enums\TraderStatus;
use App\Enums\TradingStyle;
use Database\Factories\TraderProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Trader recruitment profile (docs/logic/trader-onboarding.md).
 *
 * @property int $id
 * @property int $user_id
 * @property string $display_name
 * @property string $slug
 * @property string $bio
 * @property TradingStyle $trading_style
 * @property int $experience_years
 * @property list<int> $main_instruments
 * @property TraderStatus $status
 * @property int|null $rank_id
 * @property bool $is_featured
 * @property bool $is_flagged
 * @property Carbon|null $applied_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $reviewed_at
 * @property int|null $reviewed_by
 * @property string|null $rejection_reason
 * @property Carbon|null $suspended_at
 * @property string|null $suspension_reason
 * @property-read User $user
 * @property-read Rank|null $rank
 * @property-read User|null $reviewer
 */
#[Fillable(['user_id', 'display_name', 'slug', 'bio', 'trading_style', 'experience_years', 'main_instruments', 'status', 'rank_id', 'is_featured', 'is_flagged', 'applied_at', 'approved_at', 'reviewed_at', 'reviewed_by', 'rejection_reason', 'suspended_at', 'suspension_reason'])]
class TraderProfile extends Model
{
    /** @use HasFactory<TraderProfileFactory> */
    use HasFactory;

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
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trading_style' => TradingStyle::class,
            'status' => TraderStatus::class,
            'experience_years' => 'integer',
            'main_instruments' => 'array',
            'is_featured' => 'boolean',
            'is_flagged' => 'boolean',
            'applied_at' => 'datetime',
            'approved_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }
}
