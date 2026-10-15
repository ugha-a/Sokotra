import { Head, Link } from '@inertiajs/react';
import { Check, SquarePen, X } from 'lucide-react';
import Heading from '@/components/heading';
import { RankBadge } from '@/components/domain/rank-badge';
import { StatsGrid } from '@/components/domain/stats-grid';
import { StreakIndicator } from '@/components/domain/streak-indicator';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes/trader';
import { create } from '@/routes/trader/signals';
import { show as traderProfile } from '@/routes/traders';
import type { RankRequirement, RankSummary, StatsByPeriod } from '@/types';

type Props = {
    profile: {
        name: string;
        slug: string;
        status: string;
        status_label: string;
    };
    rank: RankSummary | null;
    stats: StatsByPeriod;
    windowDays: number;
    streak: {
        current: number;
        best_this_month: number;
        required: number | null;
    };
    nextRank: {
        name: string;
        color: string;
        requirements: RankRequirement[];
    } | null;
    validFollowers: number;
};

export default function TraderDashboard({
    profile,
    rank,
    stats,
    windowDays,
    streak,
    nextRank,
    validFollowers,
}: Props) {
    return (
        <>
            <Head title="Dashboard Trader" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        title={`Halo, ${profile.name}`}
                        description={`${validFollowers} follower valid · status ${profile.status_label.toLowerCase()}`}
                    />
                    <div className="flex flex-wrap gap-2">
                        <Button variant="outline" asChild>
                            <Link href={traderProfile(profile.slug)}>
                                Profil publik
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={create()}>
                                <SquarePen /> Posting Sinyal
                            </Link>
                        </Button>
                    </div>
                </div>

                <StatsGrid
                    stats={stats}
                    windowDays={windowDays}
                    initial="month"
                />

                <div className="grid gap-4 lg:grid-cols-2">
                    <StreakIndicator
                        current={streak.current}
                        bestThisMonth={streak.best_this_month}
                        required={streak.required}
                    />

                    <section className="grid content-start gap-3 rounded-xl border bg-card p-4">
                        <div className="flex flex-wrap items-center gap-2 text-sm font-medium">
                            Rank{' '}
                            {rank && (
                                <RankBadge
                                    name={rank.name}
                                    color={rank.color}
                                />
                            )}
                            {nextRank && (
                                <>
                                    <span className="text-muted-foreground">
                                        →
                                    </span>
                                    <RankBadge
                                        name={nextRank.name}
                                        color={nextRank.color}
                                    />
                                </>
                            )}
                        </div>
                        {nextRank ? (
                            <ul className="grid gap-2 text-sm">
                                {nextRank.requirements.map((req) => (
                                    <li
                                        key={req.key}
                                        className="flex items-center justify-between gap-3"
                                    >
                                        <span className="flex items-center gap-2">
                                            {req.met ? (
                                                <Check className="size-4 text-profit" />
                                            ) : (
                                                <X className="size-4 text-muted-foreground" />
                                            )}
                                            {req.label}
                                        </span>
                                        <span
                                            className={cn(
                                                'font-mono tabular-nums',
                                                req.met
                                                    ? 'text-foreground'
                                                    : 'text-muted-foreground',
                                            )}
                                        >
                                            {req.current.toLocaleString(
                                                'id-ID',
                                            )}{' '}
                                            /{' '}
                                            {req.required.toLocaleString(
                                                'id-ID',
                                            )}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                Kamu sudah di rank tertinggi.
                            </p>
                        )}
                        <p className="text-xs text-muted-foreground">
                            Rank dihitung ulang setiap Senin dari statistik{' '}
                            {windowDays} hari terakhir.
                        </p>
                    </section>
                </div>
            </div>
        </>
    );
}

TraderDashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard Trader', href: dashboard() }],
};
