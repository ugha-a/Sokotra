<?php

use App\Actions\Payroll\ApprovePayroll;
use App\Actions\Payroll\GeneratePayrollDraft;
use App\Enums\PayrollItemStatus;
use App\Enums\PayrollPeriodStatus;
use App\Models\AdminAuditLog;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollRule;
use App\Models\Rank;
use App\Models\Wallet;
use App\Notifications\PayrollPaid;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Notification::fake();
    $this->travelTo(Carbon::parse('2026-11-01 02:00', 'Asia/Jakarta'));
    PayrollRule::where('rank_id', Rank::where('slug', 'newbie')->value('id'))->update([
        'base_salary' => 1000000,
        'per_win_amount' => 10000,
        'required_streak' => 3,
        'min_closed_signals_month' => 3,
        'streak_bonus_amount' => 100000,
    ]);

    $this->paid = approvedTrader();
    verifiedKyc($this->paid);
    closedSignalsFor($this->paid, [2.0, 2.0, 2.0, 2.0], Carbon::parse('2026-10-20 12:00'));

    $this->unpaid = approvedTrader();
    closedSignalsFor($this->unpaid, [2.0, -1.0], Carbon::parse('2026-10-20 12:00'));
});

it('generates a draft for the previous month with an item per approved trader', function () {
    $period = app(GeneratePayrollDraft::class)->handle('2026-10');

    $paidItem = PayrollItem::where('user_id', $this->paid->id)->firstOrFail();
    $unpaidItem = PayrollItem::where('user_id', $this->unpaid->id)->firstOrFail();

    expect($period->status)->toBe(PayrollPeriodStatus::Draft)
        ->and($period->items()->count())->toBe(2)
        ->and($paidItem->eligible)->toBeTrue()
        ->and($paidItem->total_amount)->toBe(1000000 + 4 * 10000 + 100000)
        ->and($unpaidItem->eligible)->toBeFalse()
        ->and($unpaidItem->ineligible_reasons)->toContain('kyc_not_verified')
        ->and($period->total_amount)->toBe(1140000);
});

it('regenerates a draft but never an approved period', function () {
    app(GeneratePayrollDraft::class)->handle('2026-10');
    PayrollRule::where('rank_id', Rank::where('slug', 'newbie')->value('id'))->update(['base_salary' => 2000000]);

    app(GeneratePayrollDraft::class)->handle('2026-10');
    expect(PayrollItem::where('user_id', $this->paid->id)->value('total_amount'))->toBe(2140000)
        ->and(PayrollItem::count())->toBe(2);

    app(ApprovePayroll::class)->handle(adminUser(), PayrollPeriod::firstOrFail());

    app(GeneratePayrollDraft::class)->handle('2026-10');
})->throws(ValidationException::class);

it('credits wallets exactly once on approval and skips held items', function () {
    $period = app(GeneratePayrollDraft::class)->handle('2026-10');
    $admin = adminUser();

    $other = approvedTrader();
    verifiedKyc($other);
    closedSignalsFor($other, [2.0, 2.0, 2.0], Carbon::parse('2026-10-21 12:00'));
    $period = app(GeneratePayrollDraft::class)->handle('2026-10');

    $this->actingAs($admin)
        ->post(route('admin.payroll.items.hold', PayrollItem::where('user_id', $other->id)->firstOrFail()), ['note' => 'Cek indikasi hedging'])
        ->assertSessionHasNoErrors();

    $this->actingAs($admin)->post(route('admin.payroll.approve', $period))->assertSessionHasNoErrors();
    $this->actingAs($admin)->post(route('admin.payroll.approve', $period))->assertSessionHasErrors('payroll');

    expect(Wallet::where('user_id', $this->paid->id)->value('available_balance'))->toBe(1140000)
        ->and(Wallet::where('user_id', $other->id)->first())->toBeNull()
        ->and(PayrollItem::where('user_id', $this->paid->id)->first()->status)->toBe(PayrollItemStatus::Paid)
        ->and(PayrollItem::where('user_id', $other->id)->first()->status)->toBe(PayrollItemStatus::Held)
        ->and($period->fresh()->status)->toBe(PayrollPeriodStatus::Approved)
        ->and($period->fresh()->approved_by)->toBe($admin->id)
        ->and(AdminAuditLog::where('action', 'payroll.approved')->exists())->toBeTrue();

    Notification::assertSentTo($this->paid, PayrollPaid::class);
    Notification::assertNotSentTo($other, PayrollPaid::class);
});

it('is scheduled for the first of each month in WIB', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($event) => str_contains((string) $event->command, 'payroll:generate'));

    expect($event?->expression)->toBe('0 1 1 * *')
        ->and($event->timezone)->toBe('Asia/Jakarta');
});

it('shows the payroll to admins only', function () {
    $period = app(GeneratePayrollDraft::class)->handle('2026-10');

    $this->actingAs($this->paid)->get(route('admin.payroll.index'))->assertForbidden();

    $this->actingAs(adminUser())
        ->get(route('admin.payroll.show', $period))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/payroll/show')
            ->where('period.month', '2026-10')
            ->has('items', 2)
            ->where('items.0.eligible', true)
            ->where('canApprove', true));

    $this->actingAs(adminUser())
        ->get(route('admin.payroll.index'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/payroll/index')->has('periods', 1));
});

it('lets admins generate a period from the index', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.payroll.generate'), ['month' => '2026-10'])
        ->assertRedirect();

    expect(PayrollPeriod::where('month', '2026-10')->exists())->toBeTrue();
});

it('shows traders an estimate for this month and their payslips', function () {
    $this->travelTo(Carbon::parse('2026-10-25 12:00', 'Asia/Jakarta'));

    $this->actingAs($this->paid)
        ->get(route('trader.payroll'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('trader/payroll')
            ->where('estimate.month', '2026-10')
            ->where('estimate.total_amount', 1140000)
            ->where('estimate.eligible', true)
            ->has('payslips', 0));
});
