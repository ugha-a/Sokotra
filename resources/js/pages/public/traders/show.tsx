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
            <Head title={trader.name} />
            <div className="grid gap-8">
                <header className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div className="grid gap-2">
                        <div className="flex flex-wrap items-center gap-2">
                            <h1 className="text-2xl font-semibold tracking-tight">
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
                                <span className="inline-flex items-center gap-1 text-xs text-premium">
                                    <Star className="size-3.5" /> Pilihan
                                    Sokotra
                                </span>
                            )}
                        </div>
                        <p className="text-sm text-muted-foreground">
                            {trader.trading_style} · {trader.experience_years}{' '}
                            tahun pengalaman ·{' '}
                            <span className="font-mono">
                                {trader.main_instruments.join(', ')}
                            </span>{' '}
                            · {formatCompact(trader.followers)} follower
                        </p>
                        <p className="max-w-2xl text-sm whitespace-pre-line">
                            {trader.bio}
                        </p>
                    </div>
                    {!isOwnProfile && (
                        <div className="flex flex-wrap gap-2">
                            <FollowButton
                                traderId={trader.id}
                                isFollowing={isFollowing}
                            />
                            {plan && (
                                <Button
                                    variant={
                                        isSubscribed ? 'outline' : 'default'
                                    }
                                    asChild
                                >
                                    <Link href={subscribe(trader.id)}>
                                        <Crown />
                                        {isSubscribed
                                            ? 'Perpanjang langganan'
                                            : `Langganan ${formatRupiah(plan.monthly_price)}/bln`}
                                    </Link>
                                </Button>
                            )}
                        </div>
                    )}
                </header>

                <StatsGrid
                    stats={stats}
                    windowDays={windowDays}
                    initial="all_time"
                />

                <EquityCurve points={equityCurve} />

                <section className="grid gap-4">
                    <h2 className="text-lg font-semibold">Riwayat sinyal</h2>
                    {signals.data.length === 0 ? (
                        <p className="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
                            Belum ada sinyal.
                        </p>
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
