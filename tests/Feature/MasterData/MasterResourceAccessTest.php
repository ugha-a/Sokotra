<?php

use App\Enums\Role;
use Inertia\Testing\AssertableInertia as Assert;

dataset('master resources', [
    'instruments',
    'ranks',
    'payroll-rules',
    'follower-brackets',
    'brokers',
    'product-categories',
    'banks',
    'content-pages',
]);

it('forbids non-admins from master data', function (string $resource) {
    $this->actingAs(userWithRoles(Role::Member, Role::Trader))
        ->get(route('admin.master.index', $resource))
        ->assertForbidden();
})->with('master resources');

it('renders every master resource with its schema and records', function (string $resource) {
    $this->actingAs(adminUser())
        ->get(route('admin.master.index', $resource))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/master/index')
            ->where('resource.key', $resource)
            ->has('resource.label')
            ->has('resource.fields')
            ->has('resource.columns')
            ->has('resources', 8)
            ->has('records'));
})->with('master resources');

it('returns 404 for unknown master resources', function () {
    $this->actingAs(adminUser())
        ->get(route('admin.master.index', 'unknown-thing'))
        ->assertNotFound();
});
