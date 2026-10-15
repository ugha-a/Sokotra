<?php

use App\Actions\Wallet\ReconcileWallets;
use App\Models\AdminAuditLog;
use App\Models\BankAccount;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Scheduling\Schedule;
use Inertia\Testing\AssertableInertia as Assert;

it('shows balances, ledger, accounts, withdrawals and rules', function () {
    $trader = approvedTrader();
    credit($trader, 250000);
    BankAccount::factory()->for($trader)->create(['account_number' => '1234567890']);

    $this->actingAs($trader)
        ->get(route('wallet.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('wallet/show')
            ->where('wallet.available_balance', 250000)
            ->has('transactions.data', 1)
            ->where('transactions.data.0.type_label', 'Koreksi admin')
            ->has('bankAccounts', 1)
            ->where('bankAccounts.0.account_number', '******7890')
            ->has('banks')
            ->where('rules.min_withdraw', 100000)
            ->where('rules.withdraw_fee', 6500)
            ->where('kycVerified', false));
});

it('lets admins adjust a wallet with a mandatory note', function () {
    $user = User::factory()->create();
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('admin.wallets.adjust', $user), ['amount' => 50000, 'note' => ''])
        ->assertSessionHasErrors('note');

    $this->actingAs($admin)
        ->post(route('admin.wallets.adjust', $user), ['amount' => 50000, 'note' => 'Bonus kompetisi Oktober'])
        ->assertSessionHasNoErrors();

    expect(Wallet::where('user_id', $user->id)->value('available_balance'))->toBe(50000)
        ->and(AdminAuditLog::latest('id')->value('action'))->toBe('wallet.adjusted');
});

it('lists wallets for admins', function () {
    credit(User::factory()->create(), 1000);

    $this->actingAs(adminUser())
        ->get(route('admin.wallets.index'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/wallets/index')->has('wallets.data', 1));
});

it('detects wallets whose cached balance drifted from the ledger', function () {
    $user = User::factory()->create();
    credit($user, 1000);
    Wallet::where('user_id', $user->id)->update(['available_balance' => 5000]);

    $mismatches = app(ReconcileWallets::class)->handle();

    expect($mismatches)->toHaveCount(1)
        ->and($mismatches[0]['user_id'])->toBe($user->id)
        ->and($mismatches[0]['bucket'])->toBe('available');
});

it('reconciles daily', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($event) => str_contains((string) $event->command, 'wallets:reconcile'));

    expect($event?->expression)->toBe('30 0 * * *');
});
