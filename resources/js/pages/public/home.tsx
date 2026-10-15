import { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    BadgeCheck,
    BarChart3,
    Building2,
    Calculator,
    GraduationCap,
    LineChart,
    ShieldCheck,
} from 'lucide-react';
import { RankBadge } from '@/components/domain/rank-badge';
import { RiskDisclaimer } from '@/components/domain/risk-disclaimer';
import { TradingCalculator } from '@/components/domain/trading-calculator';
import { TradingChartPreview } from '@/components/domain/trading-chart-preview';
import { Button } from '@/components/ui/button';
import { formatCompact, formatPercent, formatR } from '@/lib/format';
import { cn } from '@/lib/utils';
import { dashboard, leaderboard, register } from '@/routes';
import { index as brokers } from '@/routes/brokers';
import { show as traderProfile } from '@/routes/traders';
import type { LeaderboardRow } from '@/types';

type Props = {
    topTraders: LeaderboardRow[];
    stats: {
        traders: number;
        closed_signals: number;
        losing_signals: number;
        brokers: number;
    };
    riskDisclaimer: string;
};

const PILLARS = [
    {
        icon: BarChart3,
        title: 'Track Record Terbuka & Jujur',
        body: 'Setiap sinyal dikunci saat dipublish dan dinilai otomatis dari pergerakan harga pasar. Hasil kalah tetap dicatat apa adanya.',
    },
    {
        icon: Calculator,
        title: 'Disiplin Manajemen Risiko',
        body: 'Gunakan kalkulator lot dan rasio Risk/Reward minimal 1:1 agar kamu belajar trading terukur, bukan asal tebak.',
    },
    {
        icon: GraduationCap,
        title: 'Edukasi & Alasan Analisa',
        body: 'Setiap sinyal disertai argumen teknikal (Price Action, SnD, Chart Pattern) untuk memperkaya wawasan trading kamu.',
    },
    {
        icon: Building2,
        title: 'Broker Legal Berizin Bappebti',
        body: 'Dana trading sepenuhnya kamu kelola di broker resmi. Sokotra tidak pernah menghimpun atau mengelola dana pengguna.',
    },
];

