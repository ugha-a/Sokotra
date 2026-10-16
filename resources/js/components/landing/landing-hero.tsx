import { ArrowRight, CheckCircle2, TrendingUp } from 'lucide-react';
import { TradingChartPreview } from '@/components/domain/trading-chart-preview';
import { Button } from '@/components/ui/button';
import { formatCompact } from '@/lib/format';

type LandingHeroProps = {
    badge?: string;
    title?: string;
    subtitle?: string;
    primaryCta?: string;
    secondaryCta?: string;
    stats: {
        traders: number;
        closed_signals: number;
        losing_signals: number;
        brokers: number;
    };
};

export function LandingHero({
    badge = 'Platform Analisa & Sinyal Trading Terverifikasi',
    title = 'Platform Analisa & Sinyal Trading dengan Rekam Jejak Terverifikasi',
    subtitle = 'Bukan sekadar rekomendasi. Setiap sinyal terikat Stop Loss, Take Profit, dan diaudit otomatis secara real-time dari harga pasar riil tanpa sensor riwayat loss.',
    primaryCta = 'Cari Analis & Sinyal',
    secondaryCta = 'Monetisasi Strategimu',
    stats,
}: LandingHeroProps) {
    return (
        <section className="relative border-b border-[#2a2e39] bg-[#131722] py-12 lg:py-20">
            <div className="mx-auto max-w-7xl px-4 sm:px-6">
                <div className="grid items-center gap-12 lg:grid-cols-12 lg:gap-8">
                    {/* Left Column: Direct Value Proposition & Dual CTAs */}
                    <div className="flex flex-col items-start lg:col-span-6">
                        {/* Trust Tag */}
                        <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#2a2e39] bg-[#1e222d] px-3.5 py-1 text-xs font-medium text-[#2962ff]">
                            <span className="h-1.5 w-1.5 rounded-full bg-[#2962ff]" />
                            <span>{badge}</span>
                        </div>

                        {/* High-Impact Headline (Strict Anti-Slop: No text-6xl) */}
                        <h1 className="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl lg:leading-[1.15]">
                            {title}
                        </h1>

                        {/* Sharp, Honest Subtitle */}
                        <p className="mt-5 text-base leading-relaxed text-[#b2b5be] sm:text-lg">
                            {subtitle}
                        </p>

                        {/* Dual Audience CTA (Member vs Analyst) */}
                        <div className="mt-8 flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
                            {/* Primary Button: Target Member */}
                            <Button
                                size="lg"
                                asChild
                                className="h-12 bg-[#2962ff] px-6 text-base font-semibold text-white shadow-sm hover:bg-[#1e53e5]"
                            >
                                <a
                                    href="#live-leaderboard"
                                    className="inline-flex items-center justify-center gap-2"
                                >
                                    <span>{primaryCta}</span>
                                    <ArrowRight className="h-4 w-4" />
                                </a>
                            </Button>

                            {/* Secondary Outline: Target Trader Analis */}
                            <Button
                                size="lg"
                                variant="outline"
                                asChild
                                className="h-12 border-[#363a45] bg-[#1e222d] px-6 text-base font-semibold text-white hover:border-[#2962ff] hover:bg-[#2a2e39]"
                            >
                                <a
                                    href="#analyst-pitch"
                                    className="inline-flex items-center justify-center gap-2"
                                >
                                    <TrendingUp className="h-4 w-4 text-[#2962ff]" />
                                    <span>{secondaryCta}</span>
                                </a>
                            </Button>
                        </div>

                        {/* Core Value Checkmarks */}
                        <div className="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-[#787b86]">
                            <span className="flex items-center gap-1.5 text-[#d1d4dc]">
                                <CheckCircle2 className="h-3.5 w-3.5 text-[#089981]" />
                                Audit Loss Transparan
                            </span>
                            <span className="flex items-center gap-1.5 text-[#d1d4dc]">
                                <CheckCircle2 className="h-3.5 w-3.5 text-[#089981]" />
                                Non-Custodial (Dana Aman)
                            </span>
                            <span className="flex items-center gap-1.5 text-[#d1d4dc]">
                                <CheckCircle2 className="h-3.5 w-3.5 text-[#089981]" />
                                Partner Broker Bappebti
                            </span>
                        </div>

                        {/* Real Audited Platform Metrics */}
                        <div className="mt-10 grid w-full grid-cols-2 gap-3 border-t border-[#2a2e39] pt-6 sm:grid-cols-4">
                            <div className="rounded-lg border border-[#2a2e39] bg-[#1e222d]/60 p-3">
                                <div className="font-mono text-xl font-bold text-white tabular-nums sm:text-2xl">
                                    {stats.traders}
                                </div>
                                <div className="mt-0.5 text-[11px] font-medium text-[#787b86]">
                                    Analis Disetujui
                                </div>
                            </div>
                            <div className="rounded-lg border border-[#2a2e39] bg-[#1e222d]/60 p-3">
                                <div className="font-mono text-xl font-bold text-white tabular-nums sm:text-2xl">
                                    {formatCompact(stats.closed_signals)}
                                </div>
                                <div className="mt-0.5 text-[11px] font-medium text-[#787b86]">
                                    Sinyal Diaudit
                                </div>
                            </div>
                            <div className="rounded-lg border border-[#2a2e39] bg-[#1e222d]/60 p-3">
                                <div className="font-mono text-xl font-bold text-[#f23645] tabular-nums sm:text-2xl">
                                    {formatCompact(stats.losing_signals)}
                                </div>
                                <div className="mt-0.5 text-[11px] font-medium text-[#787b86]">
                                    Loss Dicatat Jujur
                                </div>
                            </div>
                            <div className="rounded-lg border border-[#2a2e39] bg-[#1e222d]/60 p-3">
                                <div className="font-mono text-xl font-bold text-[#089981] tabular-nums sm:text-2xl">
                                    {stats.brokers}
                                </div>
                                <div className="mt-0.5 text-[11px] font-medium text-[#787b86]">
                                    Broker Resmi
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Right Column: Sharp, Data-Dense Workstation Mockup (TradingView vibe) */}
                    <div className="lg:col-span-6">
                        <div className="overflow-hidden rounded-xl border border-[#2a2e39] bg-[#1e222d] shadow-2xl">
                            {/* Window Header */}
                            <div className="flex items-center justify-between border-b border-[#2a2e39] bg-[#131722] px-4 py-2.5">
                                <div className="flex items-center gap-2">
                                    <div className="flex gap-1.5">
                                        <div className="h-3 w-3 rounded-full bg-[#f23645]/80" />
                                        <div className="h-3 w-3 rounded-full bg-[#f7a600]/80" />
                                        <div className="h-3 w-3 rounded-full bg-[#089981]/80" />
                                    </div>
                                    <span className="ml-2 font-mono text-xs font-semibold text-white">
                                        SOKOTRA_PRO_TERMINAL
                                    </span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className="rounded bg-[#089981]/15 px-2 py-0.5 font-mono text-[10px] font-medium text-[#089981]">
                                        LIVE AUDIT FEED
                                    </span>
                                </div>
                            </div>

                            {/* Chart Terminal Preview */}
                            <div className="p-3">
                                <TradingChartPreview />
                            </div>

                            {/* Utilitarian Terminal Bar */}
                            <div className="flex flex-wrap items-center justify-between border-t border-[#2a2e39] bg-[#131722] px-4 py-2 text-xs text-[#787b86]">
                                <span className="font-mono text-[11px]">
                                    EXECUTION: MARKET | NO DELAY
                                </span>
                                <span className="font-mono text-[11px] text-[#089981]">
                                    SYSTEM INTEGRITY: 100% VERIFIED
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
