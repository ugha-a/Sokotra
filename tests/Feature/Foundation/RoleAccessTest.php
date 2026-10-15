<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('seeds every platform role', function () {
    expect(Spatie\Permission\Models\Role::pluck('name')->sort()->values()->all())
        ->toBe(['admin', 'member', 'trader']);
});

it('assigns the member role to newly registered users', function () {
    $this->post(route('register.store'), [
        'name' => 'Budi',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'accept_terms' => true,
    ]);

    expect(User::where('email', 'budi@example.com')->first()->hasRole(Role::Member->value))
        ->toBeTrue();
});

it('redirects guests away from role areas', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with(['trader.dashboard', 'admin.dashboard']);

it('forbids members from trader and admin areas', function (string $route) {
    $this->actingAs(userWithRoles(Role::Member))
        ->get(route($route))
        ->assertForbidden();
})->with(['trader.dashboard', 'admin.dashboard']);

it('lets traders open the trader dashboard only', function () {
    $trader = approvedTrader();

    $this->actingAs($trader)
        ->get(route('trader.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('trader/dashboard'));

    $this->actingAs($trader)->get(route('admin.dashboard'))->assertForbidden();
});

it('lets admins open the admin dashboard', function () {
    $this->actingAs(userWithRoles(Role::Admin))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/dashboard'));
});

it('shares the user roles with the frontend', function () {
    $this->actingAs(userWithRoles(Role::Member, Role::Trader))
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.roles', ['member', 'trader']));
});

it('shares an empty role list with guests', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.roles', []));
});
