<?php

use App\Enums\Role;
use App\Models\AdminAuditLog;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('lists and searches users for admins only', function () {
    User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.com']);
    User::factory()->create(['name' => 'Rina Kartika']);

    $this->actingAs(userWithRoles(Role::Member))->get(route('admin.users.index'))->assertForbidden();

    $this->actingAs(adminUser())
        ->get(route('admin.users.index', ['search' => 'budi']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/index')
            ->has('users.data', 1)
            ->where('users.data.0.email', 'budi@example.com')
            ->has('users.data.0.roles'));
});

it('flags and unflags accounts for fraud with an audit trail', function () {
    $user = User::factory()->create();

    $this->actingAs(adminUser())->post(route('admin.users.flag', $user), ['reason' => 'Akun follower palsu'])->assertSessionHasNoErrors();
    expect($user->fresh()->is_fraud_flagged)->toBeTrue()
        ->and(AdminAuditLog::latest('id')->value('action'))->toBe('user.flagged');

    $this->actingAs(adminUser())->post(route('admin.users.unflag', $user))->assertSessionHasNoErrors();
    expect($user->fresh()->is_fraud_flagged)->toBeFalse();
});
