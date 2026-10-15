<?php

use App\Enums\Role;
use App\Models\ChannelPlan;
use Inertia\Testing\AssertableInertia as Assert;

it('lets eligible traders set a price within their rank range', function () {
    $trader = approvedTrader('elite');
    verifiedKyc($trader);

    $this->actingAs($trader)
        ->put(route('trader.channel.update'), ['monthly_price' => 200000, 'description' => 'Swing XAUUSD', 'is_open' => true])
        ->assertSessionHasNoErrors();

    expect(ChannelPlan::where('trader_id', $trader->id)->firstOrFail()->monthly_price)->toBe(200000);
});

it('enforces the rank price range', function (int $price) {
    $trader = approvedTrader('elite');
    verifiedKyc($trader);

    $this->actingAs($trader)
        ->put(route('trader.channel.update'), ['monthly_price' => $price, 'description' => 'Swing', 'is_open' => true])
        ->assertSessionHasErrors('monthly_price');
})->with([50000, 400000]);

it('requires a premium rank and verified KYC', function (string $rank, bool $kyc) {
    $trader = approvedTrader($rank);
    if ($kyc) {
        verifiedKyc($trader);
    }

    $this->actingAs($trader)
        ->put(route('trader.channel.update'), ['monthly_price' => 150000, 'description' => 'Swing', 'is_open' => true])
        ->assertSessionHasErrors('channel');
})->with([['newbie', true], ['elite', false]]);

it('shows the channel page with range, plan and subscribers', function () {
    $trader = premiumTrader();
    paidSubscription(userWithRoles(Role::Member), $trader);

    $this->actingAs($trader)
        ->get(route('trader.channel'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('trader/channel')
            ->where('canSell', true)
            ->where('range.min', 100000)
            ->where('range.max', 300000)
            ->where('plan.monthly_price', 150000)
            ->has('subscribers', 1)
            ->where('activeSubscribers', 1));
});
