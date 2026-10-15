<?php

namespace App\Support\Signal;

use App\Models\Instrument;
use App\Models\Signal;

/**
 * Exact price arithmetic for one instrument. Prices are converted to integer
 * "points" (smallest price increment) so comparisons never suffer float drift.
 */
final class PriceMath
{
    private readonly int $scale;

    public function __construct(
        private readonly int $digits,
        private readonly string $pipSize,
        private readonly string $spreadPips,
    ) {
        $this->scale = 10 ** $digits;
    }

    public static function forInstrument(Instrument $instrument): self
    {
        return new self($instrument->digits, $instrument->pip_size, $instrument->spread_pips);
    }

    /** Uses the snapshots taken at publish time, never the current master data. */
    public static function forSignal(Signal $signal): self
    {
        return new self($signal->digits_snapshot, $signal->pip_size_snapshot, $signal->spread_pips_snapshot);
    }

    public function points(string|float|int $price): int
    {
        return (int) round((float) $price * $this->scale);
    }

    public function price(int $points): string
    {
        return number_format($points / $this->scale, $this->digits, '.', '');
    }

    public function pipPoints(): int
    {
        return max(1, (int) round((float) $this->pipSize * $this->scale));
    }

    public function spreadPoints(): int
    {
        return (int) round((float) $this->spreadPips * $this->pipPoints());
    }

    public function pipsBetween(string|float $a, string|float $b): float
    {
        return round(abs($this->points($a) - $this->points($b)) / $this->pipPoints(), 1);
    }

    public function riskReward(string|float $entry, string|float $stopLoss, string|float $takeProfit): float
    {
        $risk = abs($this->points($entry) - $this->points($stopLoss));

        return $risk === 0 ? 0.0 : round(abs($this->points($takeProfit) - $this->points($entry)) / $risk, 2);
    }

    /**
     * Result of closing at an arbitrary price (timeouts).
     *
     * @return array{pips: float, r: float}
     */
    public function timeoutResult(string $direction, string|float $entry, string|float $stopLoss, string|float $close): array
    {
        $sign = $direction === 'buy' ? 1 : -1;
        $move = $sign * ($this->points($close) - $this->points($entry));
        $risk = abs($this->points($entry) - $this->points($stopLoss));

        return [
            'pips' => round($move / $this->pipPoints(), 1),
            'r' => $risk === 0 ? 0.0 : round($move / $risk, 2),
        ];
    }
}
