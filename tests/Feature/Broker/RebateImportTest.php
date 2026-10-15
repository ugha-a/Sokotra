<?php

use App\Enums\BrokerAccountStatus;
use App\Enums\Role;
use App\Models\BrokerAccount;
use App\Models\BrokerRebate;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->broker = demoBroker();
    $this->account = BrokerAccount::create([
        'user_id' => userWithRoles(Role::Member)->id,
        'broker_id' => $this->broker->id,
        'account_login' => '8800123',
        'account_type' => 'Micro',
        'status' => BrokerAccountStatus::Linked,
    ]);
});

function rebateCsv(string $body): UploadedFile
{
    return UploadedFile::fake()->createWithContent('rebate.csv', "account_login,lots,rebate_usd\n{$body}");
}

function importRebates(UploadedFile $file, string $period = '2026-09', string $fx = '16250')
{
    return test()->actingAs(adminUser())->post(route('admin.broker-rebates.import'), [
        'broker_id' => test()->broker->id,
        'period' => $period,
        'fx_rate' => $fx,
        'file' => $file,
    ]);
}

it('imports rebates, matching linked accounts and flagging unknown logins', function () {
    importRebates(rebateCsv("8800123,12.5,62.50\n7700001,2,10"))->assertSessionHasNoErrors();

    $matched = BrokerRebate::where('account_login', '8800123')->firstOrFail();
    $unmatched = BrokerRebate::where('account_login', '7700001')->firstOrFail();

    expect($matched->broker_account_id)->toBe($this->account->id)
        ->and((float) $matched->lots)->toBe(12.5)
        ->and($matched->rebate_idr)->toBe(1015625)
        ->and($unmatched->broker_account_id)->toBeNull()
        ->and($matched->period)->toBe('2026-09');
});

it('replaces rows when the same period is imported again', function () {
    importRebates(rebateCsv('8800123,12.5,62.50'));
    importRebates(rebateCsv('8800123,13,65'));

    expect(BrokerRebate::count())->toBe(1)
        ->and((float) BrokerRebate::first()->rebate_usd)->toBe(65.0);
});

it('rejects files without the required columns', function () {
    importRebates(UploadedFile::fake()->createWithContent('rebate.csv', "login,volume\n8800123,1"))
        ->assertSessionHasErrors('file');

    expect(BrokerRebate::count())->toBe(0);
});

it('rejects rows with invalid numbers and imports nothing', function () {
    importRebates(rebateCsv("8800123,abc,62.50\n7700001,2,10"))->assertSessionHasErrors('file');

    expect(BrokerRebate::count())->toBe(0);
});

it('summarises IB revenue for admins', function () {
    importRebates(rebateCsv("8800123,12.5,62.50\n7700001,2,10"));

    $this->actingAs(adminUser())
        ->get(route('admin.broker-rebates.index', ['period' => '2026-09']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/broker-rebates/index')
            ->where('period', '2026-09')
            ->has('rows', 2)
            ->where('totals.rebate_idr', 1178125)
            ->where('totals.unmatched', 1)
            ->has('byBroker', 1));
});
