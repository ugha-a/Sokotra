<?php

use App\Enums\SignalStatus;
use App\Support\Payroll\PayrollCalculator;
use App\Support\Payroll\PayrollInput;
use Carbon\CarbonImmutable;

/** Elite rule from docs/logic/payroll-rewards.md → Contoh. */
function eliteRule(array $overrides = []): array
{
    return [
        'base_salary' => 2000000,
        'per_win_amount' => 25000,
        'max_weekly_wins_counted' => 10,
        'required_streak' => 10,
        'min_closed_signals_month' => 20,
        'max_drawdown_r_month' => null,
        'streak_bonus_amount' => 500000,
        ...$overrides,
    ];
}

/** October 2026 in WIB: weeks start Mon 28 Sep, 5, 12, 19, 26 Oct. */
function octoberInput(array $signals, array $overrides = []): PayrollInput
{
    return new PayrollInput(...[
        'rule' => eliteRule(),
        'kycVerified' => true,
        'flagged' => false,
        'signals' => $signals,
        'validFollowers' => 620,
        'brackets' => [[0, 99, 0], [100, 499, 250000], [500, 999, 750000], [1000, null, 1500000]],
        'monthStart' => CarbonImmutable::parse('2026-10-01 00:00', 'Asia/Jakarta'),
        ...$overrides,
    ]);
}

/**
 * @param  list<array{0: string, 1: float}>  $rows  [WIB datetime, R]
 */
function payrollSignals(array $rows): array
{
    return array_map(fn (array $row) => (object) [
        'status' => $row[1] > 0 ? SignalStatus::TpHit : SignalStatus::SlHit,
        'result_r' => (string) $row[1],
        'closed_at' => CarbonImmutable::parse($row[0], 'Asia/Jakarta'),
        'excluded_from_payroll' => $row[2] ?? false,
    ], $rows);
}

/** Wins spread over October weeks: 12, 8, 10, 5 wins → 10 + 8 + 10 + 5 = 33 paid. */
function docsExampleSignals(): array
{
    $rows = [];
    foreach (['2026-10-05' => 12, '2026-10-12' => 8, '2026-10-19' => 10, '2026-10-26' => 5] as $monday => $wins) {
        for ($i = 0; $i < $wins; $i++) {
            $rows[] = [CarbonImmutable::parse($monday)->addHours(8 + $i)->toDateTimeString(), 2.0];
        }
    }

    // A loss early in the month so the best streak is 11 (as in the docs example)
    array_splice($rows, 11, 0, [['2026-10-05 19:30:00', -1.0]]);

    return payrollSignals($rows);
}

it('reproduces the documented Elite example', function () {
    $result = PayrollCalculator::calculate(octoberInput(docsExampleSignals()));

    expect($result->eligible)->toBeTrue()
        ->and($result->winsCounted)->toBe(33)
        ->and($result->baseAmount)->toBe(2000000)
        ->and($result->winsAmount)->toBe(825000)
        ->and($result->followersAmount)->toBe(750000)
        ->and($result->streakAmount)->toBe(500000)
        ->and($result->totalAmount)->toBe(4075000)
        ->and($result->bestStreak)->toBeGreaterThanOrEqual(10);
});

it('explains every reason a trader is not eligible', function () {
    $result = PayrollCalculator::calculate(octoberInput(
        payrollSignals([['2026-10-05 10:00', 2.0], ['2026-10-06 10:00', -1.0]]),
        ['kycVerified' => false, 'flagged' => true],
    ));

    expect($result->eligible)->toBeFalse()
        ->and($result->totalAmount)->toBe(0)
        ->and($result->reasons)->toContain('kyc_not_verified', 'flagged', 'streak_not_reached', 'not_enough_signals');
});

it('rejects traders above the monthly drawdown limit', function () {
    $result = PayrollCalculator::calculate(octoberInput(docsExampleSignals(), ['rule' => eliteRule(['max_drawdown_r_month' => 0.5])]));

    expect($result->eligible)->toBeFalse()->and($result->reasons)->toBe(['drawdown_exceeded']);
});

it('is not eligible when the rank pays nothing', function () {
    $result = PayrollCalculator::calculate(octoberInput(docsExampleSignals(), [
        'rule' => eliteRule(['base_salary' => 0, 'per_win_amount' => 0, 'streak_bonus_amount' => 0]),
    ]));

    expect($result->eligible)->toBeFalse()->and($result->reasons)->toContain('rank_not_paid');
});

it('ignores signals outside the month and signals excluded from payroll', function () {
    $signals = docsExampleSignals();
    $signals[] = payrollSignals([['2026-09-30 23:00', 2.0]])[0];
    $signals[] = payrollSignals([['2026-11-01 00:30', 2.0]])[0];
    $signals[] = payrollSignals([['2026-10-27 10:00', 2.0, true]])[0];

    expect(PayrollCalculator::calculate(octoberInput($signals))->winsCounted)->toBe(33);
});

it('only counts streaks within the month', function () {
    $rows = [];
    for ($i = 0; $i < 6; $i++) {
        $rows[] = ["2026-09-30 1{$i}:00", 2.0];
    }
    for ($i = 0; $i < 6; $i++) {
        $rows[] = ["2026-10-01 1{$i}:00", 2.0];
    }

    expect(PayrollCalculator::calculate(octoberInput(payrollSignals($rows)))->bestStreak)->toBe(6);
});

it('picks the follower bracket and handles an open upper bound', function () {
    $result = PayrollCalculator::calculate(octoberInput(docsExampleSignals(), ['validFollowers' => 5000]));

    expect($result->followersAmount)->toBe(1500000);
});