export default function Home({ topTraders, stats, riskDisclaimer }: Props) {
    const { auth } = usePage().props;
    const [activeFilter, setActiveFilter] = useState<
        'all' | 'high_winrate' | 'featured'
    >('all');

    const filteredTraders = topTraders.filter((row) => {
        if (activeFilter === 'high_winrate') {
            return row.stats.win_rate >= 50;
        }
        if (activeFilter === 'featured') {
            return row.trader.is_featured;
        }
        return true;
    });

    return (
        <>
            <Head title="Platform Analisa & Belajar Trading Berbasis Data Nyata" />
            <div className="flex flex-col gap-8">
                {/* 1. Header Overview & Financial Metrics Strip */}
                <section
                    aria-labelledby="platform-summary-title"
                    className="grid gap-4"
                >
                    <header className="flex flex-col justify-between gap-3 md:flex-row md:items-end">
                        <div className="max-w-2xl">
                            <h1
                                id="platform-summary-title"
                                className="text-xl font-bold tracking-tight text-white md:text-2xl"
                            >
                                Platform Analisa & Kinerja Trader Berbasis Data
                                Pasar Nyata
                            </h1>
                            <p className="mt-1 text-sm text-[#787b86]">
                                Transparansi penuh tanpa manipulasi: setiap
                                sinyal dicatat dan dievaluasi otomatis oleh
                                engine harga pasar.
                            </p>
                        </div>
                        <div className="flex items-center gap-2">
                            <Button
                                size="sm"
                                asChild
                                className="h-9 bg-[#2962ff] px-4 font-medium text-white hover:bg-[#1e53e5]"
                            >
                                <Link
                                    href={auth.user ? dashboard() : register()}
                                >
                                    {auth.user
                                        ? 'Buka Dashboard'
                                        : 'Mulai Belajar Gratis'}
                                    <ArrowRight className="ml-1.5 size-4" />
                                </Link>
                            </Button>
                            <TradingCalculator />
                        </div>
                    </header>

                    {/* Compact Financial Metric Strip */}
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <article className="rounded-lg border border-[#2a2e39] bg-[#1e222d] p-3">
                            <span className="block text-xs font-medium text-[#787b86]">
                                Trader Terverifikasi
                            </span>
                            <span className="mt-1 block font-mono text-xl font-bold text-white tabular-nums">
                                {Number(stats.traders).toLocaleString('id-ID')}
                            </span>
                        </article>
                        <article className="rounded-lg border border-[#2a2e39] bg-[#1e222d] p-3">
                            <span className="block text-xs font-medium text-[#787b86]">
                                Sinyal Selesai Dinilai
                            </span>
                            <span className="mt-1 block font-mono text-xl font-bold text-white tabular-nums">
                                {Number(stats.closed_signals).toLocaleString(
                                    'id-ID',
                                )}
                            </span>
                        </article>
                        <article className="rounded-lg border border-[#2a2e39] bg-[#1e222d] p-3">
                            <span className="block text-xs font-medium text-[#787b86]">
                                Sinyal Kalah (Transparan)
                            </span>
                            <span className="mt-1 block font-mono text-xl font-bold text-[#f23645] tabular-nums">
                                {Number(stats.losing_signals).toLocaleString(
                                    'id-ID',
                                )}
                            </span>
                        </article>
                        <article className="rounded-lg border border-[#2a2e39] bg-[#1e222d] p-3">
                            <span className="block text-xs font-medium text-[#787b86]">
                                Broker Legal Bappebti
                            </span>
                            <span className="mt-1 block font-mono text-xl font-bold text-[#089981] tabular-nums">
                                {Number(stats.brokers).toLocaleString('id-ID')}
                            </span>
                        </article>
                    </div>
                </section>

                {/* 2. Main Workstation: Top Traders Table (eToro/ZuluTrade) + Live Chart Preview */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    {/* Left 7 Columns: Top Traders Discovery Table */}
                    <section
                        aria-labelledby="top-traders-title"
                        className="rounded-lg border border-[#2a2e39] bg-[#1e222d] lg:col-span-7"
                    >
                        <header className="flex flex-col justify-between gap-3 border-b border-[#2a2e39] p-3 sm:flex-row sm:items-center">
                            <div>
                                <h2
                                    id="top-traders-title"
                                    className="text-sm font-semibold text-white"
                                >
                                    Analis Teratas Minggu Ini
                                </h2>
                                <p className="text-xs text-[#787b86]">
                                    Peringkat dihitung objektif dari win rate
                                    dan akumulasi rasio R.
                                </p>
                            </div>

                            {/* Filter Tabs */}
                            <nav
                                aria-label="Filter Trader"
                                className="flex items-center gap-1 rounded border border-[#2a2e39] bg-[#131722] p-0.5 text-xs"
                            >
                                <button
                                    type="button"
                                    onClick={() => setActiveFilter('all')}
                                    className={cn(
                                        'rounded px-2.5 py-1 font-medium transition-colors',
                                        activeFilter === 'all'
                                            ? 'bg-[#2a2e39] text-white'
                                            : 'text-[#787b86] hover:text-[#d1d4dc]',
                                    )}
                                >
                                    Semua
                                </button>
                                <button
                                    type="button"
                                    onClick={() =>
                                        setActiveFilter('high_winrate')
                                    }
                                    className={cn(
                                        'rounded px-2.5 py-1 font-medium transition-colors',
                                        activeFilter === 'high_winrate'
                                            ? 'bg-[#2a2e39] text-white'
                                            : 'text-[#787b86] hover:text-[#d1d4dc]',
                                    )}
                                >
                                    Win Rate &gt; 50%
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setActiveFilter('featured')}
                                    className={cn(
                                        'rounded px-2.5 py-1 font-medium transition-colors',
                                        activeFilter === 'featured'
                                            ? 'bg-[#2a2e39] text-white'
                                            : 'text-[#787b86] hover:text-[#d1d4dc]',
                                    )}
                                >
                                    Pilihan
                                </button>
                            </nav>
                        </header>

                        {/* Dense Trader Performance Table (ala eToro / ZuluTrade) */}
                        {filteredTraders.length === 0 ? (
                            <div className="p-8 text-center text-xs text-[#787b86]">
                                Belum ada trader pada kategori ini.
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead className="border-b border-[#2a2e39] bg-[#171b26] text-[#787b86]">
                                        <tr>
                                            <th
                                                scope="col"
                                                className="px-3 py-2 font-medium"
                                            >
                                                Analis
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-3 py-2 text-right font-medium"
                                            >
                                                Win Rate
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-3 py-2 text-right font-medium"
                                            >
                                                Total R
                                            </th>
                                            <th
                                                scope="col"
                                                className="hidden px-3 py-2 text-right font-medium sm:table-cell"
                                            >
                                                Sinyal
                                            </th>
                                            <th
                                                scope="col"
                                                className="hidden px-3 py-2 text-right font-medium md:table-cell"
                                            >
                                                Follower
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-3 py-2 text-right font-medium"
                                            >
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-[#2a2e39] font-mono text-[#d1d4dc] tabular-nums">
                                        {filteredTraders.map((row) => (
                                            <tr
                                                key={row.trader.slug}
                                                className="transition-colors hover:bg-[#242835]"
                                            >
                                                <td className="px-3 py-2.5 font-sans">
                                                    <div className="flex items-center gap-2">
                                                        <span className="flex size-7 items-center justify-center rounded bg-[#2a2e39] text-xs font-bold text-white">
                                                            {row.trader.name.charAt(
                                                                0,
                                                            )}
                                                        </span>
                                                        <div>
                                                            <Link
                                                                href={traderProfile(
                                                                    row.trader
                                                                        .slug,
                                                                )}
                                                                className="flex items-center gap-1 font-medium text-white hover:text-[#2962ff]"
                                                            >
                                                                {
                                                                    row.trader
                                                                        .name
                                                                }
                                                                <BadgeCheck className="size-3.5 text-[#2962ff]" />
                                                            </Link>
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
                                                    </div>
                                                </td>
                                                <td className="px-3 py-2.5 text-right font-bold text-[#089981]">
                                                    {formatPercent(
                                                        row.stats.win_rate,
                                                    )}
                                                </td>
                                                <td className="px-3 py-2.5 text-right font-bold text-white">
                                                    {formatR(row.stats.total_r)}
                                                </td>
                                                <td className="hidden px-3 py-2.5 text-right text-[#787b86] sm:table-cell">
                                                    {row.stats.closed_signals}
                                                </td>
                                                <td className="hidden px-3 py-2.5 text-right text-[#787b86] md:table-cell">
                                                    {formatCompact(
                                                        row.followers,
                                                    )}
                                                </td>
                                                <td className="px-3 py-2.5 text-right font-sans">
                                                    <Link
                                                        href={traderProfile(
                                                            row.trader.slug,
                                                        )}
                                                        className="inline-flex items-center rounded border border-[#2a2e39] bg-[#131722] px-2 py-1 text-xs font-medium text-white hover:border-[#2962ff] hover:text-[#2962ff]"
                                                    >
                                                        Lihat Sinyal
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}

                        <footer className="border-t border-[#2a2e39] p-2 text-right">
                            <Link
                                href={leaderboard()}
                                className="inline-flex items-center gap-1 text-xs font-medium text-[#2962ff] hover:underline"
                            >
                                Lihat Semua Leaderboard
                                <ArrowRight className="size-3.5" />
                            </Link>
                        </footer>
                    </section>

                    {/* Right 5 Columns: Trading Terminal Preview */}
                    <section
                        aria-labelledby="chart-preview-title"
                        className="flex flex-col gap-4 lg:col-span-5"
                    >
                        <article className="rounded-lg border border-[#2a2e39] bg-[#1e222d] p-3">
                            <h2
                                id="chart-preview-title"
                                className="mb-2 text-xs font-semibold text-white"
                            >
                                Preview Chart Teknis Real-time
                            </h2>
                            <TradingChartPreview
                                symbol="XAU/USD"
                                timeframe="1H"
                                className="border-[#2a2e39] bg-[#131722]"
                            />
                        </article>

                        {/* Analis Recruitment Callout (Utilitarian) */}
                        <article className="flex flex-col gap-2 rounded-lg border border-[#2a2e39] bg-[#1e222d] p-4">
                            <div className="flex items-center gap-1.5 text-xs font-semibold text-[#2962ff]">
                                <LineChart className="size-4" />
                                <span>Peluang untuk Analis Konsisten</span>
                            </div>
                            <h3 className="text-sm font-bold text-white">
                                Bangun Rekam Jejak Terverifikasi Pasar
                            </h3>
                            <p className="text-xs leading-relaxed text-[#787b86]">
                                Dapatkan gaji bulanan dari sistem evaluasi
                                berbasis akurasi, kumpulkan follower nyata, dan
                                buka langganan channel berbayar kamu.
                            </p>
                            <div className="pt-2">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    asChild
                                    className="h-8 border-[#2a2e39] bg-[#131722] text-xs font-medium text-white hover:border-[#2962ff] hover:text-[#2962ff]"
                                >
                                    <Link
                                        href={
                                            auth.user ? dashboard() : register()
                                        }
                                    >
                                        Daftar Jadi Analis
                                    </Link>
                                </Button>
                            </div>
                        </article>
                    </section>
                </div>

                {/* 3. 4 Pillars: Prinsip Finansial & Manajemen Risiko */}
                <section aria-labelledby="pillars-title" className="grid gap-3">
                    <h2
                        id="pillars-title"
                        className="text-sm font-semibold tracking-wider text-[#787b86] uppercase"
                    >
                        Standar Transparansi & Manajemen Risiko
                    </h2>
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        {PILLARS.map(({ icon: Icon, title, body }) => (
                            <article
                                key={title}
                                className="flex flex-col gap-2 rounded-lg border border-[#2a2e39] bg-[#1e222d] p-4"
                            >
                                <div className="flex size-8 items-center justify-center rounded border border-[#2a2e39] bg-[#131722] text-[#2962ff]">
                                    <Icon className="size-4" />
                                </div>
                                <h3 className="text-sm font-semibold text-white">
                                    {title}
                                </h3>
                                <p className="text-xs leading-relaxed text-[#787b86]">
                                    {body}
                                </p>
                            </article>
                        ))}
                    </div>
                </section>

                {/* 4. Official Regulatory & Risk Disclaimer */}
                <section
                    aria-labelledby="broker-legal-title"
                    className="flex flex-col gap-2 border-t border-[#2a2e39] pt-4"
                >
                    <div className="flex items-center gap-2 text-xs font-semibold text-white">
                        <ShieldCheck className="size-4 text-[#089981]" />
                        <h2 id="broker-legal-title">
                            Keamanan Dana & Kemitraan Broker Berizin Resmi
                            Bappebti
                        </h2>
                    </div>
                    <p className="text-xs leading-relaxed text-[#787b86]">
                        Sokotra tidak pernah menghimpun, mengelola, atau
                        menerima deposit dana trading kamu. Seluruh eksekusi dan
                        penempatan dana dilakukan secara langsung pada{' '}
                        <Link
                            href={brokers()}
                            className="font-medium text-[#2962ff] hover:underline"
                        >
                            broker partner berizin resmi Bappebti
                        </Link>
                        .
                    </p>
                    <RiskDisclaimer text={riskDisclaimer} />
                </section>
            </div>
        </>
    );
}
