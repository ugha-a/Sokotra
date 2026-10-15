import { Head, Link } from '@inertiajs/react';
import { Radio, Trophy, Users } from 'lucide-react';
import Heading from '@/components/heading';
import { RiskDisclaimer } from '@/components/domain/risk-disclaimer';
import { SignalCard } from '@/components/domain/signal-card';
import { Button } from '@/components/ui/button';
import { dashboard, leaderboard } from '@/routes';
import { index as signals } from '@/routes/signals';
import type { SignalItem } from '@/types';

type Props = {
    followingCount: number;
    feed: SignalItem[];
    riskDisclaimer: string;
};

export default function Dashboard({
    followingCount,
    feed,
    riskDisclaimer,
}: Props) {
    return (
        <>
            <Head title="Beranda" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Beranda"
                    description={
                        followingCount > 0
                            ? `Sinyal terbaru dari ${followingCount} trader yang kamu ikuti.`
                            : 'Ikuti trader dari leaderboard untuk melihat sinyal mereka di sini.'
                    }
                />
                <RiskDisclaimer text={riskDisclaimer} />

                {feed.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed p-10 text-center">
                        <Users className="size-8 text-muted-foreground" />
                        <p className="font-medium">
                            Belum ada sinyal dari trader yang kamu ikuti
                        </p>
                        <div className="flex flex-wrap justify-center gap-2">
                            <Button asChild>
                                <Link href={leaderboard()}>
                                    <Trophy /> Cari trader
                                </Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link href={signals()}>
                                    <Radio /> Lihat semua sinyal
                                </Link>
                            </Button>
                        </div>
                    </div>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {feed.map((signal) => (
                            <SignalCard key={signal.id} signal={signal} />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Beranda', href: dashboard() }],
};
