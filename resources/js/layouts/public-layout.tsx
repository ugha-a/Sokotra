import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import { MarketTickerTape } from '@/components/domain/market-ticker-tape';
import { TradingCalculator } from '@/components/domain/trading-calculator';
import { Button } from '@/components/ui/button';
import { dashboard, home, leaderboard, login, register } from '@/routes';
import { index as brokers } from '@/routes/brokers';
import { show as showPage } from '@/routes/pages';
import { index as marketplace } from '@/routes/marketplace';

/**
 * Layout for public-facing trading portal.
 * Features ambient lighting, glassmorphism, subtle grid patterns, and live market streaming.
 */
export default function PublicLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { auth, riskDisclaimer, legalPages } = usePage().props;

    return (
        <div className="dark relative flex min-h-screen flex-col bg-[#090d16] text-slate-100 selection:bg-brand/30 selection:text-white">
            {/* Ambient Background Lighting Mesh */}
            <div className="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[750px] overflow-hidden">
                <div className="absolute top-[-250px] left-1/2 h-[550px] w-[1000px] -translate-x-1/2 rounded-full bg-gradient-to-b from-blue-600/25 via-cyan-500/15 to-transparent opacity-70 blur-[140px]" />
                <div className="absolute top-[150px] right-[-100px] h-[450px] w-[600px] rounded-full bg-emerald-500/10 opacity-60 blur-[140px]" />
                <div className="absolute top-[400px] left-[-100px] h-[400px] w-[500px] rounded-full bg-purple-600/10 opacity-40 blur-[130px]" />
            </div>

            {/* Subtle Trading Technical Grid Pattern */}
            <div className="trading-grid-bg pointer-events-none absolute inset-0 -z-10 opacity-[0.22]" />

            {/* Live Market Ticker Tape */}
            <MarketTickerTape />

            {/* Glass Navigation Header */}
            <header className="sticky top-0 z-40 border-b border-white/10 bg-[#090d16]/80 shadow-lg shadow-black/20 backdrop-blur-xl">
                <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                    <Link
                        href={home()}
                        className="group flex items-center gap-2"
                    >
                        <AppLogo />
                    </Link>
                    <nav className="flex items-center gap-1 sm:gap-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            asChild
                            className="font-medium text-slate-300 hover:bg-white/5 hover:text-white"
                        >
                            <Link href={leaderboard()}>Leaderboard</Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            asChild
                            className="font-medium text-slate-300 hover:bg-white/5 hover:text-white"
                        >
                            <Link href={marketplace()}>Marketplace</Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            asChild
                            className="hidden font-medium text-slate-300 hover:bg-white/5 hover:text-white sm:inline-flex"
                        >
                            <Link href={brokers()}>Broker Legal</Link>
                        </Button>

                        {/* Interactive Risk & Lot Calculator Trigger */}
                        <TradingCalculator />

                        {auth.user ? (
                            <Button
                                size="sm"
                                asChild
                                className="border border-blue-400/30 bg-gradient-to-r from-blue-600 to-cyan-600 font-semibold text-white shadow-md shadow-blue-500/25 hover:from-blue-500 hover:to-cyan-500"
                            >
                                <Link href={dashboard()}>Buka Terminal</Link>
                            </Button>
                        ) : (
                            <>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    asChild
                                    className="text-slate-300 hover:bg-white/5 hover:text-white"
                                >
                                    <Link href={login()}>Masuk</Link>
                                </Button>
                                <Button
                                    size="sm"
                                    asChild
                                    className="border border-blue-400/30 bg-gradient-to-r from-blue-600 to-cyan-600 font-semibold text-white shadow-md shadow-blue-500/25 hover:from-blue-500 hover:to-cyan-500"
                                >
                                    <Link href={register()}>Daftar Gratis</Link>
                                </Button>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 md:py-12">
                {children}
            </main>

            {/* Glass Footer */}
            <footer className="border-t border-white/10 bg-[#090d16]/90 backdrop-blur-lg">
                <div className="mx-auto grid max-w-7xl gap-4 px-4 py-8 sm:px-6">
                    {legalPages.length > 0 && (
                        <nav
                            aria-label="Informasi"
                            className="flex flex-wrap gap-x-6 gap-y-2 text-xs font-medium"
                        >
                            {legalPages.map((page) => (
                                <Link
                                    key={page.slug}
                                    href={showPage(page.slug)}
                                    className="text-slate-400 transition-colors hover:text-cyan-400"
                                >
                                    {page.title}
                                </Link>
                            ))}
                        </nav>
                    )}
                    <p className="max-w-5xl text-xs leading-relaxed text-slate-500">
                        {riskDisclaimer}
                    </p>
                    <div className="flex flex-wrap items-center justify-between border-t border-white/5 pt-2 text-[11px] text-slate-600">
                        <span>
                            Hak Cipta © 2026 Sokotra. Ekosistem Edukasi &
                            Analisa Trading Terverifikasi.
                        </span>
                        <span className="text-slate-500">
                            Kemitraan Broker Berizin Resmi Bappebti
                        </span>
                    </div>
                </div>
            </footer>
        </div>
    );
}
