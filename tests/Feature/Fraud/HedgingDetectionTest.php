<?php

use App\Enums\FraudFlagStatus;
use App\Models\FraudFlag;
use App\Models\Instrument;
use App\Models\Signal;
use App\Models\TraderProfile;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->feed = fakePriceFeed();
    $this->feed->setQuote('EURUSD', '1.08000', '1.08010');
    $this->a = approvedTrader();
    $this->b = approvedTrader();
});

function publishFrom(User $trader, string $direction, string $ip = '10.0.0.7')
{
    return test()->actingAs($trader)->withServerVariables(['REMOTE_ADDR' => $ip])->post(route('trader.signals.store'), [
        'instrument_id' => Instrument::where('symbol', 'EURUSD')->value('id'),
        'direction' => $direction,
        'order_type' => 'market',
        'stop_loss' => $direction === 'buy' ? '1.07800' : '1.08200',
        'take_profit' => $direction === 'buy' ? '1.08500' : '1.07500',
        'visibility' => 'free',
        'analysis' => 'Analisa struktur pasar harian dengan konfirmasi momentum di H4.',
    ]);
}

it('flags opposite signals from different traders on the same network and excludes them from payroll', function () {
    publishFrom($this->a, 'buy')->assertSessionHasNoErrors();
    $this->travel(4)->minutes();
    publishFrom($this->b, 'sell')->assertSessionHasNoErrors();

    $flag = FraudFlag::firstOrFail();

    expect($flag->type->value)->toBe('hedging')
        ->and($flag->status)->toBe(FraudFlagStatus::Open)
        ->and($flag->subject_user_ids)->toEqualCanonicalizing([$this->a->id, $this->b->id])
        ->and(Signal::where('excluded_from_payroll', true)->count())->toBe(2);
});

it('ignores signals outside the window, from other networks, or in the same direction', function () {
    publishFrom($this->a, 'buy');
    $this->travel(11)->minutes();
    publishFrom($this->b, 'sell');

    publishFrom(approvedTrader(), 'buy', '10.9.9.9');
    publishFrom(approvedTrader(), 'sell');

    expect(FraudFlag::count())->toBe(0);
});

it('lets admins confirm (flag traders) or dismiss (restore payroll) a hedging flag', function () {
    publishFrom($this->a, 'buy');
    publishFrom($this->b, 'sell');
    $flag = FraudFlag::firstOrFail();
    $admin = adminUser();

    $this->actingAs($admin)->get(route('admin.fraud-flags.index'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/fraud-flags/index')->has('flags.data', 1));

    $this->actingAs($admin)->post(route('admin.fraud-flags.dismiss', $flag), ['notes' => 'Kantor yang sama, analisa berbeda.'])->assertSessionHasNoErrors();

    expect($flag->fresh()->status)->toBe(FraudFlagStatus::Dismissed)
        ->and(Signal::where('excluded_from_payroll', true)->count())->toBe(0);

    $this->travel(1)->minutes();
    publishFrom($this->a, 'buy');
    publishFrom($this->b, 'sell');
    $second = FraudFlag::latest('id')->firstOrFail();

    $this->actingAs($admin)->post(route('admin.fraud-flags.confirm', $second), ['notes' => 'Terbukti hedging.'])->assertSessionHasNoErrors();

    expect(TraderProfile::whereIn('user_id', [$this->a->id, $this->b->id])->where('is_flagged', true)->count())->toBe(2);
});
