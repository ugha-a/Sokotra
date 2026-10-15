<?php

namespace App\Support\Payroll;

final readonly class PayrollResult
{
    /**
     * @param  list<string>  $reasons  Keys of PayrollCalculator::REASONS.
     */
    public function __construct(
        public bool $eligible,
        public array $reasons,
        public int $closedSignals,
        public int $bestStreak,
        public float $maxDrawdownR,
        public int $baseAmount,
        public int $winsCounted,
        public int $winsAmount,
        public int $followersCounted,
        public int $followersAmount,
        public int $streakAmount,
        public int $totalAmount,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toColumns(): array
    {
        return [
            'eligible' => $this->eligible,
            'ineligible_reasons' => $this->reasons,
            'closed_signals' => $this->closedSignals,
            'best_streak' => $this->bestStreak,
            'max_drawdown_r' => $this->maxDrawdownR,
            'base_amount' => $this->baseAmount,
            'wins_counted' => $this->winsCounted,
            'wins_amount' => $this->winsAmount,
            'followers_counted' => $this->followersCounted,
            'followers_amount' => $this->followersAmount,
            'streak_amount' => $this->streakAmount,
            'total_amount' => $this->totalAmount,
        ];
    }
}
