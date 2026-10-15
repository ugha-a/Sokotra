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
    Trophy,
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
            <div className="grid gap-16 md:gap-20">
                {/* Hero Section: Modern Trading Workstation Feel */}
                <section className="grid gap-10 pt-2 md:pt-6 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                    <div className="grid gap-6">
                        <div className="inline-flex w-fit items-center gap-2 rounded-full border border-border/70 bg-card/60 px-3 py-1 text-xs text-muted-foreground backdrop-blur-xs">
                            <span className="relative flex size-2">
                                <span className="absolute inline-flex size-full animate-ping rounded-full bg-profit opacity-75" />
                                <span className="relative inline-flex size-2 rounded-full bg-profit" />
                            </span>
                            <span className="font-medium text-foreground">
                                Sistem Evaluasi Otomatis
                            </span>
                            <span>·</span>
                            <span>Track Record 100% Riil</span>
                        </div>

                        <h1 className="text-3xl font-bold tracking-tight text-foreground md:text-5xl lg:leading-[1.15]">
                            Tempat trading dan belajar analisa dengan{' '}
                            <span className="text-brand">
                                track record nyata
                            </span>
                            .
                        </h1>

                        <p className="max-w-xl text-base leading-relaxed text-muted-foreground md:text-lg">
                            Bukan janji manis atau tangkapan layar profit palsu.
                            Sokotra menghubungkan kamu dengan trader analis
                            berpengalaman yang kinerjanya diuji otomatis oleh
                            pergerakan harga pasar.
                        </p>

                        <div className="flex flex-wrap items-center gap-3 pt-1">
                            <Button
                                size="lg"
                                className="h-11 px-6 font-semibold shadow-sm"
                                asChild
                            >
                                <Link
                                    href={auth.user ? dashboard() : register()}
                                >
                                    {auth.user
                                        ? 'Buka Dashboard'
                                        : 'Mulai Belajar Gratis'}
                                    <ArrowRight className="ml-1 size-4" />
                                </Link>
                            </Button>
                            <Button
                                size="lg"
                                variant="outline"
                                className="h-11 px-5"
                                asChild
                            >
                                <Link href={leaderboard()}>
                                    <Trophy className="mr-1 size-4 text-brand" />
                                    Lihat Leaderboard
                                </Link>
                            </Button>
                            <TradingCalculator />
                        </div>

                        {/* Financial Metric Counters */}
                        <dl className="grid grid-cols-2 gap-4 rounded-xl border border-border/80 bg-card/40 p-4 font-mono sm:grid-cols-4">
                            {[
                                ['Trader Terverifikasi', stats.traders],
                                ['Sinyal Dinilai', stats.closed_signals],
                                [
                                    'Sinyal Kalah (Transparan)',
                                    stats.losing_signals,
                                ],
                                ['Broker Legal Bappebti', stats.brokers],
                            ].map(([label, value]) => (
                                <div key={label} className="grid gap-1">
                                    <dt className="font-sans text-[11px] leading-tight text-muted-foreground">
                                        {label}
                                    </dt>
                                    <dd className="text-xl font-bold text-foreground tabular-nums">
                                        {Number(value).toLocaleString('id-ID')}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </div>

                    {/* Right column: Interactive Candlestick Preview */}
                    <div className="relative">
                        <div className="absolute -inset-1 rounded-2xl bg-gradient-to-r from-brand/20 via-profit/10 to-transparent opacity-50 blur-xl" />
                        <TradingChartPreview
                            symbol="XAU/USD"
                            timeframe="1H"
                            className="relative border-border shadow-lg"
                        />
                    </div>
                </section>

                {/* 4 Pillars Section: Tempat Trading & Belajar Nyaman */}
                <section className="grid gap-6">
                    <div className="flex max-w-2xl flex-col gap-2">
                        <span className="text-xs font-semibold tracking-wider text-brand uppercase">
                            Komitmen Transparansi
                        </span>
                        <h2 className="text-2xl font-bold tracking-tight md:text-3xl">
                            Dirancang agar kamu belajar trading secara aman dan
                            rasional
                        </h2>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {PILLARS.map(({ icon: Icon, title, body }) => (
                            <div
                                key={title}
                                className="grid content-start gap-3 rounded-xl border border-border/80 bg-card p-5 transition-all duration-200 hover:border-brand/40 hover:shadow-xs"
                            >
                                <div className="flex size-10 items-center justify-center rounded-lg bg-brand/10 text-brand">
                                    <Icon className="size-5" />
                                </div>
                                <h3 className="text-base font-semibold text-foreground">
                                    {title}
                                </h3>
                                <p className="text-sm leading-relaxed text-muted-foreground">
                                    {body}
                                </p>
                            </div>
                        ))}
                    </div>
                </section>

                {/* Top Traders Spotlight with Interactive Filter */}
                <section className="grid gap-6">
                    <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div className="grid gap-1">
                            <span className="text-xs font-semibold tracking-wider text-brand uppercase">
                                Leaderboard
                            </span>
                            <h2 className="text-2xl font-bold tracking-tight">
                                Analis Teratas Minggu Ini
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Pemeringkatan berbasis rasio skor R dan win rate
                                terverifikasi.
                            </p>
                        </div>

                        <div className="flex items-center gap-2">
                            <div className="flex rounded-lg border border-border/80 bg-muted/40 p-1 text-xs">
                                <button
                                    type="button"
                                    onClick={() => setActiveFilter('all')}
                                    className={cn(
                                        'rounded-md px-3 py-1 font-medium transition-all',
                                        activeFilter === 'all'
                                            ? 'bg-card font-semibold text-foreground shadow-2xs'
                                            : 'text-muted-foreground hover:text-foreground',
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
                                        'rounded-md px-3 py-1 font-medium transition-all',
                                        activeFilter === 'high_winrate'
                                            ? 'bg-card font-semibold text-foreground shadow-2xs'
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    Win Rate &gt; 50%
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setActiveFilter('featured')}
                                    className={cn(
                                        'rounded-md px-3 py-1 font-medium transition-all',
                                        activeFilter === 'featured'
                                            ? 'bg-card font-semibold text-foreground shadow-2xs'
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    Pilihan Sokotra
                                </button>
                            </div>
                            <Button variant="ghost" size="sm" asChild>
                                <Link href={leaderboard()}>
                                    Semua{' '}
                                    <ArrowRight className="ml-1 size-3.5" />
                                </Link>
                            </Button>
                        </div>
                    </div>

                    {filteredTraders.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-border/80 p-8 text-center text-sm text-muted-foreground">
                            Belum ada trader dengan kriteria ini.
                        </div>
                    ) : (
                        <div className="grid gap-4 md:grid-cols-3">
                            {filteredTraders.map((row) => (
                                <Link
                                    key={row.trader.slug}
                                    href={traderProfile(row.trader.slug)}
                                    className="group grid content-between gap-4 rounded-xl border border-border/80 bg-card p-5 transition-all duration-200 hover:border-brand/60 hover:shadow-md"
                                >
                                    <div className="flex items-center justify-between gap-2">
                                        <div className="flex items-center gap-2">
                                            <div className="flex size-9 items-center justify-center rounded-full bg-brand/10 text-sm font-bold text-brand">
                                                {row.trader.name.charAt(0)}
                                            </div>
                                            <div>
                                                <span className="flex items-center gap-1.5 font-semibold text-foreground transition-colors group-hover:text-brand">
                                                    {row.trader.name}
                                                    <BadgeCheck className="size-4 text-brand" />
                                                </span>
                                                <span className="font-mono text-[11px] text-muted-foreground">
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

                                    <div className="grid grid-cols-3 gap-2 rounded-lg border border-border/50 bg-muted/40 p-3 text-center font-mono">
                                        <div>
                                            <dt className="font-sans text-[11px] text-muted-foreground">
                                                Win Rate
                                            </dt>
                                            <dd className="text-sm font-bold text-profit tabular-nums">
                                                {formatPercent(
                                                    row.stats.win_rate,
                                                )}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="font-sans text-[11px] text-muted-foreground">
                                                Total R
                                            </dt>
                                            <dd className="text-sm font-bold text-foreground tabular-nums">
                                                {formatR(row.stats.total_r)}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="font-sans text-[11px] text-muted-foreground">
                                                Sinyal
                                            </dt>
                                            <dd className="text-sm font-semibold text-muted-foreground tabular-nums">
                                                {row.stats.closed_signals}
                                            </dd>
                                        </div>
                                    </div>

                                    <div className="flex items-center justify-between pt-1 text-xs font-medium text-brand">
                                        <span>
                                            Lihat riwayat sinyal & kurva
                                        </span>
                                        <ArrowRight className="size-3.5 transition-transform group-hover:translate-x-1" />
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}
                </section>

                {/* Trader Recruitment Callout */}
                <section className="relative overflow-hidden rounded-2xl border border-brand/30 bg-gradient-to-br from-card via-card to-brand/5 p-6 md:p-8">
                    <div className="grid gap-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div className="grid gap-2">
                            <div className="flex items-center gap-2 text-sm font-semibold text-brand">
                                <LineChart className="size-5" />
                                <span>Program Analis & Trader Sokotra</span>
                            </div>
                            <h2 className="text-xl font-bold tracking-tight text-foreground md:text-2xl">
                                Kamu analis yang memiliki metode teruji dan
                                konsisten?
                            </h2>
                            <p className="max-w-2xl text-sm leading-relaxed text-muted-foreground">
                                Bangun track record terbuka, kumpulkan follower
                                terverifikasi, dapatkan gaji bulanan otomatis
                                berdasarkan akurasi analisa, dan buka channel
                                langganan premium kamu sendiri.
                            </p>
                        </div>
                        <Button
                            size="lg"
                            className="font-semibold shadow-xs"
                            asChild
                        >
                            <Link href={auth.user ? dashboard() : register()}>
                                Gabung Jadi Trader
                            </Link>
                        </Button>
                    </div>
                </section>

                {/* Official Regulatory & Risk Disclaimer */}
                <section className="grid gap-3 pt-2">
                    <div className="flex items-center gap-2 font-semibold text-foreground">
                        <ShieldCheck className="size-5 text-profit" />
                        <span>Keamanan Dana & Kemitraan Broker Berizin</span>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        Sokotra tidak pernah menghimpun, mengelola, atau
                        menerima deposit dana trading kamu. Seluruh eksekusi dan
                        penempatan dana dilakukan secara langsung pada{' '}
                        <Link
                            href={brokers()}
                            className="font-medium text-brand hover:underline"
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
