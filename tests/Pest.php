<?php

use App\Enums\Role;
use App\Enums\SignalStatus;
use App\Models\Follow;
use App\Models\Rank;
use App\Models\Signal;
use App\Models\TraderProfile;
use App\Models\User;
use App\Services\PriceFeed\FakePriceFeed;
use App\Services\PriceFeed\PriceFeed;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function userWithRoles(Role ...$roles): User
{
    $user = User::factory()->create();
    $user->assignRole(array_map(fn (Role $role) => $role->value, $roles));

    return $user;
}

function adminUser(): User
{
    return userWithRoles(Role::Member, Role::Admin);
}

function fakePriceFeed(): FakePriceFeed
{
    $feed = new FakePriceFeed;
    app()->instance(PriceFeed::class, $feed);

    return $feed;
}

function approvedTrader(string $rankSlug = 'newbie'): User
{
    $user = userWithRoles(Role::Member, Role::Trader);

    TraderProfile::factory()->approved()->for($user)->create([
        'rank_id' => Rank::where('slug', $rankSlug)->value('id'),
    ]);

    return $user;
}

/**
 * Closed signals for a trader with the given R results, closed one hour apart ending now.
 *
 * @param  list<float>  $results
 */
function closedSignalsFor(User $trader, array $results, ?CarbonInterface $endingAt = null): void
{
    $end = CarbonImmutable::instance($endingAt ?? now());
    $count = count($results);

    foreach ($results as $i => $r) {
        $closedAt = $end->subHours($count - 1 - $i);

        Signal::factory()->for($trader, 'trader')->create([
            'status' => $r > 0 ? SignalStatus::TpHit : SignalStatus::SlHit,
            'rr' => $r > 0 ? $r : 2,
            'result_r' => $r,
            'result_pips' => $r * 20,
            'published_at' => $closedAt->subHour(),
            'triggered_at' => $closedAt->subHour(),
            'closed_at' => $closedAt,
        ]);
    }
}

function followTrader(User $trader, int $count = 1, bool $valid = true): void
{
    User::factory()->count($count)->create([
        'email_verified_at' => $valid ? now() : null,
        'last_active_at' => now(),
    ])->each(fn (User $user) => Follow::create(['follower_id' => $user->id, 'trader_id' => $trader->id]));
}
