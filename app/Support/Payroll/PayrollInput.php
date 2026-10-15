<?php

namespace App\Support\Payroll;

use App\Models\Signal;
use Carbon\CarbonImmutable;
use stdClass;

/**
 * Everything the payroll formula needs for one trader and one month.
 */
final readonly class PayrollInput
{
    /**
     * @param  array{base_salary: int, per_win_amount: int, max_weekly_wins_counted: int, required_streak: int, min_closed_signals_month: int, max_drawdown_r_month: float|string|null, streak_bonus_amount: int}|null  $rule
     * @param  iterable<Signal|stdClass>  $signals  Closed signals around the month (filtered by the calculator).
     * @param  list<array{0: int, 1: int|null, 2: int}>  $brackets  [min followers, max followers|null, bonus]
     * @param  CarbonImmutable  $monthStart  First day of the month, 00:00 in the business timezone.
     */
    public function __construct(
        public ?array $rule,
        public bool $kycVerified,
        public bool $flagged,
        public iterable $signals,
        public int $validFollowers,
        public array $brackets,
        public CarbonImmutable $monthStart,
    ) {}
}
