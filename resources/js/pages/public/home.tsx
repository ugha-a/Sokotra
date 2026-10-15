import { Head, Link, usePage } from '@inertiajs/react';
import {
    BadgeCheck,
    Building2,
    LineChart,
    ShieldCheck,
    Trophy,
    Users,
} from 'lucide-react';
import { RankBadge } from '@/components/domain/rank-badge';
import { RiskDisclaimer } from '@/components/domain/risk-disclaimer';
import { Button } from '@/components/ui/button';
import { formatPercent, formatR } from '@/lib/format';
import { dashboard, leaderboard, register } from '@/routes';
import { index as brokers } from '@/routes/brokers';
import { show as traderProfile } from '@/routes/traders';
import type { LeaderboardRow } from '@/types';

type Props = {
    topTraders: LeaderboardRow[];
    stats: {
        traders: number;
        closed_signals: number;
        losing_signals: number;
        brokers: number;
    };
    riskDisclaimer: string;
};

const STEPS = [
    {
        icon: Trophy,
        title: 'Pilih trader dari track record',
        body: 'Setiap sinyal dikunci saat dipublish lalu dinilai otomatis dari harga pasar. Yang kalah tetap ditampilkan.',
    },
    {
        icon: Users,
        title: 'Ikuti dan pelajari analisanya',
        body: 'Dapatkan notifikasi saat trader memposting sinyal, lengkap dengan alasan analisanya.',
    },
    {
        icon: Building2,
        title: 'Trading di broker berizin',
        body: 'Buka akun di broker partner berizin Bappebti. Sokotra tidak pernah mengelola dana kamu.',
    },
];

export default function Home({ topTraders, stats, riskDisclaimer }: Props) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Belajar dari trader dengan track record terverifikasi" />
            <div className="grid gap-14">
                <section className="grid gap-6 py-6 md:py-12">
                    <h1 className="max-w-3xl text-3xl font-semibold tracking-tight md:text-5xl">
                        Belajar trading dari analis yang track record-nya
                        terbuka.
                    </h1>
                    <p className="max-w-2xl text-muted-foreground md:text-lg">
                        Sokotra mempertemukan trader pemula dengan analis
                        berpengalaman. Setiap sinyal dinilai otomatis dari harga
                        pasar, jadi kamu bisa menilai kinerja sebelum mengikuti.
                    </p>
                    <div className="flex flex-wrap gap-3">
                        <Button size="lg" asChild>
                            <Link href={auth.user ? dashboard() : register()}>
                                {auth.user ? 'Buka dashboard' : 'Daftar gratis'}
                            </Link>
                        </Button>
                        <Button size="lg" variant="outline" asChild>
                            <Link href={leaderboard()}>Lihat leaderboard</Link>
                        </Button>
                    </div>
                    <dl className="grid max-w-3xl grid-cols-2 gap-4 pt-4 md:grid-cols-4">
                        {[
                            ['Trader terverifikasi', stats.traders],
                            ['Sinyal dinilai', stats.closed_signals],
                            [
                                'Sinyal kalah (tetap tampil)',
                                stats.losing_signals,
                            ],
                            ['Broker berizin', stats.brokers],
                        ].map(([label, value]) => (
                            <div key={label} className="grid gap-0.5">
                                <dt className="text-xs text-muted-foreground">
                                    {label}
                                </dt>
                                <dd className="font-mono text-2xl font-semibold tabular-nums">
                                    {Number(value).toLocaleString('id-ID')}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </section>

                <section className="grid gap-4 md:grid-cols-3">
                    {STEPS.map(({ icon: Icon, title, body }) => (
                        <div
                            key={title}
                            className="grid content-start gap-2 rounded-xl border p-5"
                        >
                            <Icon className="size-5 text-brand" />
                            <h2 className="font-medium">{title}</h2>
                            <p className="text-sm text-muted-foreground">
                                {body}
                            </p>
                        </div>
                    ))}
                </section>

                <section className="grid gap-4">
                    <div className="flex items-end justify-between gap-3">
                        <h2 className="text-xl font-semibold">
                            Trader teratas
                        </h2>
                        <Link
                            href={leaderboard()}
                            className="text-sm text-brand hover:underline"
                        >
                            Leaderboard lengkap
                        </Link>
                    </div>
                    {topTraders.length === 0 ? (
                        <p className="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
                            Leaderboard terisi setelah trader menyelesaikan
                            cukup banyak sinyal.
                        </p>
                    ) : (
                        <div className="grid gap-4 md:grid-cols-3">
                            {topTraders.map((row) => (
                                <Link
                                    key={row.trader.slug}
                                    href={traderProfile(row.trader.slug)}
                                    className="grid gap-3 rounded-xl border bg-card p-5 hover:border-brand/50"
                                >
                                    <span className="flex items-center gap-2 font-medium">
                                        <BadgeCheck className="size-4 text-brand" />{' '}
                                        {row.trader.name}
                                        {row.trader.rank && (
                                            <RankBadge
                                                name={row.trader.rank.name}
                                                color={row.trader.rank.color}
                                            />
                                        )}
                                    </span>
                                    <dl className="grid grid-cols-3 gap-2 text-sm">
                                        <div>
                                            <dt className="text-xs text-muted-foreground">
                                                Win rate
                                            </dt>
                                            <dd className="font-mono tabular-nums">
                                                {formatPercent(
                                                    row.stats.win_rate,
                                                )}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-xs text-muted-foreground">
                                                Total R
                                            </dt>
                                            <dd className="font-mono tabular-nums">
                                                {formatR(row.stats.total_r)}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-xs text-muted-foreground">
                                                Sinyal
                                            </dt>
                                            <dd className="font-mono tabular-nums">
                                                {row.stats.closed_signals}
                                            </dd>
                                        </div>
                                    </dl>
                                </Link>
                            ))}
                        </div>
                    )}
                </section>

                <section className="grid gap-4 rounded-xl border p-6 md:grid-cols-[1fr_auto] md:items-center">
                    <div className="grid gap-1">
                        <h2 className="flex items-center gap-2 font-semibold">
                            <LineChart className="size-5 text-brand" /> Kamu
                            analis yang konsisten?
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Gabung sebagai trader: bangun track record, dapatkan
                            follower, gaji bulanan sesuai kinerja, dan jual
                            langganan atau indikator.
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href={auth.user ? dashboard() : register()}>
                            Jadi trader
                        </Link>
                    </Button>
                </section>

                <section className="grid gap-3">
                    <h2 className="flex items-center gap-2 font-semibold">
                        <ShieldCheck className="size-5 text-profit" /> Hanya
                        broker berizin
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        Dana trading tetap di broker partner berizin Bappebti.{' '}
                        <Link
                            href={brokers()}
                            className="text-brand hover:underline"
                        >
                            Lihat broker partner
                        </Link>
                        .
                    </p>
                    <RiskDisclaimer text={riskDisclaimer} />
                </section>
            </div>
        </>
    );
}
