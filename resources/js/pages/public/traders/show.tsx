import { Head, Link } from '@inertiajs/react';
import { BadgeCheck, Crown, Star } from 'lucide-react';
import { EquityCurve } from '@/components/domain/equity-curve';
import { FollowButton } from '@/components/domain/follow-button';
import { Pagination } from '@/components/domain/pagination';
import { RankBadge } from '@/components/domain/rank-badge';
import { SignalCard } from '@/components/domain/signal-card';
import { StatsGrid } from '@/components/domain/stats-grid';
import { Button } from '@/components/ui/button';
import { formatCompact, formatRupiah } from '@/lib/format';
import { leaderboard } from '@/routes';
import { create as subscribe } from '@/routes/subscriptions';
import type { EquityPoint } from '@/lib/equity-curve';
import type {
    Paginated,
    PublicTrader,
    SignalItem,
    StatsByPeriod,
} from '@/types';

type Props = {
    trader: PublicTrader;
    stats: StatsByPeriod;
    windowDays: number;
    equityCurve: EquityPoint[];
    signals: Paginated<SignalItem>;
    isFollowing: boolean;
    isOwnProfile: boolean;
    plan: { monthly_price: number; description: string | null } | null;
    isSubscribed: boolean;
};

export default function TraderProfile({
    trader,
    stats,
    windowDays,
    equityCurve,
    signals,
    isFollowing,
    isOwnProfile,
    plan,
    isSubscribed,
}: Props) {
    return (
        <>
            <Head title={`Profil Analis ${trader.name} - Sotra`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* eToro & Darwinex Benchmark: Verified Trader Header Card */}
                <header className="rounded-xl border border-border bg-card p-5">
                    <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div className="flex items-start gap-4">
                            <div className="flex size-14 shrink-0 items-center justify-center rounded-xl border border-border bg-muted font-mono text-xl font-bold text-foreground">
                                {trader.name.charAt(0).toUpperCase()}
                            </div>
                            <div className="grid gap-1.5">
                                <div className="flex flex-wrap items-center gap-2">
                                    <h1 className="text-xl font-bold tracking-tight text-foreground">
                                        {trader.name}
                                    </h1>
                                    <BadgeCheck
                                        className="size-5 text-brand"
                                        aria-label="Identitas terverifikasi"
                                    />
                                    {trader.rank && (
                                        <RankBadge
                                            name={trader.rank.name}
                                            color={trader.rank.color}
                                        />
                                    )}
                                    {trader.is_featured && (
                                        <span className="inline-flex items-center gap-1 rounded-md border border-premium/30 bg-premium/10 px-2 py-0.5 text-xs font-semibold text-premium">
                                            <Star className="size-3.5 fill-premium" />{' '}
                                            Pilihan Sotra
                                        </span>
                                    )}
                                </div>
                                <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                    <span className="font-medium text-foreground">
                                        {trader.trading_style}
                                    </span>
                                    <span>·</span>
                                    <span>
                                        {trader.experience_years} tahun
                                        pengalaman
                                    </span>
                                    <span>·</span>
                                    <span className="font-mono text-foreground">
                                        {trader.main_instruments.join(', ')}
                                    </span>
                                    <span>·</span>
                                    <span>
                                        <strong className="font-mono text-foreground">
                                            {formatCompact(trader.followers)}
                                        </strong>{' '}
                                        follower
                                    </span>
                                </div>
                                {trader.bio && (
                                    <p className="max-w-2xl pt-1 text-xs leading-relaxed whitespace-pre-line text-muted-foreground">
                                        {trader.bio}
                                    </p>
                                )}
                            </div>
                        </div>

                        {!isOwnProfile && (
                            <div className="flex flex-wrap items-center gap-2 md:self-start">
                                <FollowButton
                                    traderId={trader.id}
                                    isFollowing={isFollowing}
                                />
                                {plan && (
                                    <Button
                                        variant={
                                            isSubscribed ? 'outline' : 'default'
                                        }
                                        size="sm"
                                        asChild
                                        className="h-9 gap-1.5 text-xs font-semibold shadow-xs"
                                    >
                                        <Link href={subscribe(trader.id)}>
                                            <Crown className="size-3.5" />
                                            {isSubscribed
                                                ? 'Perpanjang Langganan'
                                                : `Langganan ${formatRupiah(plan.monthly_price)}/bln`}
                                        </Link>
                                    </Button>
                                )}
                            </div>
                        )}
                    </div>
                </header>

                {/* Quantitative Performance Metrics (Darwinex Style) */}
                <StatsGrid
                    stats={stats}
                    windowDays={windowDays}
                    initial="all_time"
                />

                {/* Equity Curve Visualizer */}
                <EquityCurve points={equityCurve} />

                {/* Signals History Feed */}
                <section className="grid gap-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-base font-bold text-foreground">
                            Riwayat Sinyal & Eksekusi
                        </h2>
                        <span className="font-mono text-xs text-muted-foreground">
                            Total {signals.total} sinyal tercatat
                        </span>
                    </div>

                    {signals.data.length === 0 ? (
                        <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed border-border bg-card p-10 text-center text-sm text-muted-foreground">
                            <p className="font-medium text-foreground">
                                Belum ada sinyal yang dipublikasikan
                            </p>
                            <p className="text-xs">
                                Sinyal baru dari analis ini akan tampil di sini
                                saat dirilis.
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-4 lg:grid-cols-2">
                            {signals.data.map((signal) => (
                                <SignalCard key={signal.id} signal={signal} />
                            ))}
                        </div>
                    )}
                    <Pagination paginator={signals} />
                </section>
            </div>
        </>
    );
}

TraderProfile.layout = {
    breadcrumbs: [
        { title: 'Leaderboard', href: leaderboard() },
        { title: 'Profil Trader' },
    ],
};
