<?php

use App\Enums\SignalStatus;
use App\Models\Signal;
use App\Models\SignalEvent;

it('refuses to change the levels of a published signal', function (string $attribute, mixed $value) {
    $signal = Signal::factory()->create();

    $signal->update([$attribute => $value]);
})->with([
    ['entry_price', '1.09000'],
    ['stop_loss', '1.00000'],
    ['take_profit', '1.20000'],
    ['direction', 'sell'],
    ['instrument_id', 2],
])->throws(LogicException::class);

it('still allows status transitions', function () {
    $signal = Signal::factory()->create();

    $signal->update(['status' => SignalStatus::TpHit, 'closed_at' => now()]);

    expect($signal->fresh()->status)->toBe(SignalStatus::TpHit);
});

it('never deletes signals', function () {
    Signal::factory()->create()->delete();
})->throws(LogicException::class);

it('keeps signal events append-only', function () {
    $event = SignalEvent::create([
        'signal_id' => Signal::factory()->create()->id,
        'type' => 'published',
        'occurred_at' => now(),
    ]);

    $event->update(['type' => 'tampered']);
})->throws(LogicException::class);
