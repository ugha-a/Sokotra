<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $from_rank_id
 * @property int|null $to_rank_id
 * @property array<string, mixed> $stats
 * @property Carbon|null $created_at
 * @property-read Rank|null $fromRank
 * @property-read Rank|null $toRank
 */
#[Fillable(['user_id', 'from_rank_id', 'to_rank_id', 'stats'])]
class TraderRankHistory extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<Rank, $this>
     */
    public function fromRank(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'from_rank_id');
    }

    /**
     * @return BelongsTo<Rank, $this>
     */
    public function toRank(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'to_rank_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['stats' => 'array'];
    }
}
