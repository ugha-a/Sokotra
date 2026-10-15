import type { RankSummary } from './trader';

export type PayrollBreakdownData = {
    eligible: boolean;
    reasons: string[];
    closed_signals: number;
    best_streak: number;
    max_drawdown_r: number;
    base_amount: number;
    wins_counted: number;
    wins_amount: number;
    followers_counted: number;
    followers_amount: number;
    streak_amount: number;
    total_amount: number;
};

export type PayrollItemRow = PayrollBreakdownData & {
    id: number;
    tax_amount: number;
    status: string;
    status_label: string;
    hold_note: string | null;
    trader: { name: string; email: string };
    rank: RankSummary | null;
};

export type PayrollPeriodSummary = {
    id: number;
    month: string;
    label: string;
    status: string;
    status_label: string;
    total_amount: number;
    items_count?: number;
    eligible_count?: number;
    generated_at: string | null;
    approved_at: string | null;
};
