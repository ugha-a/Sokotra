<?php

use App\Enums\Role;
use App\Enums\SignalStatus;
use App\Enums\TraderStatus;
use App\Models\Instrument;
use App\Models\Signal;
use App\Models\TraderProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    $this->feed = fakePriceFeed();
    $this->feed->setQuote('EURUSD', '1.08000', '1.08010');
    $this->feed->setQuote('USDJPY', '150.000', '150.015');
    $this->trader = approvedTrader();
});

function signalPayload(array $overrides = []): array
{
    return [
        'instrument_id' => Instrument::where('symbol', 'EURUSD')->value('id'),
        'direction' => 'buy',
        'order_type' => 'market',
        'entry_price' => null,
        'stop_loss' => '1.07800',
        'take_profit' => '1.08500',
        'visibility' => 'free',
        'analysis' => 'Harga memantul dari support harian 1.0780 dengan struktur higher low di H4.',
        ...$overrides,
    ];
}

it('only lets approved, active traders open the signal form', function () {
    $this->actingAs(userWithRoles(Role::Member))->get(route('trader.signals.create'))->assertForbidden();

    $this->actingAs($this->trader)
        ->get(route('trader.signals.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('trader/signals/create')
            ->has('instruments', 9)
            ->where('instruments.0.digits', fn ($digits) => is_int($digits))
            ->where('rules.min_rr', 1)
            ->where('canPublishPremium', false)
            ->where('blockReason', null));
});

it('blocks suspended traders from publishing', function () {
    TraderProfile::where('user_id', $this->trader->id)->update(['status' => TraderStatus::Suspended]);

    $this->actingAs($this->trader)
        ->post(route('trader.signals.store'), signalPayload())
        ->assertSessionHasErrors('signal');

    expect(Signal::count())->toBe(0);
});

it('publishes a market buy at the live ask price', function () {
    $this->actingAs($this->trader)
        ->post(route('trader.signals.store'), signalPayload(['entry_price' => '1.07000']))
        ->assertRedirect(route('trader.signals.index'))
        ->assertSessionHasNoErrors();

    $signal = Signal::firstOrFail();

    expect($signal->status)->toBe(SignalStatus::Active)
        ->and($signal->entry_price)->toBe('1.08010')
        ->and($signal->trader_id)->toBe($this->trader->id)
        ->and($signal->pip_size_snapshot)->toBe('0.00010')
        ->and($signal->digits_snapshot)->toBe(5)
        ->and((float) $signal->rr)->toBe(2.33)
        ->and((float) $signal->sl_pips)->toBe(21.0)
        ->and($signal->published_at)->not->toBeNull()
        ->and($signal->triggered_at)->not->toBeNull()
        ->and($signal->publish_ip)->not->toBeNull()
        ->and($signal->events()->pluck('type')->all())->toBe(['published', 'triggered']);
});

it('publishes a market sell at the live bid price', function () {
    $this->actingAs($this->trader)->post(route('trader.signals.store'), signalPayload([
        'direction' => 'sell',
        'stop_loss' => '1.08200',
        'take_profit' => '1.07500',
    ]));

    expect(Signal::firstOrFail()->entry_price)->toBe('1.08000');
});

it('publishes a pending limit order with an expiry', function () {
    $this->freezeSecond();

    $this->actingAs($this->trader)->post(route('trader.signals.store'), signalPayload([
        'order_type' => 'limit',
        'entry_price' => '1.07900',
        'stop_loss' => '1.07700',
        'take_profit' => '1.08400',
    ]))->assertSessionHasNoErrors();

    $signal = Signal::firstOrFail();

    expect($signal->status)->toBe(SignalStatus::Pending)
        ->and($signal->entry_price)->toBe('1.07900')
        ->and($signal->triggered_at)->toBeNull()
        ->and($signal->expires_at->equalTo(now()->addHours(24)))->toBeTrue();
});

it('validates signal levels and rules', function (array $overrides, string $field) {
    $this->actingAs($this->trader)
        ->post(route('trader.signals.store'), signalPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(Signal::count())->toBe(0);
})->with([
    'buy with SL above entry' => [['stop_loss' => '1.08300'], 'stop_loss'],
    'sell with TP above entry' => [['direction' => 'sell', 'stop_loss' => '1.08300', 'take_profit' => '1.08600'], 'take_profit'],
    'risk reward below minimum' => [['take_profit' => '1.08100'], 'take_profit'],
    'stop loss too close' => [['stop_loss' => '1.07950', 'take_profit' => '1.08200'], 'stop_loss'],
    'premium without rank permission' => [['visibility' => 'premium'], 'visibility'],
    'buy limit above market' => [['order_type' => 'limit', 'entry_price' => '1.08200', 'stop_loss' => '1.08000', 'take_profit' => '1.08700'], 'entry_price'],
    'buy stop below market' => [['order_type' => 'stop', 'entry_price' => '1.07900', 'stop_loss' => '1.07700', 'take_profit' => '1.08400'], 'entry_price'],
    'pending order without entry' => [['order_type' => 'limit', 'entry_price' => null], 'entry_price'],
    'analysis too short' => [['analysis' => 'Naik.'], 'analysis'],
    'prohibited claim' => [['analysis' => 'Setup ini pasti profit karena support kuat sekali di area harian.'], 'analysis'],
]);

it('rejects inactive instruments', function () {
    $instrument = Instrument::where('symbol', 'EURUSD')->firstOrFail();
    $instrument->update(['is_active' => false]);

    $this->actingAs($this->trader)
        ->post(route('trader.signals.store'), signalPayload())
        ->assertSessionHasErrors('instrument_id');
});

it('limits the number of open signals per trader', function () {
    Signal::factory()->count(5)->for($this->trader, 'trader')->create();

    $this->actingAs($this->trader)
        ->post(route('trader.signals.store'), signalPayload())
        ->assertSessionHasErrors('signal');
});

it('allows premium signals for ranks that can sell premium', function () {
    $elite = approvedTrader('elite');

    $this->actingAs($elite)
        ->post(route('trader.signals.store'), signalPayload(['visibility' => 'premium']))
        ->assertSessionHasNoErrors();

    expect(Signal::firstOrFail()->visibility->value)->toBe('premium');
});

it('stores the chart image privately', function () {
    $this->actingAs($this->trader)->post(route('trader.signals.store'), signalPayload([
        'chart_image' => UploadedFile::fake()->image('chart.png', 1200, 700),
    ]));

    $signal = Signal::firstOrFail();

    expect($signal->chart_image_path)->toStartWith("signals/{$this->trader->id}/");
    Storage::disk('local')->assertExists($signal->chart_image_path);
});

it('rejects a market order whose stop loss is already crossed by the live price', function () {
    $this->feed->setQuote('EURUSD', '1.07700', '1.07710');

    $this->actingAs($this->trader)
        ->post(route('trader.signals.store'), signalPayload())
        ->assertSessionHasErrors('stop_loss');
});

it('serves live quotes to traders only', function () {
    $instrument = Instrument::where('symbol', 'USDJPY')->firstOrFail();

    $this->actingAs($this->trader)
        ->getJson(route('trader.quotes.show', $instrument))
        ->assertOk()
        ->assertJson(['bid' => '150.000', 'ask' => '150.015']);

    $this->actingAs(userWithRoles(Role::Member))
        ->getJson(route('trader.quotes.show', $instrument))
        ->assertForbidden();
});

it('reports when the price feed is unavailable', function () {
    $this->feed->failFor('EURUSD');

    $this->actingAs($this->trader)
        ->post(route('trader.signals.store'), signalPayload())
        ->assertSessionHasErrors('signal');
});
