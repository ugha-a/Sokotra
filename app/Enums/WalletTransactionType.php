<?php

namespace App\Enums;

enum WalletTransactionType: string
{
    case SubscriptionIncome = 'subscription_income';
    case MarketplaceIncome = 'marketplace_income';
    case PendingRelease = 'pending_release';
    case PayrollCredit = 'payroll_credit';
    case RefundReversal = 'refund_reversal';
    case WithdrawLock = 'withdraw_lock';
    case WithdrawPaid = 'withdraw_paid';
    case WithdrawUnlock = 'withdraw_unlock';
    case AdminAdjustment = 'admin_adjustment';

    public function label(): string
    {
        return match ($this) {
            self::SubscriptionIncome => 'Pendapatan langganan',
            self::MarketplaceIncome => 'Penjualan produk',
            self::PendingRelease => 'Saldo dirilis',
            self::PayrollCredit => 'Gaji & reward',
            self::RefundReversal => 'Refund',
            self::WithdrawLock => 'Penarikan diajukan',
            self::WithdrawPaid => 'Penarikan dibayar',
            self::WithdrawUnlock => 'Penarikan dibatalkan',
            self::AdminAdjustment => 'Koreksi admin',
        };
    }

    /** Income that waits in the pending bucket for wallet.hold_period_days. */
    public function isHeldIncome(): bool
    {
        return in_array($this, [self::SubscriptionIncome, self::MarketplaceIncome], true);
    }
}
