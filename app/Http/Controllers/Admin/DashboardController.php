<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BrokerAccountStatus;
use App\Enums\FraudFlagStatus;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\SignalStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TraderStatus;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\BrokerAccount;
use App\Models\BrokerRebate;
use App\Models\FraudFlag;
use App\Models\KycVerification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Signal;
use App\Models\Subscription;
use App\Models\TraderProfile;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Support\BusinessCalendar;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $monthStart = BusinessCalendar::startOfMonth()->utc();

        return Inertia::render('admin/dashboard', [
            'kpis' => [
                'users' => User::count(),
                'new_users_this_month' => User::where('created_at', '>=', $monthStart)->count(),
                'approved_traders' => TraderProfile::where('status', TraderStatus::Approved)->count(),
                'active_subscriptions' => Subscription::query()->currentlyActive()->count(),
                'open_signals' => Signal::open()->count(),
                'signals_closed_this_month' => Signal::whereIn('status', [SignalStatus::TpHit, SignalStatus::SlHit, SignalStatus::ClosedTimeout])->where('closed_at', '>=', $monthStart)->count(),
            ],
            'revenue' => [
                'subscription_fees' => (int) Subscription::whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Expired])->sum('platform_fee'),
                'marketplace_commission' => (int) Order::where('status', OrderStatus::Paid)->sum('commission'),
                'withdraw_fees' => (int) Withdrawal::where('status', WithdrawalStatus::Paid)->sum('fee'),
                'ib_rebates' => (int) BrokerRebate::sum('rebate_idr'),
            ],
            'liabilities' => [
                'available' => (int) Wallet::sum('available_balance'),
                'pending' => (int) Wallet::sum('pending_balance'),
                'locked' => (int) Wallet::sum('locked_balance'),
            ],
            'queues' => [
                'trader_applications' => TraderProfile::where('status', TraderStatus::Applied)->count(),
                'kyc' => KycVerification::where('status', KycStatus::Submitted)->count(),
                'withdrawals' => Withdrawal::whereIn('status', [WithdrawalStatus::Requested, WithdrawalStatus::Processing])->count(),
                'products' => Product::where('status', ProductStatus::InReview)->count(),
                'broker_accounts' => BrokerAccount::where('status', BrokerAccountStatus::Submitted)->count(),
                'fraud_flags' => FraudFlag::where('status', FraudFlagStatus::Open)->count(),
            ],
        ]);
    }
}
