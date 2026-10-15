<?php

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->trader = approvedTrader();
    $this->bca = Bank::where('code', 'BCA')->firstOrFail();
});

it('adds a bank account with the number encrypted at rest', function () {
    $this->actingAs($this->trader)
        ->post(route('wallet.bank-accounts.store'), [
            'bank_id' => $this->bca->id,
            'account_number' => '1234567890',
            'account_name' => 'Rina Kartika',
        ])
        ->assertSessionHasNoErrors();

    $account = BankAccount::firstOrFail();

    expect($account->account_number)->toBe('1234567890')
        ->and(DB::table('bank_accounts')->value('account_number'))->not->toContain('1234567890')
        ->and($account->user_id)->toBe($this->trader->id);
});

it('validates bank accounts', function (array $payload, string $field) {
    $this->actingAs($this->trader)
        ->post(route('wallet.bank-accounts.store'), [
            'bank_id' => $this->bca->id,
            'account_number' => '1234567890',
            'account_name' => 'Rina Kartika',
            ...$payload,
        ])
        ->assertSessionHasErrors($field);
})->with([
    'letters in number' => [['account_number' => '12AB'], 'account_number'],
    'inactive bank' => [['bank_id' => 999999], 'bank_id'],
    'missing name' => [['account_name' => ''], 'account_name'],
]);

it('limits the number of bank accounts', function () {
    BankAccount::factory()->count(3)->for($this->trader)->create();

    $this->actingAs($this->trader)
        ->post(route('wallet.bank-accounts.store'), ['bank_id' => $this->bca->id, 'account_number' => '9999999', 'account_name' => 'Rina'])
        ->assertSessionHasErrors('bank_account');
});

it('deletes unused accounts only and only your own', function () {
    $mine = BankAccount::factory()->for($this->trader)->create();
    $used = BankAccount::factory()->for($this->trader)->create();
    Withdrawal::factory()->for($this->trader)->create(['bank_account_id' => $used->id]);
    $theirs = BankAccount::factory()->create();

    $this->actingAs($this->trader)->delete(route('wallet.bank-accounts.destroy', $mine))->assertSessionHasNoErrors();
    $this->actingAs($this->trader)->delete(route('wallet.bank-accounts.destroy', $used))->assertSessionHasErrors('bank_account');
    $this->actingAs($this->trader)->delete(route('wallet.bank-accounts.destroy', $theirs))->assertForbidden();

    expect(BankAccount::whereKey($mine->id)->exists())->toBeFalse()
        ->and(BankAccount::whereKey($used->id)->exists())->toBeTrue();
});
