<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * Append-only audit trail of a signal's lifecycle.
 *
 * @property int $id
 * @property int $signal_id
 * @property string $type
 * @property string|null $price
 * @property array<string, mixed>|null $payload
 * @property Carbon $occurred_at
 */
#[Fillable(['signal_id', 'type', 'price', 'payload', 'occurred_at'])]
class SignalEvent extends Model
{
    public $timestamps = false;

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Signal events are append-only.'));
        static::deleting(fn () => throw new LogicException('Signal events are append-only.'));
    }

    /**
     * @return BelongsTo<Signal, $this>
     */
    public function signal(): BelongsTo
    {
        return $this->belongsTo(Signal::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:5',
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
