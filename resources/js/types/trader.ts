import type { MasterOption } from './master';

export type TraderApplicationSummary = {
    display_name: string;
    bio: string;
    trading_style: string;
    experience_years: number;
    main_instruments: number[];
    status: string;
    status_label: string;
    rejection_reason: string | null;
    suspension_reason: string | null;
    applied_at: string | null;
};

export type MemberKycSummary = {
    full_name: string;
    birth_date: string;
    status: string;
    status_label: string;
    rejection_reason: string | null;
};

export type BecomeTraderProps = {
    application: TraderApplicationSummary | null;
    kyc: MemberKycSummary | null;
    canSubmit: boolean;
    blockReason: string | null;
    instruments: MasterOption[];
    tradingStyles: MasterOption[];
    maxFileMb: number;
    maxInstruments: number;
    riskDisclaimer: string;
};

export type RankSummary = { name: string; color: string };

export type TraderApplicationRow = {
    id: number;
    display_name: string;
    user: { name: string; email: string };
    trading_style: string;
    kyc_status: string | null;
    kyc_status_label: string | null;
    rank: RankSummary | null;
    applied_at: string | null;
};

export type TraderStatusFilter = {
    value: string;
    label: string;
    count: number;
};

export type AdminTraderProfile = {
    id: number;
    display_name: string;
    slug: string;
    bio: string;
    trading_style: string;
    experience_years: number;
    main_instruments: string[];
    status: string;
    status_label: string;
    rank: RankSummary | null;
    applied_at: string | null;
    approved_at: string | null;
    reviewed_at: string | null;
    reviewer: string | null;
    rejection_reason: string | null;
    suspension_reason: string | null;
    user: { name: string; email: string };
};

export type AdminKyc = {
    id: number;
    full_name: string;
    nik_masked: string;
    birth_date: string;
    status: string;
    status_label: string;
    submitted_at: string | null;
    reviewed_at: string | null;
    reviewer: string | null;
    rejection_reason: string | null;
    documents: { id_card: string; selfie: string };
    documentLinkMinutes: number;
};

export type TraderStatSummary = {
    period: string;
    period_start: string | null;
    closed_signals: number;
    wins: number;
    losses: number;
    win_rate: number;
    total_r: number;
    avg_rr: number;
    total_pips: number;
    max_consecutive_wins: number;
    max_consecutive_losses: number;
    current_streak: number;
    best_streak: number;
    max_drawdown_r: number;
    profit_factor: number | null;
};

export type StatsByPeriod = Record<
    'week' | 'month' | 'rolling' | 'all_time',
    TraderStatSummary | null
>;

export type LeaderboardRow = {
    position: number;
    trader: {
        name: string;
        slug: string;
        rank: RankSummary | null;
        is_featured: boolean;
    };
    score: number;
    consistency: number;
    followers: number;
    stats: TraderStatSummary;
};

export type RankRequirement = {
    key: string;
    label: string;
    required: number;
    current: number;
    met: boolean;
};

export type PublicTrader = {
    id: number;
    name: string;
    slug: string;
    bio: string;
    trading_style: string;
    experience_years: number;
    main_instruments: string[];
    rank: RankSummary | null;
    followers: number;
    is_featured: boolean;
    approved_at: string | null;
};

export type NotificationItem = {
    id: string;
    title: string;
    body: string;
    url: string | null;
    read: boolean;
    created_at: string | null;
};
