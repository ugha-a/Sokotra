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
    Sparkles,
    Trophy,
    TrendingUp,
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
        badgeColor: 'bg-blue-500/10 text-blue-400 border-blue-500/25',
    },
    {
        icon: Calculator,
        title: 'Disiplin Manajemen Risiko',
        body: 'Gunakan kalkulator lot dan rasio Risk/Reward minimal 1:1 agar kamu belajar trading terukur, bukan asal tebak.',
        badgeColor: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25',
    },
    {
        icon: GraduationCap,
        title: 'Edukasi & Alasan Analisa',
        body: 'Setiap sinyal disertai argumen teknikal (Price Action, SnD, Chart Pattern) untuk memperkaya wawasan trading kamu.',
        badgeColor: 'bg-purple-500/10 text-purple-400 border-purple-500/25',
    },
    {
        icon: Building2,
        title: 'Broker Legal Berizin Bappebti',
        body: 'Dana trading sepenuhnya kamu kelola di broker resmi. Sokotra tidak pernah menghimpun atau mengelola dana pengguna.',
        badgeColor: 'bg-amber-500/10 text-amber-400 border-amber-500/25',
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
            <div className="grid gap-16 md:gap-24">
                {/* Hero Section: Modern Trading Workstation Feel (Inspired by TradingView & Bybit) */}
                <section className="grid gap-12 pt-4 md:pt-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                    <div className="grid gap-6">
                        {/* Live Status Badge */}
                        <div className="inline-flex w-fit items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-950/40 px-3.5 py-1 text-xs text-cyan-300 shadow-xs shadow-cyan-500/10 backdrop-blur-md">
                            <span className="relative flex size-2">
                                <span className="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                                <span className="relative inline-flex size-2 rounded-full bg-emerald-400" />
                            </span>
                            <span className="font-semibold text-white">
                                Sistem Evaluasi Otomatis
                            </span>
                            <span className="text-cyan-500">·</span>
                            <span className="text-slate-300">
                                Track Record 100% Riil
                            </span>
                        </div>

                        {/* High-Impact Headline with Gradient Shimmer */}
                        <h1 className="text-3xl font-extrabold tracking-tight text-white md:text-5xl lg:text-6xl lg:leading-[1.12]">
                            Tempat trading dan belajar analisa dengan{' '}
                            <span className="bg-gradient-to-r from-blue-400 via-cyan-300 to-emerald-400 bg-clip-text text-transparent">
                                track record nyata
                            </span>
                            .
                        </h1>

                        <p className="max-w-xl text-base leading-relaxed text-slate-300 md:text-lg">
                            Bukan janji manis atau tangkapan layar profit palsu.
                            Sokotra menghubungkan kamu dengan trader analis
                            berpengalaman yang kinerjanya diuji otomatis oleh
                            pergerakan harga pasar secara transparan.
                        </p>

                        {/* Interactive CTAs */}
                        <div className="flex flex-wrap items-center gap-3.5 pt-2">
                            <Button
                                size="lg"
                                className="h-12 border border-cyan-400/30 bg-gradient-to-r from-blue-600 via-cyan-600 to-emerald-600 px-7 font-bold text-white shadow-lg shadow-cyan-500/25 transition-all hover:scale-[1.02] hover:from-blue-500 hover:to-emerald-500 hover:shadow-cyan-500/40"
                                asChild
                            >
                                <Link
                                    href={auth.user ? dashboard() : register()}
                                >
                                    {auth.user
                                        ? 'Buka Terminal Trading'
                                        : 'Mulai Belajar Gratis'}
                                    <ArrowRight className="ml-2 size-4" />
                                </Link>
                            </Button>
                            <Button
                                size="lg"
                                variant="outline"
                                className="h-12 border-white/15 bg-white/5 px-6 font-semibold text-slate-200 backdrop-blur-md transition-all hover:border-white/25 hover:bg-white/10 hover:text-white"
                                asChild
                            >
                                <Link href={leaderboard()}>
                                    <Trophy className="mr-2 size-4 text-cyan-400" />
                                    Lihat Leaderboard
                                </Link>
                            </Button>
                            <TradingCalculator
                                trigger={
                                    <Button
                                        size="lg"
                                        variant="outline"
                                        className="h-12 border-white/15 bg-white/5 px-6 font-semibold text-slate-200 backdrop-blur-md transition-all hover:border-cyan-500/40 hover:bg-white/10 hover:text-white"
                                    >
                                        <Calculator className="mr-2 size-4 text-emerald-400" />
                                        Kalkulator Risiko
                                    </Button>
                                }
                            />
                        </div>

                        {/* Financial Metrics Counters with Elevated Glass Layering */}
                        <dl className="grid grid-cols-2 gap-3 rounded-2xl border border-white/10 bg-[#121826]/80 p-4 font-mono shadow-xl shadow-black/30 backdrop-blur-xl sm:grid-cols-4">
                            {[
                                [
                                    'Trader Terverifikasi',
                                    stats.traders,
                                    'text-cyan-400',
                                ],
                                [
                                    'Sinyal Dinilai',
                                    stats.closed_signals,
                                    'text-white',
                                ],
                                [
                                    'Sinyal Kalah (Jujur)',
                                    stats.losing_signals,
                                    'text-ruby-400 text-rose-400',
                                ],
                                [
                                    'Broker Berizin Bappebti',
                                    stats.brokers,
                                    'text-emerald-400',
                                ],
                            ].map(([label, value, colorClass]) => (
                                <div
                                    key={label}
                                    className="flex flex-col gap-1 rounded-xl border border-white/5 bg-white/[0.02] p-2"
                                >
                                    <dt className="font-sans text-[11px] leading-tight font-medium text-slate-400">
                                        {label}
                                    </dt>
                                    <dd
                                        className={cn(
                                            'text-2xl font-extrabold tracking-tight tabular-nums',
                                            colorClass,
                                        )}
                                    >
                                        {Number(value).toLocaleString('id-ID')}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </div>

                    {/* Right column: Interactive Candlestick Preview with Luminous Backdrop */}
                    <div className="relative">
                        <div className="pointer-events-none absolute -inset-2 rounded-3xl bg-gradient-to-tr from-blue-600/30 via-cyan-500/20 to-emerald-500/30 opacity-70 blur-2xl" />
                        <TradingChartPreview
                            symbol="XAU/USD"
                            timeframe="1H"
                            className="relative border-white/10 bg-[#121826]/90 shadow-2xl backdrop-blur-xl"
                        />
                    </div>
                </section>

                {/* 4 Pillars Section: Tempat Trading & Belajar Nyaman (Traders Family & TradingView Philosophy) */}
                <section className="grid gap-8">
                    <div className="flex max-w-2xl flex-col gap-2">
                        <div className="inline-flex items-center gap-1.5 text-xs font-bold tracking-wider text-cyan-400 uppercase">
                            <Sparkles className="size-3.5" />
                            <span>
                                Komitmen Transparansi & Manajemen Risiko
                            </span>
                        </div>
                        <h2 className="text-2xl font-bold tracking-tight text-white md:text-3xl">
                            Dirancang agar kamu belajar trading secara aman,
                            rasional, dan terukur
                        </h2>
                        <p className="text-sm leading-relaxed text-slate-400">
                            Kami menghapus mitos cepat kaya dan menggantikannya
                            dengan disiplin kalkulasi risiko, evaluasi
                            independen, dan edukasi teknikal mendalam.
                        </p>
                    </div>

                    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        {PILLARS.map(
                            ({ icon: Icon, title, body, badgeColor }) => (
                                <div
                                    key={title}
                                    className="trading-glass trading-card-glow grid content-start gap-4 rounded-2xl border border-white/10 bg-[#121826]/70 p-6 shadow-xl backdrop-blur-xl hover:border-cyan-500/40"
                                >
                                    <div
                                        className={cn(
                                            'flex size-11 items-center justify-center rounded-xl border',
                                            badgeColor,
                                        )}
                                    >
                                        <Icon className="size-5" />
                                    </div>
                                    <h3 className="text-base font-bold text-white">
                                        {title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-slate-300">
                                        {body}
                                    </p>
                                </div>
                            ),
                        )}
                    </div>
                </section>

                {/* Top Traders Spotlight with Interactive Filter (Inspired by eToro & Bybit Leaderboard) */}
                <section className="grid gap-8">
                    <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div className="grid gap-1">
                            <div className="inline-flex items-center gap-1.5 text-xs font-bold tracking-wider text-cyan-400 uppercase">
                                <TrendingUp className="size-3.5" />
                                <span>Leaderboard Terverifikasi</span>
                            </div>
                            <h2 className="text-2xl font-bold tracking-tight text-white md:text-3xl">
                                Analis Teratas Minggu Ini
                            </h2>
                            <p className="text-sm text-slate-400">
                                Pemeringkatan berbasis performa rasio R dan win
                                rate terverifikasi mesin pasar.
                            </p>
                        </div>

                        {/* Interactive Filter Pills */}
                        <div className="flex items-center gap-3">
                            <div className="flex rounded-xl border border-white/10 bg-[#121826]/90 p-1 text-xs backdrop-blur-md">
                                <button
                                    type="button"
                                    onClick={() => setActiveFilter('all')}
                                    className={cn(
                                        'rounded-lg px-3.5 py-1.5 font-semibold transition-all',
                                        activeFilter === 'all'
                                            ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-sm'
                                            : 'text-slate-400 hover:text-white',
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
                                        'rounded-lg px-3.5 py-1.5 font-semibold transition-all',
                                        activeFilter === 'high_winrate'
                                            ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-sm'
                                            : 'text-slate-400 hover:text-white',
                                    )}
                                >
                                    Win Rate &gt; 50%
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setActiveFilter('featured')}
                                    className={cn(
                                        'rounded-lg px-3.5 py-1.5 font-semibold transition-all',
                                        activeFilter === 'featured'
                                            ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-sm'
                                            : 'text-slate-400 hover:text-white',
                                    )}
                                >
                                    Pilihan Sokotra
                                </button>
                            </div>
                            <Button
                                variant="ghost"
                                size="sm"
                                asChild
                                className="font-medium text-cyan-400 hover:bg-cyan-950/30 hover:text-cyan-300"
                            >
                                <Link href={leaderboard()}>
                                    Semua Leaderboard{' '}
                                    <ArrowRight className="ml-1 size-3.5" />
                                </Link>
                            </Button>
                        </div>
                    </div>

                    {filteredTraders.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-white/15 bg-[#121826]/40 p-10 text-center text-sm text-slate-400">
                            Belum ada trader dengan kriteria ini.
                        </div>
                    ) : (
                        <div className="grid gap-5 md:grid-cols-3">
                            {filteredTraders.map((row) => (
                                <Link
                                    key={row.trader.slug}
                                    href={traderProfile(row.trader.slug)}
                                    className="trading-glass trading-card-glow group grid content-between gap-5 rounded-2xl border border-white/10 bg-[#121826]/80 p-6 backdrop-blur-xl transition-all duration-300 hover:border-cyan-500/50 hover:shadow-2xl hover:shadow-cyan-500/10"
                                >
                                    <div className="flex items-center justify-between gap-2">
                                        <div className="flex items-center gap-3">
                                            <div className="flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 text-base font-bold text-white shadow-md shadow-cyan-500/20">
                                                {row.trader.name.charAt(0)}
                                            </div>
                                            <div>
                                                <span className="flex items-center gap-1.5 font-bold text-white transition-colors group-hover:text-cyan-400">
                                                    {row.trader.name}
                                                    <BadgeCheck className="size-4 text-cyan-400" />
                                                </span>
                                                <span className="font-mono text-xs text-slate-400">
                                                    {formatCompact(
                                                        row.followers,
                                                    )}{' '}
                                                    follower
                                                </span>
                                            </div>
                                        </div>
                                        {row.trader.rank && (
                                            <RankBadge
                                                name={row.trader.rank.name}
                                                color={row.trader.rank.color}
                                            />
                                        )}
                                    </div>

                                    {/* Trader Stats Box */}
                                    <div className="grid grid-cols-3 gap-2 rounded-xl border border-white/10 bg-white/[0.03] p-3.5 text-center font-mono">
                                        <div>
                                            <dt className="font-sans text-[11px] font-medium text-slate-400">
                                                Win Rate
                                            </dt>
                                            <dd className="text-base font-extrabold text-emerald-400 tabular-nums">
                                                {formatPercent(
                                                    row.stats.win_rate,
                                                )}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="font-sans text-[11px] font-medium text-slate-400">
                                                Total R
                                            </dt>
                                            <dd className="text-base font-extrabold text-cyan-300 tabular-nums">
                                                {formatR(row.stats.total_r)}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="font-sans text-[11px] font-medium text-slate-400">
                                                Sinyal
                                            </dt>
                                            <dd className="text-base font-bold text-slate-300 tabular-nums">
                                                {row.stats.closed_signals}
                                            </dd>
                                        </div>
                                    </div>

                                    {/* Action Footnote */}
                                    <div className="flex items-center justify-between pt-1 text-xs font-semibold text-cyan-400 group-hover:text-cyan-300">
                                        <span>
                                            Lihat riwayat sinyal & kurva R
                                        </span>
                                        <ArrowRight className="size-3.5 transition-transform group-hover:translate-x-1.5" />
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}
                </section>

                {/* Trader Recruitment Callout (Inspirasi Traders Family & Bybit Pro Analis) */}
                <section className="relative overflow-hidden rounded-3xl border border-cyan-500/30 bg-gradient-to-r from-[#121826] via-[#151c2e] to-[#0d1c30] p-8 shadow-2xl backdrop-blur-xl md:p-10">
                    <div className="pointer-events-none absolute -top-20 -right-20 size-72 rounded-full bg-cyan-500/15 blur-3xl" />
                    <div className="pointer-events-none absolute -bottom-20 -left-20 size-72 rounded-full bg-blue-600/15 blur-3xl" />

                    <div className="relative grid gap-6 md:grid-cols-[1fr_auto] md:items-center">
                        <div className="grid gap-3">
                            <div className="inline-flex items-center gap-2 text-xs font-bold tracking-wider text-cyan-400 uppercase">
                                <LineChart className="size-4" />
                                <span>Program Analis & Trader Sokotra</span>
                            </div>
                            <h2 className="text-2xl font-bold tracking-tight text-white md:text-3xl">
                                Kamu analis yang memiliki metode teruji dan
                                konsisten?
                            </h2>
                            <p className="max-w-2xl text-sm leading-relaxed text-slate-300">
                                Bangun track record terbuka tanpa manipulasi,
                                kumpulkan follower terverifikasi, dapatkan
                                kompensasi bulanan otomatis berdasarkan akurasi
                                analisa, dan buka channel langganan premium kamu
                                sendiri.
                            </p>
                        </div>
                        <Button
                            size="lg"
                            className="h-12 border border-cyan-400/30 bg-gradient-to-r from-blue-600 to-cyan-600 px-7 font-bold text-white shadow-lg shadow-cyan-500/25 hover:from-blue-500 hover:to-cyan-500"
                            asChild
                        >
                            <Link href={auth.user ? dashboard() : register()}>
                                Gabung Jadi Trader
                                <ArrowRight className="ml-2 size-4" />
                            </Link>
                        </Button>
                    </div>
                </section>

                {/* Official Regulatory & Risk Disclaimer (Traders Family Bappebti Standard) */}
                <section className="grid gap-4 pt-2">
                    <div className="flex items-center gap-2.5 font-bold text-white">
                        <ShieldCheck className="size-5 text-emerald-400" />
                        <span>
                            Keamanan Dana & Kemitraan Broker Berizin Resmi
                        </span>
                    </div>
                    <p className="text-sm leading-relaxed text-slate-400">
                        Sokotra tidak pernah menghimpun, mengelola, atau
                        menerima deposit dana trading kamu. Seluruh eksekusi dan
                        penempatan dana dilakukan secara langsung pada{' '}
                        <Link
                            href={brokers()}
                            className="font-semibold text-cyan-400 hover:underline"
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
