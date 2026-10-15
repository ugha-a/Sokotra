<?php

namespace App\Http\Controllers\Trader;

use App\Actions\Payroll\CalculateTraderPayroll;
use App\Enums\PayrollPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\PayrollItem;
use App\Models\PayrollRule;
use App\Models\TraderProfile;
use App\Support\BusinessCalendar;
use App\Support\Payroll\PayrollCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    public function __invoke(Request $request, CalculateTraderPayroll $calculate): Response|RedirectResponse
    {
        $profile = TraderProfile::with(['user', 'rank'])->where('user_id', $request->user()?->id)->first();

        if ($profile === null) {
            return to_route('become-trader.show');
        }

        $month = BusinessCalendar::now()->format('Y-m');
        $estimate = $calculate->handle($profile, $month);
        $rule = $profile->rank_id ? PayrollRule::where('rank_id', $profile->rank_id)->first() : null;

        return Inertia::render('trader/payroll', [
            'estimate' => [
                'month' => $month,
                'label' => BusinessCalendar::now()->translatedFormat('F Y'),
                'eligible' => $estimate->eligible,
                'reasons' => array_map(fn (string $key) => PayrollCalculator::REASONS[$key] ?? $key, $estimate->reasons),
                ...collect($estimate->toColumns())->except(['eligible', 'ineligible_reasons'])->all(),
            ],
            'rule' => $rule ? [
                'rank' => $profile->rank?->name,
                'base_salary' => $rule->base_salary,
                'per_win_amount' => $rule->per_win_amount,
                'max_weekly_wins_counted' => $rule->max_weekly_wins_counted,
                'required_streak' => $rule->required_streak,
                'streak_bonus_amount' => $rule->streak_bonus_amount,
                'min_closed_signals_month' => $rule->min_closed_signals_month,
                'max_drawdown_r_month' => $rule->max_drawdown_r_month === null ? null : (float) $rule->max_drawdown_r_month,
            ] : null,
            'payslips' => PayrollItem::query()
                ->with('period')
                ->where('user_id', $profile->user_id)
                ->whereHas('period', fn ($query) => $query->where('status', PayrollPeriodStatus::Approved))
                ->latest('id')
                ->get()
                ->map(fn (PayrollItem $item) => [...$item->summary(), 'period' => $item->period->label()])
                ->values()->all(),
        ]);
    }
}
