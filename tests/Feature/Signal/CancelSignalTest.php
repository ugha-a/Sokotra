<?php

use App\Enums\SignalStatus;
use App\Models\Signal;

beforeEach(function () {
    $this->trader = approvedTrader();
});

it('lets a trader cancel their own pending signal', function () {
    $signal = Signal::factory()->pending()->for($this->trader, 'trader')->create();

    $this->actingAs($this->trader)
        ->post(route('trader.signals.cancel', $signal))
        ->assertSessionHasNoErrors();

    $signal->refresh();

    expect($signal->status)->toBe(SignalStatus::Cancelled)
        ->and($signal->closed_at)->not->toBeNull()
        ->and($signal->events()->latest('id')->value('type'))->toBe('cancelled');
});

it('does not cancel active signals', function () {
    $signal = Signal::factory()->for($this->trader, 'trader')->create();

    $this->actingAs($this->trader)
        ->post(route('trader.signals.cancel', $signal))
        ->assertSessionHasErrors('signal');

    expect($signal->fresh()->status)->toBe(SignalStatus::Active);
});

it('does not cancel another trader\'s signal', function () {
    $signal = Signal::factory()->pending()->create();

    $this->actingAs($this->trader)
        ->post(route('trader.signals.cancel', $signal))
        ->assertForbidden();
});

it('limits cancellations per week', function () {
    Signal::factory()->count(3)->for($this->trader, 'trader')->create([
        'status' => SignalStatus::Cancelled,
        'closed_at' => now(),
    ]);
    $signal = Signal::factory()->pending()->for($this->trader, 'trader')->create();

    $this->actingAs($this->trader)
        ->post(route('trader.signals.cancel', $signal))
        ->assertSessionHasErrors('signal');
});
