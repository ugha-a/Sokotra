<?php

use App\Actions\Ranking\RecalculateRanks;
use App\Enums\Role;
use App\Models\ChannelPlan;
use App\Models\Instrument;
use App\Models\Signal;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    fakePaymentGateway();
    $this->trader = premiumTrader();
    $this->member = userWithRoles(Role::Member);
});

it('shows open premium signals to active subscribers', function () {
    Signal::factory()->for($this->trader, 'trader')->create(['visibility' => 'premium']);

    $this->actingAs($this->member)->get(route('signals.index'))
        ->assertInertia(fn (Assert $page) => $page->where('signals.data.0.locked', true));

    paidSubscription($this->member, $this->trader);

    $this->actingAs($this->member)->get(route('signals.index'))
        ->assertInertia(fn (Assert $page) => $page->where('signals.data.0.locked', false));

    $this->travel(32)->days();

    $this->actingAs($this->member)->get(route('signals.index'))
        ->assertInertia(fn (Assert $page) => $page->where('signals.data.0.locked', true));
});

it('closes the channel to new subscribers when the rank drops, but keeps premium posting for existing ones', function () {
    paidSubscription($this->member, $this->trader);

    app(RecalculateRanks::class)->handle();

    expect(ChannelPlan::where('trader_id', $this->trader->id)->value('is_open'))->toBeFalse();

    $feed = fakePriceFeed();
    $feed->setQuote('EURUSD', '1.08000', '1.08010');

    $this->actingAs($this->trader)->post(route('trader.signals.store'), [
        'instrument_id' => Instrument::where('symbol', 'EURUSD')->value('id'),
        'direction' => 'buy',
        'order_type' => 'market',
        'stop_loss' => '1.07800',
        'take_profit' => '1.08500',
        'visibility' => 'premium',
        'analysis' => 'Pantulan dari support harian dengan struktur higher low di H4.',
    ])->assertSessionHasNoErrors();
});

it('offers the subscribe call to action on locked signals of open channels', function () {
    Signal::factory()->for($this->trader, 'trader')->create(['visibility' => 'premium']);

    $this->actingAs($this->member)->get(route('signals.index'))
        ->assertInertia(fn (Assert $page) => $page->where('signals.data.0.subscribe_url', fn ($url) => str_contains((string) $url, '/subscribe')));
});
