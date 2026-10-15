import { Head, Link } from '@inertiajs/react';
import { Star, Trophy } from 'lucide-react';
import Heading from '@/components/heading';
import { RankBadge } from '@/components/domain/rank-badge';
import { formatCompact, formatPercent, formatR } from '@/lib/format';
import { cn } from '@/lib/utils';
import { leaderboard } from '@/routes';
import { show as traderProfile } from '@/routes/traders';
import type { LeaderboardRow } from '@/types';

type Props = {
    tab: 'score' | 'consistent' | 'featured';
    period: 'week' | 'month' | 'all_time';
    rows: LeaderboardRow[];
    minSignals: number;
};

const TABS = [
    { value: 'score', label: 'Peringkat' },
    { value: 'consistent', label: 'Paling Konsisten' },
    { value: 'featured', label: 'Pilihan Sokotra' },
] as const;

const PERIODS = [
    { value: 'week', label: 'Minggu ini' },
    { value: 'month', label: 'Bulan ini' },
    { value: 'all_time', label: 'Sepanjang waktu' },
] as const;

function Pill({
    href,
    active,
    children,
}: {
    href: string;
    active: boolean;
    children: React.ReactNode;
}) {
    return (
        <Link
            href={href}
            preserveScroll
            className={cn(
                'rounded-xl border px-3.5 py-1.5 text-xs font-semibold whitespace-nowrap transition-all',
                active
                    ? 'border-cyan-500/50 bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-sm shadow-cyan-500/20'
                    : 'border-white/10 bg-white/[0.03] text-slate-400 hover:bg-white/10 hover:text-white',
            )}
        >
            {children}
        </Link>
    );
}

export default function Leaderboard({ tab, period, rows, minSignals }: Props) {
    const url = (next: Partial<Pick<Props, 'tab' | 'period'>>) =>
        leaderboard({
            query: { tab: next.tab ?? tab, period: next.period ?? period },
        }).url;

    return (
        <>
            <Head title="Leaderboard Trader" />
            <div className="grid gap-8">
                <div className="grid gap-2">
                    <Heading
                        title="Leaderboard Analis Terverifikasi"
                        description={`Peringkat dihitung otomatis dari sinyal yang sudah selesai dinilai oleh engine harga pasar (termasuk sinyal kalah). Minimal ${minSignals} sinyal untuk masuk daftar.`}
                    />
                </div>
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <nav
                        aria-label="Jenis peringkat"
                        className="-mx-1 flex gap-2 overflow-x-auto px-1"
                    >
                        {TABS.map((item) => (
                            <Pill
                                key={item.value}
                                href={url({ tab: item.value })}
                                active={tab === item.value}
                            >
                                {item.label}
                            </Pill>
                        ))}
                    </nav>
                    <nav
                        aria-label="Periode"
                        className="-mx-1 flex gap-2 overflow-x-auto px-1"
                    >
                        {PERIODS.map((item) => (
                            <Pill
                                key={item.value}
                                href={url({ period: item.value })}
                                active={period === item.value}
                            >
                                {item.label}
                            </Pill>
                        ))}
                    </nav>
                </div>

                {rows.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-white/15 bg-[#121826]/40 p-12 text-center">
                        <Trophy className="size-10 text-slate-500" />
                        <p className="text-base font-bold text-white">
                            Belum ada trader yang memenuhi syarat
                        </p>
                        <p className="max-w-md text-sm text-slate-400">
                            Trader masuk leaderboard setelah minimal{' '}
                            {minSignals} sinyal selesai dievaluasi oleh engine
                            pasar.
                        </p>
                    </div>
                ) : (
                    <ol className="trading-glass divide-y divide-white/5 overflow-hidden rounded-2xl border border-white/10 bg-[#121826]/80 shadow-2xl backdrop-blur-xl">
                        {rows.map((row) => (
                            <li key={row.trader.slug}>
                                <Link
                                    href={traderProfile(row.trader.slug)}
                                    className="grid grid-cols-[3rem_1fr_auto] items-center gap-4 p-4 transition-colors hover:bg-white/[0.04] md:grid-cols-[3.5rem_1fr_repeat(4,7rem)] md:p-5"
                                >
                                    <span
                                        className={cn(
                                            'rounded-lg px-2 py-1 text-center font-mono text-sm font-bold tabular-nums',
                                            row.position === 1 &&
                                                'border border-amber-400/30 bg-amber-400/15 text-amber-300',
                                            row.position === 2 &&
                                                'border border-slate-300/30 bg-slate-300/15 text-slate-200',
                                            row.position === 3 &&
                                                'border border-amber-600/30 bg-amber-700/20 text-amber-500',
                                            row.position > 3 &&
                                                'bg-white/5 text-slate-400',
                                        )}
                                    >
                                        #{row.position}
                                    </span>
                                    <span className="grid gap-1">
                                        <span className="flex flex-wrap items-center gap-2 text-base font-bold text-white">
                                            {row.trader.name}
                                            {row.trader.rank && (
                                                <RankBadge
                                                    name={row.trader.rank.name}
                                                    color={
                                                        row.trader.rank.color
                                                    }
                                                />
                                            )}
                                            {row.trader.is_featured && (
                                                <Star
                                                    className="size-4 fill-amber-400 text-amber-400"
                                                    aria-label="Pilihan Sokotra"
                                                />
                                            )}
                                        </span>
                                        <span className="text-xs text-slate-400">
                                            {row.stats.closed_signals} sinyal
                                            dinilai ·{' '}
                                            {formatCompact(row.followers)}{' '}
                                            follower
                                        </span>
                                    </span>
                                    <span className="text-right md:hidden">
                                        <span className="block font-mono font-bold text-cyan-300 tabular-nums">
                                            {formatR(row.stats.total_r)}
                                        </span>
                                        <span className="text-xs font-semibold text-emerald-400">
                                            {formatPercent(row.stats.win_rate)}
                                        </span>
                                    </span>
                                    <Metric
                                        label="Win rate"
                                        value={formatPercent(
                                            row.stats.win_rate,
                                        )}
                                        valueClass="text-emerald-400 font-bold"
                                    />
                                    <Metric
                                        label="Total R"
                                        value={formatR(row.stats.total_r)}
                                        valueClass="text-cyan-300 font-bold"
                                    />
                                    <Metric
                                        label="Max Drawdown"
                                        value={`${row.stats.max_drawdown_r.toLocaleString('id-ID')}R`}
                                        valueClass="text-rose-400 font-medium"
                                    />
                                    <Metric
                                        label={
                                            tab === 'consistent'
                                                ? 'Konsistensi'
                                                : 'Skor'
                                        }
                                        value={(tab === 'consistent'
                                            ? row.consistency
                                            : row.score
                                        ).toLocaleString('id-ID')}
                                        valueClass="text-white font-extrabold"
                                    />
                                </Link>
                            </li>
                        ))}
                    </ol>
                )}
            </div>
        </>
    );
}

function Metric({
    label,
    value,
    valueClass,
}: {
    label: string;
    value: string;
    valueClass?: string;
}) {
    return (
        <span className="hidden text-right md:block">
            <span
                className={cn(
                    'block font-mono text-base tabular-nums',
                    valueClass || 'font-semibold text-white',
                )}
            >
                {value}
            </span>
            <span className="text-xs font-medium text-slate-400">{label}</span>
        </span>
    );
}
