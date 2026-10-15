import type { MasterOption } from './master';

export type WalletSummary = {
    available_balance: number;
    pending_balance: number;
    locked_balance: number;
};

export type LedgerRow = {
    id: number;
    type: string;
    type_label: string;
    bucket: 'available' | 'pending' | 'locked';
    bucket_label: string;
    amount: number;
    balance_after: number;
    description: string | null;
    available_at: string | null;
    created_at: string | null;
};

export type BankAccountSummary = {
    id: number;
    bank: string;
    account_number: string;
    account_name: string;
};

export type WithdrawalRow = {
    id: number;
    amount: number;
    fee: number;
    status: string;
    status_label: string;
    bank_account: BankAccountSummary;
    rejection_reason: string | null;
    disbursement_reference: string | null;
    created_at: string | null;
};

export type WalletRules = {
    min_withdraw: number;
    withdraw_fee: number;
    max_bank_accounts: number;
    hold_period_days: number;
};

export type WalletPageProps = {
    wallet: WalletSummary;
    transactions: import('./master').Paginated<LedgerRow>;
    bankAccounts: BankAccountSummary[];
    banks: MasterOption[];
    withdrawals: WithdrawalRow[];
    rules: WalletRules;
    kycVerified: boolean;
};

export type AdminWithdrawalRow = {
    id: number;
    user: { name: string; email: string };
    kyc_name: string | null;
    bank: string;
    account_number: string;
    account_name: string;
    name_mismatch: boolean;
    amount: number;
    fee: number;
    status: string;
    status_label: string;
    rejection_reason: string | null;
    disbursement_reference: string | null;
    created_at: string | null;
};
