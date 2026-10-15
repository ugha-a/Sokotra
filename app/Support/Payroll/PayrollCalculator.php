<?php

namespace App\Support\Payroll;

use Carbon\CarbonImmutable;

/**
 * Single source of truth for the monthly salary formula (docs/logic/payroll-rewards.md).
 *
 * gaji = base_salary + Σ_week min(wins, max_weekly) × per_win + follower bracket bonus + streak bonus
 */
final class PayrollCalculator
{
    public const REASONS = [
        'rank_not_paid' => 'Rank belum termasuk penerima gaji',
        'kyc_not_verified' => 'KYC belum terverifikasi',
        'flagged' => 'Ditandai untuk pemeriksaan fraud',
        'suspended' => 'Akun trader ditangguhkan',
        'streak_not_reached' => 'Syarat streak belum tercapai',
        'not_enough_signals' => 'Jumlah sinyal selesai belum cukup',
        'drawdown_exceeded' => 'Drawdown melebihi batas',
    ];

    /**
     * @param  list<string>  $extraReasons  Reasons decided outside the formula (e.g. suspended).
     */
    public static function calculate(PayrollInput $input, array $extraReasons = []): PayrollResult
    {
        // Pure: the month (and its timezone) comes entirely from the input.
        $tz = $input->monthStart->getTimezone();
        $start = $input->monthStart;
        $end = $start->addMonth();
        $rule = $input->rule;

        $rows = [];
        foreach ($input->signals as $signal) {
            $closedAt = CarbonImmutable::instance($signal->closed_at)->setTimezone($tz);

            if ($signal->status->isScored() && ! $signal->excluded_from_payroll && $closedAt->gte($start) && $closedAt->lt($end)) {
                $rows[] = ['r' => (float) $signal->result_r, 'closed_at' => $closedAt];
            }
        }

        usort($rows, fn (array $a, array $b) => $a['closed_at'] <=> $b['closed_at']);

        $streak = $bestStreak = 0;
        $equity = $peak = $drawdown = 0.0;
        $weeklyWins = [];

        foreach ($rows as $row) {
            $equity += $row['r'];
            $peak = max($peak, $equity);
            $drawdown = max($drawdown, $peak - $equity);

            if ($row['r'] > 0) {
                $streak++;
                $bestStreak = max($bestStreak, $streak);
                $week = $row['closed_at']->format('o-W');
                $weeklyWins[$week] = ($weeklyWins[$week] ?? 0) + 1;
            } else {
                $streak = 0;
            }
        }

        $closed = count($rows);
        $maxWeekly = $rule['max_weekly_wins_counted'] ?? 0;
        $winsCounted = array_sum(array_map(fn (int $wins) => min($wins, $maxWeekly), $weeklyWins));
        $followersBonus = self::bracketBonus($input->brackets, $input->validFollowers);

        $reasons = $extraReasons;
        $pays = $rule !== null && ($rule['base_salary'] > 0 || $rule['per_win_amount'] > 0);

        if (! $pays) {
            $reasons[] = 'rank_not_paid';
        }
        if (! $input->kycVerified) {
            $reasons[] = 'kyc_not_verified';
        }
        if ($input->flagged) {
            $reasons[] = 'flagged';
        }
        if ($bestStreak < ($rule['required_streak'] ?? PHP_INT_MAX)) {
            $reasons[] = 'streak_not_reached';
        }
        if ($closed < ($rule['min_closed_signals_month'] ?? PHP_INT_MAX)) {
            $reasons[] = 'not_enough_signals';
        }
        if (($rule['max_drawdown_r_month'] ?? null) !== null && $drawdown > (float) $rule['max_drawdown_r_month']) {
            $reasons[] = 'drawdown_exceeded';
        }

        $eligible = $reasons === [];
        $base = $eligible ? (int) $rule['base_salary'] : 0;
        $winsAmount = $eligible ? $winsCounted * (int) $rule['per_win_amount'] : 0;
        $followersAmount = $eligible ? $followersBonus : 0;
        $streakAmount = $eligible ? (int) $rule['streak_bonus_amount'] : 0;

        return new PayrollResult(
            eligible: $eligible,
            reasons: array_values(array_unique($reasons)),
            closedSignals: $closed,
            bestStreak: $bestStreak,
            maxDrawdownR: round($drawdown, 2),
            baseAmount: $base,
            winsCounted: $winsCounted,
            winsAmount: $winsAmount,
            followersCounted: $input->validFollowers,
            followersAmount: $followersAmount,
            streakAmount: $streakAmount,
            totalAmount: $base + $winsAmount + $followersAmount + $streakAmount,
        );
    }

    /**
     * @param  list<array{0: int, 1: int|null, 2: int}>  $brackets
     */
    private static function bracketBonus(array $brackets, int $followers): int
    {
        foreach ($brackets as [$min, $max, $bonus]) {
            if ($followers >= $min && ($max === null || $followers <= $max)) {
                return $bonus;
            }
        }

        return 0;
    }
}
