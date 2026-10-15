<?php

use App\Support\Signal\PriceMath;

it('converts prices to integer points using the instrument digits', function () {
    $eurusd = new PriceMath(digits: 5, pipSize: '0.0001', spreadPips: '1.0');

    expect($eurusd->points('1.08345'))->toBe(108345)
        ->and($eurusd->pipPoints())->toBe(10)
        ->and($eurusd->spreadPoints())->toBe(10)
        ->and($eurusd->price(108355))->toBe('1.08355');
});

it('measures distances in pips', function () {
    $xauusd = new PriceMath(digits: 2, pipSize: '0.1', spreadPips: '3');

    expect($xauusd->pipsBetween('2650.00', '2645.50'))->toBe(45.0)
        ->and($xauusd->spreadPoints())->toBe(30);
});

it('computes the risk reward ratio', function () {
    $math = new PriceMath(digits: 5, pipSize: '0.0001', spreadPips: '0');

    expect($math->riskReward('1.08000', '1.07800', '1.08500'))->toBe(2.5);
});

it('computes the R multiple of a timeout close', function () {
    $math = new PriceMath(digits: 3, pipSize: '0.01', spreadPips: '0');

    // Buy 150.000, SL 149.500 (50 pips risk), closed at 150.250 (+25 pips)
    expect($math->timeoutResult('buy', '150.000', '149.500', '150.250'))->toBe(['pips' => 25.0, 'r' => 0.5])
        // Sell 150.000, SL 150.500, closed at 150.250 (−25 pips)
        ->and($math->timeoutResult('sell', '150.000', '150.500', '150.250'))->toBe(['pips' => -25.0, 'r' => -0.5]);
});
