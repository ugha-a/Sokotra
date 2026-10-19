import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { ArrowRight, Lock, TrendingUp, Trophy } from 'lucide-react';
import { RankBadge } from '@/components/domain/rank-badge';
import { AuthPromptModal } from '@/components/landing/auth-prompt-modal';
import { Button } from '@/components/ui/button';
import { formatPercent } from '@/lib/format';
import { register } from '@/routes';
import type { LeaderboardRow } from '@/types';

type LandingLeaderboardProps = {
    topTraders: LeaderboardRow[];
    isAuthenticated: boolean;
};

export function LandingLeaderboard({
    topTraders,
    isAuthenticated,
}: LandingLeaderboardProps) {
    const [selectedTrader, setSelectedTrader] = useState<string | null>(null);
    const [actionType, setActionType] = useState<'copy' | 'subscribe'>('copy');

    const handleAction = (traderName: string, type: 'copy' | 'subscribe') => {
        if (!isAuthenticated) {
            setSelectedTrader(traderName);
            setActionType(type);
        }
    };

    return (
        <section
            id="live-leaderboard"
            className="border-b border-[#2a2e39] bg-[#0e1118] py-16 lg:py-24"
        >
            <div className="mx-auto max-w-7xl px-4 sm:px-6">
                {/* Section Header */}
                <div className="flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
                    <div>
                        <div className="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wider text-[#089981] uppercase">
                            <Trophy className="h-3.5 w-3.5" />
                            <span>Social Proof & Live Leaderboard</span>
                        </div>
                        <h2 className="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                            Bukti Nyata, Bukan Janji Manis
                        </h2>
                        <p className="mt-2 max-w-2xl text-sm leading-relaxed text-[#b2b5be] sm:text-base">
                            Peringkat analis diperbarui otomatis dari hasil riil
                            sinyal pasar. Trader tidak dapat menyembunyikan
                            rekam jejak atau menghapus sinyal yang menyentuh
                            Stop Loss.
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button
                            variant="outline"
                            size="sm"
                            asChild
                            className="border-[#2a2e39] bg-[#1e222d] text-xs font-medium text-white hover:border-[#2962ff] hover:bg-[#2a2e39]"
                        >
                            <Link href={register()}>
                                <span>Lihat Semua 50+ Analis</span>
                                <ArrowRight className="ml-1.5 h-3.5 w-3.5" />
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* ZuluTrade Inspired Leaderboard Table */}
                <div className="mt-8 overflow-hidden rounded-xl border border-[#2a2e39] bg-[#1e222d]">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm text-[#d1d4dc]">
                            <thead className="border-b border-[#2a2e39] bg-[#131722] text-xs font-semibold text-[#787b86] uppercase">
                                <tr>
                                    <th className="px-5 py-3.5">Peringkat</th>
                                    <th className="px-5 py-3.5">
                                        Trader Analis
                                    </th>
                                    <th className="px-5 py-3.5 text-right">
                                        Win Rate
                                    </th>
                                    <th className="px-5 py-3.5 text-right">
                                        Performa (R)
                                    </th>
                                    <th className="px-5 py-3.5 text-right">
                                        Sinyal Selesai
                                    </th>
                                    <th className="px-5 py-3.5 text-right">
                                        Follower
                                    </th>
                                    <th className="px-5 py-3.5 text-center">
                                        Tindakan
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#2a2e39]/60">
                                {topTraders.length > 0 ? (
                                    topTraders.map((row, idx) => {
                                        const pos = row.position || idx + 1;
                                        const winRate =
                                            row.stats?.win_rate ?? 0;
                                        const totalR = row.stats?.total_r ?? 0;
                                        const closedSignals =
                                            row.stats?.closed_signals ?? 0;

                                        return (
                                            <tr
                                                key={row.trader.slug || idx}
                                                className="transition-colors hover:bg-[#2a2e39]/40"
                                            >
                                                {/* Position with Medal */}
                                                <td className="px-5 py-4 font-mono font-bold whitespace-nowrap">
                                                    <span
                                                        className={`inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold ${
                                                            pos === 1
                                                                ? 'bg-[#f7a600]/20 text-[#f7a600] ring-1 ring-[#f7a600]/40'
                                                                : pos === 2
                                                                  ? 'bg-[#d1d4dc]/20 text-white ring-1 ring-[#d1d4dc]/40'
                                                                  : pos === 3
                                                                    ? 'bg-[#cd7f32]/20 text-[#cd7f32] ring-1 ring-[#cd7f32]/40'
                                                                    : 'bg-[#131722] text-[#787b86]'
                                                        }`}
                                                    >
                                                        #{pos}
                                                    </span>
                                                </td>

                                                {/* Trader Profile */}
                                                <td className="px-5 py-4 whitespace-nowrap">
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-[#2a2e39] bg-[#131722] font-mono text-sm font-bold text-white">
                                                            {row.trader.name
                                                                .slice(0, 2)
                                                                .toUpperCase()}
                                                        </div>
                                                        <div>
                                                            <div className="flex items-center gap-2">
                                                                <span className="font-semibold text-white">
                                                                    {
                                                                        row
                                                                            .trader
                                                                            .name
                                                                    }
                                                                </span>
                                                                {row.trader
                                                                    .rank && (
                                                                    <RankBadge
                                                                        name={
                                                                            row
                                                                                .trader
                                                                                .rank
                                                                                .name
                                                                        }
                                                                        color={
                                                                            row
                                                                                .trader
                                                                                .rank
                                                                                .color
                                                                        }
                                                                    />
                                                                )}
                                                            </div>
                                                            <div className="text-xs text-[#787b86]">
                                                                {row.trader
                                                                    .is_featured
                                                                    ? 'Analis Pilihan Sotra'
                                                                    : 'Strategi Swing & Intraday'}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Win Rate */}
                                                <td className="px-5 py-4 text-right font-mono font-semibold whitespace-nowrap text-[#089981] tabular-nums">
                                                    <div className="flex flex-col items-end">
                                                        <span>
                                                            {formatPercent(
                                                                winRate,
                                                            )}
                                                        </span>
                                                        <div className="mt-1 h-1.5 w-16 overflow-hidden rounded-full bg-[#131722]">
                                                            <div
                                                                className="h-full bg-[#089981]"
                                                                style={{
                                                                    width: `${Math.min(100, Math.max(0, winRate))}%`,
                                                                }}
                                                            />
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* ROI / Total R */}
                                                <td className="px-5 py-4 text-right font-mono font-bold whitespace-nowrap tabular-nums">
                                                    <span
                                                        className={`inline-flex items-center gap-1 ${
                                                            totalR >= 0
                                                                ? 'text-[#089981]'
                                                                : 'text-[#f23645]'
                                                        }`}
                                                    >
                                                        <TrendingUp className="h-3.5 w-3.5" />
                                                        {totalR > 0
                                                            ? `+${totalR}R`
                                                            : `${totalR}R`}
                                                    </span>
                                                </td>

                                                {/* Closed Signals Count */}
                                                <td className="px-5 py-4 text-right font-mono whitespace-nowrap text-[#b2b5be] tabular-nums">
                                                    {closedSignals} Sinyal
                                                </td>

                                                {/* Followers */}
                                                <td className="px-5 py-4 text-right font-mono whitespace-nowrap text-[#b2b5be] tabular-nums">
                                                    {row.followers} Member
                                                </td>

                                                {/* Action Buttons: Copy / Subscribe */}
                                                <td className="px-5 py-4 text-center whitespace-nowrap">
                                                    <div className="inline-flex items-center gap-2">
                                                        <Button
                                                            size="sm"
                                                            onClick={() =>
                                                                handleAction(
                                                                    row.trader
                                                                        .name,
                                                                    'copy',
                                                                )
                                                            }
                                                            className="h-8 bg-[#2962ff] px-3 text-xs font-semibold text-white hover:bg-[#1e53e5]"
                                                        >
                                                            Copy
                                                        </Button>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() =>
                                                                handleAction(
                                                                    row.trader
                                                                        .name,
                                                                    'subscribe',
                                                                )
                                                            }
                                                            className="h-8 border-[#363a45] bg-[#131722] px-3 text-xs text-[#d1d4dc] hover:border-[#2962ff] hover:text-white"
                                                        >
                                                            Subscribe
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="px-5 py-8 text-center text-sm text-[#787b86]"
                                        >
                                            Sedang memuat rekam jejak analis
                                            teratas...
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Footer Teaser Strip */}
                    <div className="flex flex-col items-center justify-between gap-4 border-t border-[#2a2e39] bg-[#131722] px-5 py-4 sm:flex-row">
                        <div className="flex items-center gap-2 text-xs text-[#787b86]">
                            <Lock className="h-4 w-4 text-[#2962ff]" />
                            <span>
                                Hanya menampilkan Top 3 analis. Buka akun gratis
                                untuk melihat 50+ analis lengkap dengan kurva
                                ekuitas dan riwayat trading.
                            </span>
                        </div>
                        <Button
                            size="sm"
                            asChild
                            className="h-8 bg-[#2962ff] text-xs font-medium text-white hover:bg-[#1e53e5]"
                        >
                            <Link href={register()}>
                                Buka Akun & Lihat Semua
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            {/* Auth Prompt Modal when unauthenticated guest clicks Copy or Subscribe */}
            <AuthPromptModal
                isOpen={!!selectedTrader}
                onClose={() => setSelectedTrader(null)}
                traderName={selectedTrader || ''}
                actionType={actionType}
            />
        </section>
    );
}
