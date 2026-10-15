import { Head, Link } from '@inertiajs/react';
import { BadgeCheck, Star, Trophy } from 'lucide-react';
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
    { value: 'score', label: 'Peringkat Skor' },
    { value: 'consistent', label: 'Paling Konsisten' },
    { value: 'featured', label: 'Pilihan Sokotra' },
] as const;

const PERIODS = [
    { value: 'week', label: 'Minggu Ini' },
    { value: 'month', label: 'Bulan Ini' },
    { value: 'all_time', label: 'Sepanjang Waktu' },
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
                'rounded border px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors',
                active
                    ? 'border-[#2962ff] bg-[#2962ff] text-white'
                    : 'border-[#2a2e39] bg-[#1e222d] text-[#787b86] hover:bg-[#2a2e39] hover:text-[#d1d4dc]',
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
            <Head title="Leaderboard Analis Trader" />
            <div className="flex flex-col gap-6">
                <header className="border-b border-[#2a2e39] pb-4">
                    <Heading
                        title="Leaderboard Analis Terverifikasi"
                        description={`Peringkat dihitung otomatis dari sinyal yang sudah selesai dinilai oleh engine harga pasar (termasuk sinyal kalah). Minimal ${minSignals} sinyal untuk masuk daftar.`}
                    />
                </header>

                {/* Filter and Period Selection */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <nav
                        aria-label="Kategori Peringkat"
                        className="flex gap-1.5 overflow-x-auto pb-1"
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
                        aria-label="Filter Periode"
                        className="flex gap-1.5 overflow-x-auto pb-1"
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
                    <div className="flex flex-col items-center gap-2 rounded-lg border border-dashed border-[#2a2e39] bg-[#1e222d] p-10 text-center">
                        <Trophy className="size-8 text-[#787b86]" />
                        <p className="text-sm font-semibold text-white">
                            Belum ada trader yang memenuhi syarat
                        </p>
                        <p className="max-w-md text-xs text-[#787b86]">
                            Trader masuk leaderboard setelah minimal{' '}
                            {minSignals} sinyal selesai dievaluasi oleh engine
                            pasar.
                        </p>
                    </div>
                ) : (
                    /* High Data Density Financial Leaderboard Table (ala ZuluTrade & eToro) */
                    <div className="overflow-x-auto rounded-lg border border-[#2a2e39] bg-[#1e222d]">
                        <table className="w-full text-left text-xs">
                            <thead className="border-b border-[#2a2e39] bg-[#171b26] text-[#787b86]">
                                <tr>
                                    <th
                                        scope="col"
                                        className="w-12 px-3 py-2.5 text-center font-medium"
                                    >
                                        #
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-3 py-2.5 font-medium"
                                    >
                                        Trader / Analis
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-3 py-2.5 text-right font-medium"
                                    >
                                        Win Rate
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-3 py-2.5 text-right font-medium"
                                    >
                                        Total R
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-3 py-2.5 text-right font-medium"
                                    >
                                        Max Drawdown
                                    </th>
                                    <th
                                        scope="col"
                                        className="hidden px-3 py-2.5 text-right font-medium sm:table-cell"
                                    >
                                        {tab === 'consistent'
                                            ? 'Konsistensi'
                                            : 'Skor Bobot'}
                                    </th>
                                    <th
                                        scope="col"
                                        className="hidden px-3 py-2.5 text-right font-medium md:table-cell"
                                    >
                                        Sinyal
                                    </th>
                                    <th
                                        scope="col"
                                        className="hidden px-3 py-2.5 text-right font-medium lg:table-cell"
                                    >
                                        Follower
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-3 py-2.5 text-right font-medium"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#2a2e39] font-mono text-[#d1d4dc] tabular-nums">
                                {rows.map((row) => (
                                    <tr
                                        key={row.trader.slug}
                                        className="transition-colors hover:bg-[#242835]"
                                    >
                                        <td className="px-3 py-3 text-center">
                                            <span
                                                className={cn(
                                                    'inline-flex size-6 items-center justify-center rounded text-xs font-bold',
                                                    row.position === 1 &&
                                                        'border border-amber-500/30 bg-amber-500/20 text-amber-300',
                                                    row.position === 2 &&
                                                        'border border-slate-400/30 bg-slate-400/20 text-slate-200',
                                                    row.position === 3 &&
                                                        'border border-amber-700/30 bg-amber-700/20 text-amber-500',
                                                    row.position > 3 &&
                                                        'text-[#787b86]',
                                                )}
                                            >
                                                {row.position}
                                            </span>
                                        </td>
                                        <td className="px-3 py-3 font-sans">
                                            <div className="flex items-center gap-2">
                                                <Link
                                                    href={traderProfile(
                                                        row.trader.slug,
                                                    )}
                                                    className="flex items-center gap-1 font-medium text-white hover:text-[#2962ff]"
                                                >
                                                    {row.trader.name}
                                                    <BadgeCheck className="size-3.5 text-[#2962ff]" />
                                                </Link>
                                                {row.trader.rank && (
                                                    <RankBadge
                                                        name={
                                                            row.trader.rank.name
                                                        }
                                                        color={
                                                            row.trader.rank
                                                                .color
                                                        }
                                                    />
                                                )}
                                                {row.trader.is_featured && (
                                                    <Star
                                                        className="size-3.5 fill-amber-400 text-amber-400"
                                                        aria-label="Pilihan Sokotra"
                                                    />
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-3 py-3 text-right font-bold text-[#089981]">
                                            {formatPercent(row.stats.win_rate)}
                                        </td>
                                        <td className="px-3 py-3 text-right font-bold text-white">
                                            {formatR(row.stats.total_r)}
                                        </td>
                                        <td className="px-3 py-3 text-right text-[#f23645]">
                                            {row.stats.max_drawdown_r.toLocaleString(
                                                'id-ID',
                                            )}
                                            R
                                        </td>
                                        <td className="hidden px-3 py-3 text-right font-semibold text-white sm:table-cell">
                                            {(tab === 'consistent'
                                                ? row.consistency
                                                : row.score
                                            ).toLocaleString('id-ID')}
                                        </td>
                                        <td className="hidden px-3 py-3 text-right text-[#787b86] md:table-cell">
                                            {row.stats.closed_signals}
                                        </td>
                                        <td className="hidden px-3 py-3 text-right text-[#787b86] lg:table-cell">
                                            {formatCompact(row.followers)}
                                        </td>
                                        <td className="px-3 py-3 text-right font-sans">
                                            <Link
                                                href={traderProfile(
                                                    row.trader.slug,
                                                )}
                                                className="inline-flex items-center rounded border border-[#2a2e39] bg-[#131722] px-2.5 py-1 text-xs font-medium text-white transition-colors hover:border-[#2962ff] hover:text-[#2962ff]"
                                            >
                                                Profil & Sinyal
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}
