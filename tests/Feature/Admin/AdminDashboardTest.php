<?php

use App\Enums\KycStatus;
use App\Enums\Role;
use App\Enums\WithdrawalStatus;
use App\Models\KycVerification;
use App\Models\TraderProfile;
use App\Models\Withdrawal;
use Inertia\Testing\AssertableInertia as Assert;

it('shows admins platform KPIs, revenue by source and approval queues', function () {
    fakePaymentGateway();
    $trader = premiumTrader(150000);
    $member = userWithRoles(Role::Member);
    paidSubscription($member, $trader);

    $applicant = TraderProfile::factory()->create();
    KycVerification::factory()->for($applicant->user)->create(['status' => KycStatus::Submitted]);
    Withdrawal::factory()->for($trader)->create(['status' => WithdrawalStatus::Requested, 'fee' => 6500]);
    Withdrawal::factory()->for($trader)->create(['status' => WithdrawalStatus::Paid, 'fee' => 6500]);

    $this->actingAs(adminUser())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->where('kpis.approved_traders', 1)
            ->where('kpis.active_subscriptions', 1)
            ->where('revenue.subscription_fees', 30000)
            ->where('revenue.withdraw_fees', 6500)
            ->has('revenue.marketplace_commission')
            ->has('revenue.ib_rebates')
            ->where('queues.trader_applications', 1)
            ->where('queues.kyc', 1)
            ->where('queues.withdrawals', 1)
            ->has('queues.products')
            ->has('queues.broker_accounts')
            ->has('queues.fraud_flags'));
});
