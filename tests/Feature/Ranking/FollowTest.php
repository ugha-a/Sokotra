<?php

use App\Enums\Role;
use App\Models\Follow;
use App\Models\TraderProfile;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->member = userWithRoles(Role::Member);
    $this->trader = approvedTrader();
    $this->profile = TraderProfile::where('user_id', $this->trader->id)->firstOrFail();
});

it('follows and unfollows a trader', function () {
    $this->actingAs($this->member)->post(route('traders.follow', $this->profile))->assertRedirect();
    $this->actingAs($this->member)->post(route('traders.follow', $this->profile));

    expect(Follow::where('trader_id', $this->trader->id)->count())->toBe(1);

    $this->actingAs($this->member)->delete(route('traders.unfollow', $this->profile))->assertRedirect();

    expect(Follow::count())->toBe(0);
});

it('cannot follow yourself', function () {
    $this->actingAs($this->trader)
        ->post(route('traders.follow', $this->profile))
        ->assertSessionHasErrors('follow');

    expect(Follow::count())->toBe(0);
});

it('cannot follow traders who are not approved', function () {
    $applicant = TraderProfile::factory()->create();

    $this->actingAs($this->member)->post(route('traders.follow', $applicant))->assertNotFound();
});

it('requires login to follow', function () {
    $this->post(route('traders.follow', $this->profile))->assertRedirect(route('login'));
});

it('records member activity at most once per hour', function () {
    $this->travelTo(Carbon::parse('2026-10-16 10:00:00'));
    $this->actingAs($this->member)->get(route('dashboard'));
    expect($this->member->fresh()->last_active_at->format('H:i'))->toBe('10:00');

    $this->travelTo(Carbon::parse('2026-10-16 10:30:00'));
    $this->actingAs($this->member->fresh())->get(route('dashboard'));
    expect($this->member->fresh()->last_active_at->format('H:i'))->toBe('10:00');

    $this->travelTo(Carbon::parse('2026-10-16 11:05:00'));
    $this->actingAs($this->member->fresh())->get(route('dashboard'));
    expect($this->member->fresh()->last_active_at->format('H:i'))->toBe('11:05');
});
