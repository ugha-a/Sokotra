<?php

use App\Enums\Role;
use Inertia\Testing\AssertableInertia as Assert;

it('forbids non-admins from the audit log', function () {
    $this->actingAs(userWithRoles(Role::Member))
        ->get(route('admin.audit-logs.index'))
        ->assertForbidden();
});

it('lists the latest admin changes with their actor', function () {
    $admin = adminUser();

    $this->actingAs($admin)->put(route('admin.settings.update'), [
        'settings' => ['wallet' => ['withdraw_fee' => 5000]],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/audit-logs')
            ->has('logs.data', 1)
            ->where('logs.data.0.action', 'settings.updated')
            ->where('logs.data.0.user.name', $admin->name));
});
