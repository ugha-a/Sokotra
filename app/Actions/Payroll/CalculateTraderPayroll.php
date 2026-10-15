<?php

namespace App\Actions\Payroll;

use App\Enums\KycStatus;
use App\Enums\SignalStatus;
use App\Enums\TraderStatus;
use App\Models\Follow;
use App\Models\FollowerBracket;
use App\Models\KycVerification;
use App\Models\PayrollRule;
use App\Models\Signal;
use App\Models\TraderProfile;
use App\Support\BusinessCalendar;
use App\Support\Payroll\PayrollCalculator;
use App\Support\Payroll\PayrollInput;
use App\Support\Payroll\PayrollResult;
use Carbon\CarbonImmutable;

/**
 * Loads one trader's month from the database and runs PayrollCalculator.
 */
class CalculateTraderPayroll
{
    public function handle(TraderProfile $profile, string $month): PayrollResult
    {
        $start = CarbonImmutable::createFromFormat('Y-m-d H:i:s', "{$month}-01 00:00:00", BusinessCalendar::timezone());
        $rule = $profile->rank_id ? PayrollRule::where('rank_id', $profile->rank_id)->where('is_active', true)->first() : null;

        $input = new PayrollInput(
            rule: $rule ? [
                'base_salary' => $rule->base_salary,
                'per_win_amount' => $rule->per_win_amount,
                'max_weekly_wins_counted' => $rule->max_weekly_wins_counted,
                'required_streak' => $rule->required_streak,
                'min_closed_signals_month' => $rule->min_closed_signals_month,
                'max_drawdown_r_month' => $rule->max_drawdown_r_month,
                'streak_bonus_amount' => $rule->streak_bonus_amount,
            ] : null,
            kycVerified: KycVerification::where('user_id', $profile->user_id)->first()?->status === KycStatus::Verified,
            flagged: $profile->is_flagged || (bool) $profile->user->is_fraud_flagged,
            signals: Signal::query()
                ->where('trader_id', $profile->user_id)
                ->whereIn('status', [SignalStatus::TpHit, SignalStatus::SlHit, SignalStatus::ClosedTimeout])
                ->whereBetween('closed_at', [$start->utc(), $start->addMonth()->utc()])
                ->get(['status', 'result_r', 'closed_at', 'excluded_from_payroll'])
                ->all(),
            validFollowers: Follow::query()->where('trader_id', $profile->user_id)->valid()->count(),
            brackets: array_values(array_map(
                fn (FollowerBracket $bracket) => [$bracket->min_followers, $bracket->max_followers, $bracket->bonus_amount],
                FollowerBracket::query()->where('is_active', true)->orderBy('min_followers')->get()->all(),
            )),
            monthStart: $start,
        );

        return PayrollCalculator::calculate($input, $profile->status === TraderStatus::Suspended ? ['suspended'] : []);
    }
}
