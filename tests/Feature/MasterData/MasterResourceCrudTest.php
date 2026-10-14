<?php

use App\Enums\Role;
use App\Models\AdminAuditLog;
use App\Models\Broker;
use App\Models\ContentPage;
use App\Models\FollowerBracket;
use App\Models\Instrument;
use App\Models\PayrollRule;
use App\Models\Rank;

function validInstrument(array $overrides = []): array
{
    return [
        'symbol' => 'GBPJPY',
        'name' => 'British Pound / Japanese Yen',
        'category' => 'forex',
        'pip_size' => 0.01,
        'digits' => 3,
        'spread_pips' => 2.5,
        'min_sl_pips' => 10,
        'feed_symbol' => 'GBP/JPY',
        'is_active' => true,
        ...$overrides,
    ];
}

function validRank(array $overrides = []): array
{
    return [
        'name' => 'Grandmaster',
        'level' => 7,
        'min_closed_signals' => 400,
        'min_win_rate' => 62,
        'min_total_r' => 150,
        'min_valid_followers' => 2000,
        'max_drawdown_r' => 20,
        'can_sell_premium' => true,
        'subscription_min_price' => 250000,
        'subscription_max_price' => 1500000,
        'can_sell_products' => true,
        'color' => 'rank-6',
        'is_active' => true,
        ...$overrides,
    ];
}

function validBroker(array $overrides = []): array
{
    return [
        'name' => 'Contoh Berjangka',
        'regulator_license_no' => '123/BAPPEBTI/SI/01/2026',
        'website_url' => 'https://contoh-berjangka.co.id',
        'affiliate_url' => 'https://contoh-berjangka.co.id/daftar?ib={ref}',
        'logo_url' => null,
        'description' => 'Broker contoh.',
        'rebate_per_lot_usd' => 5,
        'sort_order' => 1,
        'account_types' => [
            ['name' => 'Micro', 'min_deposit' => 1000000, 'spread_from' => 1.2, 'commission' => 0],
        ],
        'is_active' => true,
        ...$overrides,
    ];
}

describe('instruments', function () {
    it('creates a record and audits it', function () {
        $admin = adminUser();

        $this->actingAs($admin)
            ->post(route('admin.master.store', 'instruments'), validInstrument())
            ->assertRedirect(route('admin.master.index', 'instruments'))
            ->assertSessionHasNoErrors();

        $instrument = Instrument::where('symbol', 'GBPJPY')->firstOrFail();
        $log = AdminAuditLog::latest('id')->first();

        expect((float) $instrument->pip_size)->toBe(0.01)
            ->and($log->user_id)->toBe($admin->id)
            ->and($log->action)->toBe('master.created')
            ->and($log->auditable_type)->toBe($instrument->getMorphClass())
            ->and($log->auditable_id)->toBe($instrument->id)
            ->and($log->new_values['symbol'])->toBe('GBPJPY');
    });

    it('normalises the symbol to upper case', function () {
        $this->actingAs(adminUser())
            ->post(route('admin.master.store', 'instruments'), validInstrument(['symbol' => 'gbpjpy']));

        expect(Instrument::where('symbol', 'GBPJPY')->exists())->toBeTrue();
    });

    it('validates instrument fields', function (array $overrides, string $field) {
        $this->actingAs(adminUser())
            ->post(route('admin.master.store', 'instruments'), validInstrument($overrides))
            ->assertSessionHasErrors($field);
    })->with([
        'duplicate symbol' => [['symbol' => 'EURUSD'], 'symbol'],
        'zero pip size' => [['pip_size' => 0], 'pip_size'],
        'unknown category' => [['category' => 'stocks'], 'category'],
        'too many digits' => [['digits' => 9], 'digits'],
    ]);

    it('updates a record and audits old and new values', function () {
        $instrument = Instrument::where('symbol', 'EURUSD')->firstOrFail();

        $this->actingAs(adminUser())
            ->put(route('admin.master.update', ['instruments', $instrument->id]), validInstrument([
                'symbol' => 'EURUSD',
                'name' => $instrument->name,
                'pip_size' => 0.0001,
                'digits' => 5,
                'spread_pips' => 1.2,
            ]))
            ->assertSessionHasNoErrors();

        $log = AdminAuditLog::latest('id')->first();

        expect((float) $instrument->fresh()->spread_pips)->toBe(1.2)
            ->and($log->action)->toBe('master.updated')
            ->and($log->old_values)->toHaveKey('spread_pips')
            ->and((float) $log->new_values['spread_pips'])->toBe(1.2)
            ->and($log->old_values)->not->toHaveKey('symbol');
    });

    it('toggles a record instead of deleting it', function () {
        $instrument = Instrument::where('symbol', 'XAGUSD')->firstOrFail();

        $this->actingAs(adminUser())
            ->patch(route('admin.master.toggle', ['instruments', $instrument->id]))
            ->assertRedirect();

        expect($instrument->fresh()->is_active)->toBeFalse()
            ->and(AdminAuditLog::latest('id')->first()->action)->toBe('master.toggled');
    });
});

