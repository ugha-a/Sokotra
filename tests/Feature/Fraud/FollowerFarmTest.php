<?php

use App\Actions\Fraud\ScanFollowerFarms;
use App\Models\Follow;
use App\Models\FraudFlag;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;

it('records the registration IP', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '10.1.2.3'])->post(route('register.store'), [
        'name' => 'Budi',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'accept_terms' => true,
    ]);

    expect(User::where('email', 'budi@example.com')->value('registration_ip'))->toBe('10.1.2.3');
});

it('flags many new followers of one trader registered from the same IP', function () {
    $trader = approvedTrader();
    User::factory()->count(5)->create(['registration_ip' => '10.5.5.5'])
        ->each(fn (User $user) => Follow::create(['follower_id' => $user->id, 'trader_id' => $trader->id]));
    User::factory()->count(4)->create(['registration_ip' => '10.6.6.6'])
        ->each(fn (User $user) => Follow::create(['follower_id' => $user->id, 'trader_id' => $trader->id]));

    app(ScanFollowerFarms::class)->handle();
    app(ScanFollowerFarms::class)->handle();

    $flag = FraudFlag::firstOrFail();

    expect(FraudFlag::count())->toBe(1)
        ->and($flag->type->value)->toBe('follower_farm')
        ->and($flag->subject_user_ids)->toHaveCount(5)
        ->and($flag->details['trader_id'])->toBe($trader->id);

    $this->actingAs(adminUser())->post(route('admin.fraud-flags.confirm', $flag), ['notes' => 'Akun buatan.']);

    expect(User::where('registration_ip', '10.5.5.5')->where('is_fraud_flagged', true)->count())->toBe(5);
});

it('scans daily', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($event) => str_contains((string) $event->command, 'fraud:scan'));

    expect($event?->expression)->toBe('45 0 * * *');
});
