<?php

namespace App\Models;

use App\Enums\OrderType;
use App\Enums\SignalDirection;
use App\Enums\SignalStatus;
use App\Enums\SignalVisibility;
use Database\Factories\SignalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * A published trading signal (docs/logic/signal-lifecycle.md).
 * Levels are immutable once published and signals are never deleted.
 *
 * @property int $id
 * @property int $trader_id
 * @property int $instrument_id
 * @property string $pip_size_snapshot
 * @property string $spread_pips_snapshot
 * @property int $digits_snapshot
 * @property SignalDirection $direction
 * @property OrderType $order_type
 * @property string $entry_price
 * @property string $stop_loss
 * @property string $take_profit
 * @property string $rr
 * @property string $sl_pips
 * @property SignalVisibility $visibility
 * @property string $analysis
 * @property string|null $chart_image_path
 * @property SignalStatus $status
 * @property Carbon $published_at
 * @property Carbon|null $triggered_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $closed_at
 * @property string|null $close_price
 * @property string|null $result_pips
 * @property string|null $result_r
 * @property bool $excluded_from_payroll
 * @property string|null $publish_ip
 * @property string|null $publish_device_hash
 * @property-read User $trader
 * @property-read Instrument $instrument
 */
#[Fillable(['trader_id', 'instrument_id', 'pip_size_snapshot', 'spread_pips_snapshot', 'digits_snapshot', 'direction', 'order_type', 'entry_price', 'stop_loss', 'take_profit', 'rr', 'sl_pips', 'visibility', 'analysis', 'chart_image_path', 'status', 'published_at', 'triggered_at', 'expires_at', 'closed_at', 'close_price', 'result_pips', 'result_r', 'excluded_from_payroll', 'publish_ip', 'publish_device_hash'])]
class Signal extends Model
{
    /** @use HasFactory<SignalFactory> */
    use HasFactory;

    /** Columns locked after publish. */
    public const IMMUTABLE = ['trader_id', 'instrument_id', 'pip_size_snapshot', 'spread_pips_snapshot', 'digits_snapshot', 'direction', 'order_type', 'entry_price', 'stop_loss', 'take_profit', 'rr', 'sl_pips', 'visibility', 'analysis', 'published_at'];

    protected static function booted(): void
    {
        static::updating(function (Signal $signal) {
            $changed = array_intersect(array_keys($signal->getDirty()), self::IMMUTABLE);

            if ($changed !== []) {
                throw new LogicException('Published signals are immutable: '.implode(', ', $changed).'.');
            }
        });

        static::deleting(fn () => throw new LogicException('Signals are never deleted.'));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    /**
     * @return BelongsTo<Instrument, $this>
     */
    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    /**
     * @return HasMany<SignalEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(SignalEvent::class);
    }

    /**
     * @param  Builder<Signal>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', SignalStatus::open());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pip_size_snapshot' => 'decimal:5',
            'spread_pips_snapshot' => 'decimal:2',
            'digits_snapshot' => 'integer',
            'direction' => SignalDirection::class,
            'order_type' => OrderType::class,
            'entry_price' => 'decimal:5',
            'stop_loss' => 'decimal:5',
            'take_profit' => 'decimal:5',
            'rr' => 'decimal:2',
            'sl_pips' => 'decimal:1',
            'visibility' => SignalVisibility::class,
            'status' => SignalStatus::class,
            'published_at' => 'datetime',
            'triggered_at' => 'datetime',
            'expires_at' => 'datetime',
            'closed_at' => 'datetime',
            'close_price' => 'decimal:5',
            'result_pips' => 'decimal:1',
            'result_r' => 'decimal:2',
            'excluded_from_payroll' => 'boolean',
        ];
    }
}
