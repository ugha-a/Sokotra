<?php

use App\Enums\Role;
use App\Models\BrokerReferral;
use Inertia\Testing\AssertableInertia as Assert;

it('requires login to view brokers directory', function () {
    $this->get(route('brokers.index'))->assertRedirect(route('login'));
});

it('lists active licensed brokers for members', function () {
    demoBroker();
    demoBroker(['name' => 'Nonaktif', 'slug' => 'nonaktif', 'is_active' => false]);

    $this->actingAs(userWithRoles(Role::Member))
        ->get(route('brokers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/brokers')
            ->has('brokers', 1)
            ->where('brokers.0.regulator_license_no', '123/BAPPEBTI/SI/01/2026')
            ->has('brokers.0.account_types', 1)
            ->missing('brokers.0.rebate_per_lot_usd')
            ->missing('brokers.0.affiliate_url'));
});

it('records a referral and sends the member to the affiliate link', function () {
    $broker = demoBroker();
    $member = userWithRoles(Role::Member);

    $response = $this->actingAs($member)->get(route('brokers.open', $broker));

    $referral = BrokerReferral::firstOrFail();

    expect($referral->user_id)->toBe($member->id)
        ->and($referral->ref_code)->toHaveLength(10);

    $response->assertRedirect("https://contoh-berjangka.co.id/daftar?ib={$referral->ref_code}");
});

it('requires login and an active broker to open an account', function () {
    $broker = demoBroker();
    $this->get(route('brokers.open', $broker))->assertRedirect(route('login'));

    $inactive = demoBroker(['name' => 'Off', 'slug' => 'off', 'is_active' => false]);
    $this->actingAs(userWithRoles(Role::Member))->get(route('brokers.open', $inactive))->assertNotFound();
});
