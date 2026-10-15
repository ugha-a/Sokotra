<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A trader's premium channel (docs/logic/subscription.md).
 *
 * @property int $id
 * @property int $trader_id
 * @property int $monthly_price
 * @property string|null $description
 * @property bool $is_open
 * @property-read User $trader
 */
#[Fillable(['trader_id', 'monthly_price', 'description', 'is_open'])]
class ChannelPlan extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['monthly_price' => 'integer', 'is_open' => 'boolean'];
    }
}
