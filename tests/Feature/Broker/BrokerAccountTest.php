<?php

use App\Enums\BrokerAccountStatus;
use App\Enums\Role;
use App\Models\AdminAuditLog;
use App\Models\BrokerAccount;
use App\Notifications\BrokerAccountReviewed;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Notification::fake();
    $this->broker = demoBroker();
    $this->member = userWithRoles(Role::Member);
});

it('lets members link their trading account', function () {
    $this->actingAs($this->member)
        ->post(route('broker-accounts.store'), ['broker_id' => $this->broker->id, 'account_login' => '8800123', 'account_type' => 'Micro'])
        ->assertSessionHasNoErrors();

    $account = BrokerAccount::firstOrFail();

    expect($account->status)->toBe(BrokerAccountStatus::Submitted)
        ->and($account->user_id)->toBe($this->member->id);

    $this->actingAs($this->member)
        ->get(route('broker-accounts.index'))
        ->assertInertia(fn (Assert $page) => $page->component('member/broker-accounts')->has('accounts', 1)->has('brokers', 1));
});

it('allows a login only once per broker', function () {
    BrokerAccount::create(['user_id' => userWithRoles(Role::Member)->id, 'broker_id' => $this->broker->id, 'account_login' => '8800123', 'account_type' => 'Micro', 'status' => BrokerAccountStatus::Linked]);

    $this->actingAs($this->member)
        ->post(route('broker-accounts.store'), ['broker_id' => $this->broker->id, 'account_login' => '8800123', 'account_type' => 'Micro'])
        ->assertSessionHasErrors('account_login');
});

it('lets admins link or reject submitted accounts', function () {
    $account = BrokerAccount::create(['user_id' => $this->member->id, 'broker_id' => $this->broker->id, 'account_login' => '8800123', 'account_type' => 'Micro', 'status' => BrokerAccountStatus::Submitted]);
    $other = BrokerAccount::create(['user_id' => $this->member->id, 'broker_id' => $this->broker->id, 'account_login' => '8800999', 'account_type' => 'Micro', 'status' => BrokerAccountStatus::Submitted]);
    $admin = adminUser();

    $this->actingAs($this->member)->post(route('admin.broker-accounts.link', $account))->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.broker-accounts.index'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/broker-accounts/index')->has('accounts.data', 2));

    $this->actingAs($admin)->post(route('admin.broker-accounts.link', $account))->assertSessionHasNoErrors();
    $this->actingAs($admin)->post(route('admin.broker-accounts.reject', $other), ['reason' => 'Login tidak ada di laporan IB.'])->assertSessionHasNoErrors();

    expect($account->fresh()->status)->toBe(BrokerAccountStatus::Linked)
        ->and($account->fresh()->verified_by)->toBe($admin->id)
        ->and($other->fresh()->status)->toBe(BrokerAccountStatus::Rejected)
        ->and($other->fresh()->notes)->toBe('Login tidak ada di laporan IB.')
        ->and(AdminAuditLog::where('action', 'broker_account.linked')->exists())->toBeTrue();

    Notification::assertSentTo($this->member, BrokerAccountReviewed::class);
});
