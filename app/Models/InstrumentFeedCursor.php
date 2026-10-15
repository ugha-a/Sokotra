<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Start time of the last 1m candle the signal engine processed per instrument.
 *
 * @property int $instrument_id
 * @property Carbon $last_candle_at
 */
#[Fillable(['instrument_id', 'last_candle_at'])]
class InstrumentFeedCursor extends Model
{
    public const CREATED_AT = null;

    protected $primaryKey = 'instrument_id';

    public $incrementing = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['last_candle_at' => 'datetime'];
    }
}
