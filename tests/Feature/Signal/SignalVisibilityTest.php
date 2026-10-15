<?php

use App\Enums\Role;
use App\Enums\SignalStatus;
use App\Models\Instrument;
use App\Models\Signal;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    $this->member = userWithRoles(Role::Member);
    $this->owner = approvedTrader('elite');
});

it('shows the signal feed to members with premium details locked', function () {
    Signal::factory()->for($this->owner, 'trader')->create(['visibility' => 'free']);
    Signal::factory()->for($this->owner, 'trader')->create(['visibility' => 'premium']);

    $this->actingAs($this->member)
        ->get(route('signals.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('signals/index')
            ->has('signals.data', 2)
            ->where('signals.data.0.visibility', 'premium')
            ->where('signals.data.0.locked', true)
            ->where('signals.data.0.entry_price', null)
            ->where('signals.data.0.stop_loss', null)
            ->where('signals.data.0.analysis', null)
            ->where('signals.data.0.instrument.symbol', fn ($symbol) => is_string($symbol))
            ->where('signals.data.0.direction', fn ($direction) => in_array($direction, ['buy', 'sell']))
            ->where('signals.data.1.locked', false)
            ->where('signals.data.1.entry_price', fn ($entry) => is_string($entry)));
});

it('unlocks premium signals once they are closed', function () {
    Signal::factory()->for($this->owner, 'trader')->create([
        'visibility' => 'premium',
        'status' => SignalStatus::SlHit,
        'closed_at' => now(),
        'result_r' => -1,
    ]);

    $this->actingAs($this->member)
        ->get(route('signals.index'))
        ->assertInertia(fn (Assert $page) => $page->where('signals.data.0.locked', false));
});

it('shows premium details to the owner', function () {
    Signal::factory()->for($this->owner, 'trader')->create(['visibility' => 'premium']);

    $this->actingAs($this->owner)
        ->get(route('trader.signals.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('trader/signals/index')
            ->where('signals.data.0.locked', false));
});

it('filters the feed by instrument and status', function () {
    $eurusd = Signal::factory()->for($this->owner, 'trader')->create();
    Signal::factory()->for($this->owner, 'trader')->create([
        'instrument_id' => Instrument::where('symbol', 'XAUUSD')->value('id'),
    ]);

    $this->actingAs($this->member)
        ->get(route('signals.index', ['instrument' => $eurusd->instrument_id, 'status' => 'open']))
        ->assertInertia(fn (Assert $page) => $page->has('signals.data', 1));
});

it('protects chart images of locked signals', function () {
    $premium = Signal::factory()->for($this->owner, 'trader')->create([
        'visibility' => 'premium',
        'chart_image_path' => 'signals/chart.png',
    ]);
    Storage::disk('local')->put('signals/chart.png', 'png-bytes');

    $this->actingAs($this->member)->get(route('signals.chart', $premium))->assertForbidden();
    $this->actingAs($this->owner)->get(route('signals.chart', $premium))->assertOk();
});
