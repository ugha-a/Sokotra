<?php

namespace App\Support\Ranking;

/**
 * Statistics for one trader over one period (docs/logic/scoring-ranking.md).
 */
final readonly class TraderStats
{
    /**
     * @param  list<array{closed_at: string, r: float}>  $equityCurve  Cumulative R after each scored signal.
     */
    public function __construct(
        public int $closedSignals,
        public int $wins,
        public int $losses,
        public float $winRate,
        public float $totalR,
        public float $avgRr,
        public float $totalPips,
        public int $maxConsecutiveWins,
        public int $maxConsecutiveLosses,
        public int $currentStreak,
        public int $bestStreak,
        public float $maxDrawdownR,
        public ?float $profitFactor,
        public array $equityCurve,
    ) {}

    /**
     * Column values for the trader_stats table.
     *
     * @return array<string, int|float|null>
     */
    public function toColumns(): array
    {
        return [
            'closed_signals' => $this->closedSignals,
            'wins' => $this->wins,
            'losses' => $this->losses,
            'win_rate' => $this->winRate,
            'total_r' => $this->totalR,
            'avg_rr' => $this->avgRr,
            'total_pips' => $this->totalPips,
            'max_consecutive_wins' => $this->maxConsecutiveWins,
            'max_consecutive_losses' => $this->maxConsecutiveLosses,
            'current_streak' => $this->currentStreak,
            'best_streak' => $this->bestStreak,
            'max_drawdown_r' => $this->maxDrawdownR,
            'profit_factor' => $this->profitFactor,
        ];
    }
}
