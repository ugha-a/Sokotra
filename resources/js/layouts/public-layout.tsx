import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import { Button } from '@/components/ui/button';
import { dashboard, home, leaderboard, login, register } from '@/routes';
import { index as brokers } from '@/routes/brokers';
import { index as marketplace } from '@/routes/marketplace';

/**
 * Layout for pages anyone can open (leaderboard, trader profiles, content pages).
 */
export default function PublicLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { auth, riskDisclaimer } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-background">
            <header className="border-b">
                <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
                    <Link href={home()} className="flex items-center gap-2">
                        <AppLogo />
                    </Link>
                    <nav className="flex items-center gap-2">
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
            <footer className="border-t">
                <p className="mx-auto max-w-6xl px-4 py-6 text-xs text-muted-foreground">
                    {riskDisclaimer}
                </p>
            </footer>
        </div>
    );
}
