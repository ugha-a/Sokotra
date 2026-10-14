<?php

use App\Enums\Role;

it('has the three platform roles', function () {
    expect(array_map(fn (Role $role) => $role->value, Role::cases()))
        ->toBe(['member', 'trader', 'admin']);
});

it('provides an Indonesian label for each role', function (Role $role, string $label) {
    expect($role->label())->toBe($label);
})->with([
    [Role::Member, 'Member'],
    [Role::Trader, 'Trader'],
    [Role::Admin, 'Admin'],
]);
