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
 * Layout for pages anyone can open (landing, leaderboard, trader profiles, content pages).
 * Provides an authentic trading terminal feel with live market ticker and interactive utilities.
 */
export default function PublicLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { auth, riskDisclaimer, legalPages } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-background selection:bg-brand/20 selection:text-brand">
            {/* Live Market Ticker Tape */}
            <MarketTickerTape />

            {/* Sticky Trading Header */}
            <header className="sticky top-0 z-40 border-b border-border/60 bg-background/85 backdrop-blur-md">
                <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
                    <Link href={home()} className="flex items-center gap-2">
                        <AppLogo />
                    </Link>
                    <nav className="flex items-center gap-1 sm:gap-2">
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={leaderboard()}>Leaderboard</Link>
                        </Button>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={marketplace()}>Marketplace</Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            asChild
                            className="hidden sm:inline-flex"
                        >
                            <Link href={brokers()}>Broker</Link>
                        </Button>

                        {/* Interactive Risk & Lot Calculator */}
                        <TradingCalculator />

                        {auth.user ? (
                            <Button size="sm" asChild>
                                <Link href={dashboard()}>Dashboard</Link>
                            </Button>
                        ) : (
                            <>
                                <Button variant="ghost" size="sm" asChild>
                                    <Link href={login()}>Masuk</Link>
                                </Button>
                                <Button size="sm" asChild>
                                    <Link href={register()}>Daftar</Link>
                                </Button>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-6 md:py-10">
                {children}
            </main>

            <footer className="border-t border-border/60 bg-card/40">
                <div className="mx-auto grid max-w-6xl gap-3 px-4 py-6">
                    {legalPages.length > 0 && (
                        <nav
                            aria-label="Informasi"
                            className="flex flex-wrap gap-x-4 gap-y-1 text-xs"
                        >
                            {legalPages.map((page) => (
                                <Link
                                    key={page.slug}
                                    href={showPage(page.slug)}
                                    className="text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    {page.title}
                                </Link>
                            ))}
                        </nav>
                    )}
                    <p className="text-xs leading-relaxed text-muted-foreground">
                        {riskDisclaimer}
                    </p>
                </div>
            </footer>
        </div>
    );
}
