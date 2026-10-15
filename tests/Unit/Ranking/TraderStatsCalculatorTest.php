<?php

use App\Enums\SignalStatus;
use App\Support\Ranking\TraderStatsCalculator;
use Carbon\CarbonImmutable;

/**
 * @param  list<array{0: SignalStatus, 1: float|null, 2?: float}>  $rows  [status, result_r, rr]
 * @return list<object>
 */
function closedSignals(array $rows): array
{
    $start = CarbonImmutable::parse('2026-10-01 08:00');

    return array_map(fn (array $row, int $i) => (object) [
        'status' => $row[0],
        'result_r' => $row[1] === null ? null : (string) $row[1],
        'result_pips' => $row[1] === null ? null : (string) ($row[1] * 20),
        'rr' => (string) ($row[2] ?? 2.0),
        'closed_at' => $start->addHours($i),
    ], $rows, array_keys($rows));
}

it('returns empty statistics without closed signals', function () {
    $stats = TraderStatsCalculator::calculate([]);

    expect($stats->closedSignals)->toBe(0)
        ->and($stats->winRate)->toBe(0.0)
        ->and($stats->totalR)->toBe(0.0)
        ->and($stats->profitFactor)->toBeNull()
        ->and($stats->maxDrawdownR)->toBe(0.0);
});

it('computes win rate, total R, pips and averages', function () {
    $stats = TraderStatsCalculator::calculate(closedSignals([
        [SignalStatus::TpHit, 2.0, 2.0],
        [SignalStatus::SlHit, -1.0, 3.0],
        [SignalStatus::TpHit, 1.5, 1.5],
        [SignalStatus::ClosedTimeout, -0.4, 2.5],
    ]));

    expect($stats->closedSignals)->toBe(4)
        ->and($stats->wins)->toBe(2)
        ->and($stats->losses)->toBe(2)
        ->and($stats->winRate)->toBe(50.0)
        ->and($stats->totalR)->toBe(2.1)
        ->and($stats->totalPips)->toBe(42.0)
        ->and($stats->avgRr)->toBe(2.25)
        ->and($stats->profitFactor)->toBe(2.5);
});

it('counts a profitable timeout as a win', function () {
    $stats = TraderStatsCalculator::calculate(closedSignals([[SignalStatus::ClosedTimeout, 0.3]]));

    expect($stats->wins)->toBe(1)->and($stats->losses)->toBe(0);
});

it('tracks streaks in closing order', function () {
    $stats = TraderStatsCalculator::calculate(closedSignals([
        [SignalStatus::TpHit, 2.0],
        [SignalStatus::TpHit, 2.0],
        [SignalStatus::TpHit, 2.0],
        [SignalStatus::SlHit, -1.0],
        [SignalStatus::SlHit, -1.0],
        [SignalStatus::TpHit, 2.0],
        [SignalStatus::TpHit, 2.0],
    ]));

    expect($stats->maxConsecutiveWins)->toBe(3)
        ->and($stats->maxConsecutiveLosses)->toBe(2)
        ->and($stats->currentStreak)->toBe(2)
        ->and($stats->bestStreak)->toBe(3);
});

it('ignores expired and cancelled signals unless configured to break streaks', function () {
    $rows = [
        [SignalStatus::TpHit, 2.0],
        [SignalStatus::Expired, null],
        [SignalStatus::TpHit, 2.0],
        [SignalStatus::Cancelled, null],
        [SignalStatus::TpHit, 2.0],
    ];

    $ignored = TraderStatsCalculator::calculate(closedSignals($rows));
    $breaking = TraderStatsCalculator::calculate(closedSignals($rows), countExpiredInStreak: true);

    expect($ignored->closedSignals)->toBe(3)
        ->and($ignored->currentStreak)->toBe(3)
        ->and($breaking->currentStreak)->toBe(1)
        ->and($breaking->bestStreak)->toBe(1);
});

it('measures the largest drawdown of the cumulative R curve', function () {
    // Curve: 2, 1, 0, 3, 1.5 → peak 2 to trough 0 = 2; peak 3 to 1.5 = 1.5
    $stats = TraderStatsCalculator::calculate(closedSignals([
        [SignalStatus::TpHit, 2.0],
        [SignalStatus::SlHit, -1.0],
        [SignalStatus::SlHit, -1.0],
        [SignalStatus::TpHit, 3.0],
        [SignalStatus::ClosedTimeout, -1.5],
    ]));

    expect($stats->maxDrawdownR)->toBe(2.0);
});

it('builds the cumulative equity curve', function () {
    $stats = TraderStatsCalculator::calculate(closedSignals([
        [SignalStatus::TpHit, 2.0],
        [SignalStatus::SlHit, -1.0],
    ]));

    expect(array_column($stats->equityCurve, 'r'))->toBe([2.0, 1.0]);
});
