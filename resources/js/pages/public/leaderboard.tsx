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
                'rounded-full border px-3 py-1.5 text-sm whitespace-nowrap transition-colors',
                active
                    ? 'border-brand bg-brand text-brand-foreground'
                    : 'hover:bg-accent',
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
            <Head title="Leaderboard" />
            <div className="grid gap-6">
                <Heading
                    title="Leaderboard Trader"
                    description={`Dihitung otomatis dari sinyal yang sudah selesai, termasuk yang kalah. Minimal ${minSignals} sinyal untuk masuk daftar.`}
                />
                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
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
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <Trophy className="size-8 text-muted-foreground" />
                        <p className="font-medium">
                            Belum ada trader yang memenuhi syarat
                        </p>
                        <p className="text-sm text-muted-foreground">
                            Trader masuk leaderboard setelah {minSignals} sinyal
                            selesai.
                        </p>
                    </div>
                ) : (
                    <ol className="divide-y rounded-xl border">
                        {rows.map((row) => (
                            <li key={row.trader.slug}>
                                <Link
                                    href={traderProfile(row.trader.slug)}
                                    className="grid grid-cols-[2.5rem_1fr_auto] items-center gap-3 p-4 transition-colors hover:bg-accent/50 md:grid-cols-[2.5rem_1fr_repeat(4,6.5rem)]"
                                >
                                    <span className="font-mono text-lg font-semibold text-muted-foreground tabular-nums">
                                        {row.position}
                                    </span>
                                    <span className="grid gap-1">
                                        <span className="flex flex-wrap items-center gap-2 font-medium">
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
                                                    className="size-3.5 text-premium"
                                                    aria-label="Pilihan Sokotra"
                                                />
                                            )}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {row.stats.closed_signals} sinyal ·{' '}
                                            {formatCompact(row.followers)}{' '}
                                            follower valid
                                        </span>
                                    </span>
                                    <span className="text-right md:hidden">
                                        <span className="block font-mono font-semibold tabular-nums">
                                            {formatR(row.stats.total_r)}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {formatPercent(row.stats.win_rate)}
                                        </span>
                                    </span>
                                    <Metric
                                        label="Win rate"
                                        value={formatPercent(
                                            row.stats.win_rate,
                                        )}
                                    />
                                    <Metric
                                        label="Total R"
                                        value={formatR(row.stats.total_r)}
                                    />
                                    <Metric
                                        label="Drawdown"
                                        value={`${row.stats.max_drawdown_r.toLocaleString('id-ID')}R`}
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

function Metric({ label, value }: { label: string; value: string }) {
    return (
        <span className="hidden text-right md:block">
            <span className="block font-mono text-sm tabular-nums">
                {value}
            </span>
            <span className="text-xs text-muted-foreground">{label}</span>
        </span>
    );
}
