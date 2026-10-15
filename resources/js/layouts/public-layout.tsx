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
 * Utilitarian layout for public-facing trading portal.
 * Clean, high data density, matte financial canvas inspired by TradingView and eToro.
 */
export default function PublicLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { auth, riskDisclaimer, legalPages } = usePage().props;

    return (
        <div className="dark flex min-h-screen flex-col bg-[#131722] text-[#d1d4dc] antialiased selection:bg-[#2962ff]/30 selection:text-white">
            {/* Real-time Market Ticker Tape */}
            <MarketTickerTape />

            {/* Clean Financial Navigation Header */}
            <header className="sticky top-0 z-40 border-b border-[#2a2e39] bg-[#131722]">
                <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2.5 sm:px-6">
                    <Link
                        href={home()}
                        className="flex items-center gap-2 focus-visible:ring-1 focus-visible:ring-[#2962ff] focus-visible:outline-none"
                    >
                        <AppLogo />
                    </Link>
                    <nav className="flex items-center gap-1 text-sm font-medium sm:gap-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            asChild
                            className="h-8 text-[#d1d4dc] hover:bg-[#1e222d] hover:text-white"
                        >
                            <Link href={leaderboard()}>Leaderboard</Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            asChild
                            className="h-8 text-[#d1d4dc] hover:bg-[#1e222d] hover:text-white"
                        >
                            <Link href={marketplace()}>Marketplace</Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            asChild
                            className="hidden h-8 text-[#d1d4dc] hover:bg-[#1e222d] hover:text-white sm:inline-flex"
                        >
                            <Link href={brokers()}>Broker Legal</Link>
                        </Button>

                        {/* Interactive Risk & Lot Calculator Trigger */}
                        <TradingCalculator />

                        {auth.user ? (
                            <Button
                                size="sm"
                                asChild
                                className="h-8 bg-[#2962ff] font-medium text-white hover:bg-[#1e53e5]"
                            >
                                <Link href={dashboard()}>Terminal Saya</Link>
                            </Button>
                        ) : (
                            <div className="flex items-center gap-1.5 pl-1">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    asChild
                                    className="h-8 text-[#d1d4dc] hover:bg-[#1e222d] hover:text-white"
                                >
                                    <Link href={login()}>Masuk</Link>
                                </Button>
                                <Button
                                    size="sm"
                                    asChild
                                    className="h-8 bg-[#2962ff] font-medium text-white hover:bg-[#1e53e5]"
                                >
                                    <Link href={register()}>Daftar</Link>
                                </Button>
                            </div>
                        )}
                    </nav>
                </div>
            </header>

            <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6">
                {children}
            </main>

            {/* Utilitarian Footer */}
            <footer className="border-t border-[#2a2e39] bg-[#131722] text-[#787b86]">
                <div className="mx-auto grid max-w-7xl gap-4 px-4 py-6 sm:px-6">
                    {legalPages.length > 0 && (
                        <nav
                            aria-label="Informasi Legal"
                            className="flex flex-wrap gap-x-5 gap-y-2 text-xs font-medium"
                        >
                            {legalPages.map((page) => (
                                <Link
                                    key={page.slug}
                                    href={showPage(page.slug)}
                                    className="transition-colors hover:text-[#d1d4dc]"
                                >
                                    {page.title}
                                </Link>
                            ))}
                        </nav>
                    )}
                    <p className="max-w-5xl text-xs leading-relaxed text-[#787b86]">
                        {riskDisclaimer}
                    </p>
                    <div className="flex flex-wrap items-center justify-between border-t border-[#2a2e39] pt-3 text-xs text-[#787b86]">
                        <span>
                            Hak Cipta © 2026 Sokotra. Ekosistem Edukasi &
                            Analisa Trading Terbuka.
                        </span>
                        <span>Kemitraan Broker Berizin Resmi Bappebti</span>
                    </div>
                </div>
            </footer>
        </div>
    );
}
