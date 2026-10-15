<?php

use App\Enums\Role;
use App\Models\TraderProfile;

it('throttles follow spam', function () {
    $member = userWithRoles(Role::Member);
    $profile = TraderProfile::where('user_id', approvedTrader()->id)->firstOrFail();

    for ($i = 0; $i < 30; $i++) {
        $this->actingAs($member)->post(route('traders.follow', $profile))->assertRedirect();
    }

    $this->actingAs($member)->post(route('traders.follow', $profile))->assertTooManyRequests();
});

it('throttles signal publishing per trader', function () {
    $trader = approvedTrader();

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($trader)->post(route('trader.signals.store'), []);
    }

    $this->actingAs($trader)->post(route('trader.signals.store'), [])->assertTooManyRequests();
});

it('throttles checkouts and withdrawals', function (string $route, int $limit) {
    $member = userWithRoles(Role::Member);

    for ($i = 0; $i < $limit; $i++) {
        $this->actingAs($member)->post($route, []);
    }

    $this->actingAs($member)->post($route, [])->assertTooManyRequests();
})->with([
    'withdrawal' => [fn () => route('wallet.withdrawals.store'), 5],
    'trader application' => [fn () => route('become-trader.store'), 5],
]);
