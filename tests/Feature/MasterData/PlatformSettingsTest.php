<?php

use App\Enums\Role;
use App\Models\AdminAuditLog;
use App\Support\Settings;
use Inertia\Testing\AssertableInertia as Assert;

it('returns typed default settings', function () {
    expect(Settings::get('wallet.min_withdraw'))->toBe(100000)
        ->and(Settings::get('wallet.withdraw_fee'))->toBe(6500)
        ->and(Settings::get('subscription.fee_percent'))->toBe(20.0)
        ->and(Settings::get('subscription.durations'))->toBe([1, 3, 6])
        ->and(Settings::get('signal.min_rr'))->toBe(1.0)
        ->and(Settings::get('signal.ambiguous_candle_rule'))->toBe('sl_first')
        ->and(Settings::get('signal.count_expired_in_streak'))->toBeFalse()
        ->and(Settings::get('content.prohibited_phrases'))->toContain('pasti profit')
        ->and(Settings::get('content.risk_disclaimer'))->toContain('berisiko tinggi')
        ->and(Settings::get('trader.reapply_cooldown_days'))->toBe(7)
        ->and(Settings::get('trader.max_main_instruments'))->toBe(5)
        ->and(Settings::get('kyc.max_file_mb'))->toBe(5)
        ->and(Settings::get('kyc.min_age'))->toBe(18)
        ->and(Settings::get('kyc.document_link_minutes'))->toBe(10);
});

it('fails loudly for unknown setting keys', function () {
    Settings::get('wallet.does_not_exist');
})->throws(InvalidArgumentException::class);

it('forbids non-admins from the settings page', function () {
    $this->actingAs(userWithRoles(Role::Member, Role::Trader))
        ->get(route('admin.settings.edit'))
        ->assertForbidden();
});

it('shows settings grouped for admins', function () {
    $this->actingAs(adminUser())
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/settings')
            ->has('groups', 8)
            ->where('groups.0.key', 'signal'));
});

it('updates settings, refreshes the cache and writes an audit log', function () {
    $admin = adminUser();
    expect(Settings::get('wallet.min_withdraw'))->toBe(100000);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'settings' => [
                'wallet' => ['min_withdraw' => 150000],
                'subscription' => ['durations' => [1, 12]],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Settings::get('wallet.min_withdraw'))->toBe(150000)
        ->and(Settings::get('subscription.durations'))->toBe([1, 12]);

    $log = AdminAuditLog::latest('id')->first();

    expect($log->user_id)->toBe($admin->id)
        ->and($log->action)->toBe('settings.updated')
        ->and($log->old_values)->toBe(['wallet.min_withdraw' => 100000, 'subscription.durations' => [1, 3, 6]])
        ->and($log->new_values)->toBe(['wallet.min_withdraw' => 150000, 'subscription.durations' => [1, 12]]);
});

it('validates settings by their type', function (string $key, mixed $value) {
    $payload = [];

    $this->actingAs(adminUser())
        ->put(route('admin.settings.update'), ['settings' => data_set($payload, $key, $value)])
        ->assertSessionHasErrors("settings.{$key}");
})->with([
    'percent above 100' => ['subscription.fee_percent', 120],
    'negative money' => ['wallet.min_withdraw', -1],
    'non-integer money' => ['wallet.withdraw_fee', 10.5],
    'unknown choice' => ['signal.ambiguous_candle_rule', 'random'],
    'empty integer list' => ['subscription.durations', []],
    'non boolean' => ['signal.count_expired_in_streak', 'yes please'],
]);

it('rejects unknown setting keys', function () {
    $this->actingAs(adminUser())
        ->put(route('admin.settings.update'), ['settings' => ['wallet' => ['nope' => 1]]])
        ->assertSessionHasErrors('settings');
});

it('requires score weights to add up to one', function () {
    $this->actingAs(adminUser())
        ->put(route('admin.settings.update'), ['settings' => ['score' => ['weight_win_rate' => 0.5]]])
        ->assertSessionHasErrors('settings.score.weight_win_rate');

    $this->actingAs(adminUser())
        ->put(route('admin.settings.update'), ['settings' => ['score' => [
            'weight_win_rate' => 0.40,
            'weight_total_r' => 0.25,
        ]]])
        ->assertSessionHasNoErrors();
});
