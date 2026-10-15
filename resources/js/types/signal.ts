import type { MasterOption } from './master';
import type { RankSummary } from './trader';

export type SignalDirection = 'buy' | 'sell';

export type SignalItem = {
    id: number;
    instrument: { symbol: string; digits: number };
    trader: { name: string; slug: string | null; rank: RankSummary | null };
    direction: SignalDirection;
    order_type: string;
    order_type_label: string;
    visibility: 'free' | 'premium';
    status: string;
    status_label: string;
    is_open: boolean;
    locked: boolean;
    entry_price: string | null;
    stop_loss: string | null;
    take_profit: string | null;
    rr: string | null;
    sl_pips: string | null;
    analysis: string | null;
    chart_url: string | null;
    subscribe_url: string | null;
    close_price: string | null;
    result_pips: string | null;
    result_r: string | null;
    published_at: string;
    triggered_at: string | null;
    expires_at: string | null;
    closed_at: string | null;
};

export type SignalFilters = { instrument: number | null; status: string };

export type SignalInstrument = {
    id: number;
    symbol: string;
    name: string;
    digits: number;
    pip_size: string;
    spread_pips: string;
    min_sl_pips: string;
};

export type SignalRules = {
    min_rr: number;
    max_active: number;
    open_count: number;
    pending_expiry_hours: number;
    max_duration_days: number;
    min_analysis_chars: number;
    chart_max_mb: number;
};

export type LiveQuote = { bid: string; ask: string; time: string };

export type SignalCreateProps = {
    instruments: SignalInstrument[];
    directions: MasterOption[];
    orderTypes: MasterOption[];
    rules: SignalRules;
    canPublishPremium: boolean;
    blockReason: string | null;
    riskDisclaimer: string;
};