it('forbids non-admins from writing master data', function () {
    $this->actingAs(userWithRoles(Role::Member, Role::Trader))
        ->post(route('admin.master.store', 'instruments'), validInstrument())
        ->assertForbidden();

    expect(Instrument::where('symbol', 'GBPJPY')->exists())->toBeFalse();
});

describe('ranks', function () {
    it('generates the slug from the name', function () {
        $this->actingAs(adminUser())
            ->post(route('admin.master.store', 'ranks'), validRank())
            ->assertSessionHasNoErrors();

        expect(Rank::where('slug', 'grandmaster')->exists())->toBeTrue();
    });

    it('validates rank rules', function (array $overrides, string $field) {
        $this->actingAs(adminUser())
            ->post(route('admin.master.store', 'ranks'), validRank($overrides))
            ->assertSessionHasErrors($field);
    })->with([
        'duplicate level' => [['level' => 1], 'level'],
        'premium without min price' => [['subscription_min_price' => null], 'subscription_min_price'],
        'max below min' => [['subscription_max_price' => 100000], 'subscription_max_price'],
        'win rate above 100' => [['min_win_rate' => 101], 'min_win_rate'],
        'unknown color token' => [['color' => 'pink'], 'color'],
    ]);

    it('allows ranks without premium to have no prices', function () {
        $this->actingAs(adminUser())
            ->post(route('admin.master.store', 'ranks'), validRank([
                'can_sell_premium' => false,
                'subscription_min_price' => null,
                'subscription_max_price' => null,
            ]))
            ->assertSessionHasNoErrors();
    });
});

describe('payroll rules', function () {
    it('allows only one rule per rank', function () {
        $rank = Rank::where('slug', 'elite')->firstOrFail();

        $this->actingAs(adminUser())
            ->post(route('admin.master.store', 'payroll-rules'), [
                'rank_id' => $rank->id,
                'base_salary' => 1000000,
                'per_win_amount' => 20000,
                'required_streak' => 10,
                'min_closed_signals_month' => 20,
                'max_drawdown_r_month' => 10,
                'streak_bonus_amount' => 500000,
                'max_weekly_wins_counted' => 10,
                'is_active' => true,
            ])
            ->assertSessionHasErrors('rank_id');

        expect(PayrollRule::where('rank_id', $rank->id)->count())->toBe(1);
    });
});

describe('follower brackets', function () {
    it('rejects ranges overlapping an active bracket', function (int $min, ?int $max) {
        $this->actingAs(adminUser())
            ->post(route('admin.master.store', 'follower-brackets'), [
                'min_followers' => $min,
                'max_followers' => $max,
                'bonus_amount' => 100000,
                'is_active' => true,
            ])
            ->assertSessionHasErrors('min_followers');
    })->with([
        'inside existing' => [150, 200],
        'open ended over existing' => [5000, null],
    ]);

    it('rejects a max below the min', function () {
        $this->actingAs(adminUser())
            ->post(route('admin.master.store', 'follower-brackets'), [
                'min_followers' => 300,
                'max_followers' => 200,
                'bonus_amount' => 0,
                'is_active' => false,
            ])
            ->assertSessionHasErrors('max_followers');
    });

    it('lets an existing bracket be updated without overlapping itself', function () {
        $bracket = FollowerBracket::where('min_followers', 100)->firstOrFail();

        $this->actingAs(adminUser())
            ->put(route('admin.master.update', ['follower-brackets', $bracket->id]), [
                'min_followers' => 100,
                'max_followers' => 499,
                'bonus_amount' => 300000,
                'is_active' => true,
            ])
            ->assertSessionHasNoErrors();

        expect($bracket->fresh()->bonus_amount)->toBe(300000);
    });
});

describe('brokers', function () {
    it('stores account types and a generated slug', function () {
        $this->actingAs(adminUser())
            ->post(route('admin.master.store', 'brokers'), validBroker())
            ->assertSessionHasNoErrors();

        $broker = Broker::where('slug', 'contoh-berjangka')->firstOrFail();

        expect($broker->account_types)->toBe([
            ['name' => 'Micro', 'min_deposit' => 1000000, 'spread_from' => 1.2, 'commission' => 0],
        ]);
    });

    it('requires a Bappebti licence number and valid account types', function (array $overrides, string $field) {
        $this->actingAs(adminUser())
            ->post(route('admin.master.store', 'brokers'), validBroker($overrides))
            ->assertSessionHasErrors($field);
    })->with([
        'missing licence' => [['regulator_license_no' => ''], 'regulator_license_no'],
        'invalid affiliate url' => [['affiliate_url' => 'not a url'], 'affiliate_url'],
        'account type without name' => [['account_types' => [['name' => '', 'min_deposit' => 0, 'spread_from' => 0, 'commission' => 0]]], 'account_types.0.name'],
    ]);
});

describe('content pages', function () {
    it('toggles publication', function () {
        $page = ContentPage::where('slug', 'faq')->firstOrFail();
        $wasPublished = $page->is_published;

        $this->actingAs(adminUser())
            ->patch(route('admin.master.toggle', ['content-pages', $page->id]))
            ->assertRedirect();

        expect($page->fresh()->is_published)->toBe(! $wasPublished);
    });
});
