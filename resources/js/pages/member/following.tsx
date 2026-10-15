import { Head, Link } from '@inertiajs/react';
import { ChevronRight, Users } from 'lucide-react';
import Heading from '@/components/heading';
import { RankBadge } from '@/components/domain/rank-badge';
import { Button } from '@/components/ui/button';
import { formatPercent, formatR } from '@/lib/format';
import { leaderboard } from '@/routes';
import { index } from '@/routes/following';
import { show as traderProfile } from '@/routes/traders';
import type { RankSummary, TraderStatSummary } from '@/types';

type FollowedTrader = {
    id: number;
    name: string;
    slug: string;
    rank: RankSummary | null;
    stats: TraderStatSummary | null;
    followed_at: string | null;
};

export default function Following({ traders }: { traders: FollowedTrader[] }) {
    return (
        <>
            <Head title="Trader Diikuti" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Trader Diikuti"
                    description="Kamu mendapat notifikasi setiap kali mereka memposting atau menutup sinyal."
                />
                {traders.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed p-10 text-center">
                        <Users className="size-8 text-muted-foreground" />
                        <p className="font-medium">
                            Kamu belum mengikuti trader
                        </p>
                        <Button asChild>
                            <Link href={leaderboard()}>Buka leaderboard</Link>
                        </Button>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {traders.map((trader) => (
                            <li key={trader.id}>
                                <Link
                                    href={traderProfile(trader.slug)}
                                    className="flex items-center gap-4 p-4 hover:bg-accent/50"
                                >
                                    <span className="grid flex-1 gap-1">
                                        <span className="flex items-center gap-2 font-medium">
                                            {trader.name}
                                            {trader.rank && (
                                                <RankBadge
                                                    name={trader.rank.name}
                                                    color={trader.rank.color}
                                                />
                                            )}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {trader.stats
                                                ? `${trader.stats.closed_signals} sinyal · win rate ${formatPercent(trader.stats.win_rate)} · ${formatR(trader.stats.total_r)}`
                                                : 'Belum ada sinyal selesai'}
                                        </span>
                                    </span>
                                    <ChevronRight className="size-4 text-muted-foreground" />
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

Following.layout = {
    breadcrumbs: [{ title: 'Trader Diikuti', href: index() }],
};
