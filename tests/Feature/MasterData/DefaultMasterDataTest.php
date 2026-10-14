<?php

use App\Models\Bank;
use App\Models\ContentPage;
use App\Models\FollowerBracket;
use App\Models\Instrument;
use App\Models\PayrollRule;
use App\Models\ProductCategory;
use App\Models\Rank;

it('ships the default instruments with their pip sizes', function () {
    expect(Instrument::pluck('symbol')->all())->toEqualCanonicalizing([
        'EURUSD', 'GBPUSD', 'USDJPY', 'AUDUSD', 'USDCHF',
        'USDCAD', 'NZDUSD', 'XAUUSD', 'XAGUSD',
    ]);

    expect((float) Instrument::where('symbol', 'EURUSD')->value('pip_size'))->toBe(0.0001)
        ->and((float) Instrument::where('symbol', 'USDJPY')->value('pip_size'))->toBe(0.01)
        ->and((float) Instrument::where('symbol', 'XAUUSD')->value('pip_size'))->toBe(0.1);
});

it('ships the six default ranks in order', function () {
    expect(Rank::orderBy('level')->pluck('name')->all())
        ->toBe(['Newbie', 'Rookie', 'Pro', 'Elite', 'Master', 'Legend']);

    $elite = Rank::where('slug', 'elite')->first();

    expect($elite->can_sell_premium)->toBeTrue()
        ->and($elite->subscription_min_price)->toBe(100000)
        ->and($elite->subscription_max_price)->toBe(300000)
        ->and(Rank::where('slug', 'rookie')->first()->can_sell_premium)->toBeFalse();
});

it('ships one payroll rule per rank with unpaid entry ranks', function () {
    expect(PayrollRule::count())->toBe(Rank::count());

    $newbieRule = PayrollRule::whereRelation('rank', 'slug', 'newbie')->first();

    expect($newbieRule->base_salary)->toBe(0)
        ->and($newbieRule->per_win_amount)->toBe(0)
        ->and($newbieRule->required_streak)->toBe(10);
});

it('ships the default follower brackets, categories, banks and content pages', function () {
    expect(FollowerBracket::orderBy('min_followers')->get(['min_followers', 'max_followers', 'bonus_amount'])->toArray())
        ->toBe([
            ['min_followers' => 0, 'max_followers' => 99, 'bonus_amount' => 0],
            ['min_followers' => 100, 'max_followers' => 499, 'bonus_amount' => 250000],
            ['min_followers' => 500, 'max_followers' => 999, 'bonus_amount' => 750000],
            ['min_followers' => 1000, 'max_followers' => null, 'bonus_amount' => 1500000],
        ]);

    expect(ProductCategory::pluck('slug')->all())->toEqualCanonicalizing(['indikator', 'expert-advisor', 'template-script'])
        ->and(Bank::where('code', 'BCA')->exists())->toBeTrue()
        ->and(ContentPage::pluck('slug')->all())->toEqualCanonicalizing([
            'syarat-ketentuan', 'kebijakan-privasi', 'disclaimer-risiko', 'faq',
        ]);
});
