<?php

namespace App\Support\Ranking;

use App\Models\Signal;
use stdClass;

/**
 * Single source of truth for trader statistics formulas (docs/logic/scoring-ranking.md).
 * Pure: takes closed signals, returns TraderStats.
 */
final class TraderStatsCalculator
{
    /**
     * @param  iterable<Signal|stdClass>  $signals  Closed signals (or rows with status, result_r, result_pips, rr, closed_at).
     */
    public static function calculate(iterable $signals, bool $countExpiredInStreak = false): TraderStats
    {
        $rows = [];

        foreach ($signals as $signal) {
            if (! $signal->status->isOpen()) {
                $rows[] = $signal;
            }
        }

        usort($rows, fn (object $a, object $b) => $a->closed_at <=> $b->closed_at);

        $closed = $wins = $losses = 0;
        $totalR = $totalPips = $rrSum = $gains = $pain = 0.0;
        $streak = $lossStreak = $maxWins = $maxLosses = $bestStreak = 0;
        $peak = $equity = $maxDrawdown = 0.0;
        $curve = [];

        foreach ($rows as $signal) {
            if (! $signal->status->isScored()) {
                if ($countExpiredInStreak) {
                    $streak = 0;
                }

                continue;
            }

            $r = (float) $signal->result_r;
            $closed++;
            $totalR += $r;
            $totalPips += (float) $signal->result_pips;
            $rrSum += (float) $signal->rr;

            if ($r > 0) {
                $wins++;
                $gains += $r;
                $streak++;
                $lossStreak = 0;
            } else {
                $losses++;
                $pain += abs($r);
                $lossStreak++;
                $streak = 0;
            }

            $maxWins = max($maxWins, $streak);
            $maxLosses = max($maxLosses, $lossStreak);
            $bestStreak = max($bestStreak, $streak);

            $equity += $r;
            $peak = max($peak, $equity);
            $maxDrawdown = max($maxDrawdown, $peak - $equity);
            $curve[] = ['closed_at' => $signal->closed_at->toIso8601String(), 'r' => round($equity, 2)];
        }

        return new TraderStats(
            closedSignals: $closed,
            wins: $wins,
            losses: $losses,
            winRate: $closed === 0 ? 0.0 : round($wins / $closed * 100, 2),
            totalR: round($totalR, 2),
            avgRr: $closed === 0 ? 0.0 : round($rrSum / $closed, 2),
            totalPips: round($totalPips, 1),
            maxConsecutiveWins: $maxWins,
            maxConsecutiveLosses: $maxLosses,
            currentStreak: $streak,
            bestStreak: $bestStreak,
            maxDrawdownR: round($maxDrawdown, 2),
            profitFactor: $pain > 0 ? round($gains / $pain, 2) : null,
            equityCurve: $curve,
        );
    }
}
