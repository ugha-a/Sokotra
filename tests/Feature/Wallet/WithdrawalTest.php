<?php

use App\Enums\Role;
use App\Enums\WithdrawalStatus;
use App\Models\AdminAuditLog;
use App\Models\BankAccount;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\WithdrawalUpdated;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Notification::fake();
    $this->trader = approvedTrader();
    verifiedKyc($this->trader, 'Rina Kartika');
    $this->account = BankAccount::factory()->for($this->trader)->create(['account_name' => 'RINA  kartika']);
    credit($this->trader, 300000);
});

function requestWithdrawal(int $amount = 150000, ?int $accountId = null)
{
    return test()->actingAs(test()->trader)->post(route('wallet.withdrawals.store'), [
        'amount' => $amount,
        'bank_account_id' => $accountId ?? test()->account->id,
    ]);
}

it('locks the amount plus fee when a withdrawal is requested', function () {
    requestWithdrawal()->assertSessionHasNoErrors();

    $withdrawal = Withdrawal::firstOrFail();
    $wallet = Wallet::where('user_id', $this->trader->id)->firstOrFail();

    expect($withdrawal->status)->toBe(WithdrawalStatus::Requested)
        ->and($withdrawal->amount)->toBe(150000)
        ->and($withdrawal->fee)->toBe(6500)
        ->and($withdrawal->net_amount)->toBe(150000)
        ->and($withdrawal->name_mismatch)->toBeFalse()
        ->and($wallet->available_balance)->toBe(143500)
        ->and($wallet->locked_balance)->toBe(156500);
});

it('validates withdrawal requests', function (Closure $setup, int $amount, string $field) {
    $setup();

    requestWithdrawal($amount)->assertSessionHasErrors($field);

    expect(Withdrawal::count())->toBe(0);
})->with([
    'below minimum' => [fn () => null, 50000, 'amount'],
    'more than available incl. fee' => [fn () => null, 295000, 'amount'],
    'kyc not verified' => [fn () => test()->trader->kycVerification()->update(['status' => 'submitted']), 150000, 'withdrawal'],
]);

it('rejects bank accounts of other users', function () {
    requestWithdrawal(150000, BankAccount::factory()->create()->id)->assertSessionHasErrors('bank_account_id');
});

it('allows only one open withdrawal at a time', function () {
    requestWithdrawal(100000);
    requestWithdrawal(100000)->assertSessionHasErrors('withdrawal');

    expect(Withdrawal::count())->toBe(1);
});

it('flags account names that do not match the KYC name', function () {
    $other = BankAccount::factory()->for($this->trader)->create(['account_name' => 'Budi Santoso']);

    requestWithdrawal(150000, $other->id)->assertSessionHasNoErrors();

    expect(Withdrawal::firstOrFail()->name_mismatch)->toBeTrue();
});

describe('admin review', function () {
    beforeEach(function () {
        requestWithdrawal();
        $this->withdrawal = Withdrawal::firstOrFail();
        $this->admin = adminUser();
    });

    it('is admin only', function () {
        $this->actingAs($this->trader)->get(route('admin.withdrawals.index'))->assertForbidden();
        $this->actingAs($this->trader)->post(route('admin.withdrawals.approve', $this->withdrawal))->assertForbidden();
    });

    it('lists withdrawals by status', function () {
        $this->actingAs($this->admin)
            ->get(route('admin.withdrawals.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/withdrawals/index')
                ->where('status', 'requested')
                ->has('withdrawals.data', 1)
                ->where('withdrawals.data.0.account_number', fn (string $number) => str_contains($number, '*'))
                ->where('withdrawals.data.0.kyc_name', 'Rina Kartika'));
    });

    it('approves, then marks paid with a transfer reference', function () {
        $this->actingAs($this->admin)->post(route('admin.withdrawals.approve', $this->withdrawal))->assertSessionHasNoErrors();
        expect($this->withdrawal->fresh()->status)->toBe(WithdrawalStatus::Processing);

        $this->actingAs($this->admin)
            ->post(route('admin.withdrawals.paid', $this->withdrawal), ['reference' => 'TRF-001'])
            ->assertSessionHasNoErrors();

        $wallet = Wallet::where('user_id', $this->trader->id)->firstOrFail();
        $withdrawal = $this->withdrawal->fresh();

        expect($withdrawal->status)->toBe(WithdrawalStatus::Paid)
            ->and($withdrawal->disbursement_reference)->toBe('TRF-001')
            ->and($withdrawal->reviewed_by)->toBe($this->admin->id)
            ->and($wallet->locked_balance)->toBe(0)
            ->and($wallet->available_balance)->toBe(143500)
            ->and(AdminAuditLog::latest('id')->value('action'))->toBe('withdrawal.paid');

        Notification::assertSentTo($this->trader, WithdrawalUpdated::class);
    });

    it('rejects with a reason and unlocks the balance', function () {
        $this->actingAs($this->admin)
            ->post(route('admin.withdrawals.reject', $this->withdrawal), ['reason' => 'Rekening tidak aktif.'])
            ->assertSessionHasNoErrors();

        $wallet = Wallet::where('user_id', $this->trader->id)->firstOrFail();

        expect($this->withdrawal->fresh()->status)->toBe(WithdrawalStatus::Rejected)
            ->and($this->withdrawal->fresh()->rejection_reason)->toBe('Rekening tidak aktif.')
            ->and($wallet->available_balance)->toBe(300000)
            ->and($wallet->locked_balance)->toBe(0);
    });

    it('marks a processing transfer as failed and unlocks the balance', function () {
        $this->actingAs($this->admin)->post(route('admin.withdrawals.approve', $this->withdrawal));
        $this->actingAs($this->admin)->post(route('admin.withdrawals.reject', $this->withdrawal), ['reason' => 'Transfer gagal.']);

        expect($this->withdrawal->fresh()->status)->toBe(WithdrawalStatus::Failed)
            ->and(Wallet::where('user_id', $this->trader->id)->value('available_balance'))->toBe(300000);
    });

    it('cannot pay a withdrawal that was not approved', function () {
        $this->actingAs($this->admin)
            ->post(route('admin.withdrawals.paid', $this->withdrawal), ['reference' => 'TRF-001'])
            ->assertSessionHasErrors('withdrawal');
    });
});

it('does not let members without income request a withdrawal page action from others', function () {
    $this->actingAs(userWithRoles(Role::Member))->get(route('wallet.show'))->assertOk();
});
