import { Head, Link } from '@inertiajs/react';
import { Gem } from 'lucide-react';
import Heading from '@/components/heading';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { formatDate, formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { leaderboard } from '@/routes';
import { create, index } from '@/routes/subscriptions';

type Row = {
    id: number;
    trader: { name: string; slug: string | null; profile_id: number | null };
    months: number;
    price_paid: number;
    status: string;
    status_label: string;
    starts_at: string | null;
    ends_at: string | null;
};

export default function Subscriptions({
    subscriptions,
}: {
    subscriptions: Row[];
}) {
    return (
        <>
            <Head title="Langganan" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Langganan"
                    description="Channel premium yang kamu ikuti."
                />
                {subscriptions.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed p-10 text-center">
                        <Gem className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada langganan</p>
                        <Button asChild>
                            <Link href={leaderboard()}>Cari trader</Link>
                        </Button>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {subscriptions.map((item) => (
                            <li
                                key={item.id}
                                className="flex flex-wrap items-center justify-between gap-3 p-4 text-sm"
                            >
                                <span>
                                    <span className="block font-medium">
                                        {item.trader.name}
                                    </span>
                                    <span className="text-xs text-muted-foreground">
                                        {item.months} bulan ·{' '}
                                        {formatRupiah(item.price_paid)} ·{' '}
                                        {item.starts_at
                                            ? formatDate(item.starts_at)
                                            : '—'}{' '}
                                        →{' '}
                                        {item.ends_at
                                            ? formatDate(item.ends_at)
                                            : '—'}
                                    </span>
                                </span>
                                <span className="flex items-center gap-2">
                                    <StatusPill
                                        label={item.status_label}
                                        tone={statusTone(item.status)}
                                    />
                                    {item.trader.profile_id &&
                                        item.status !== 'refunded' && (
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                asChild
                                            >
                                                <Link
                                                    href={create(
                                                        item.trader.profile_id,
                                                    )}
                                                >
                                                    Perpanjang
                                                </Link>
                                            </Button>
                                        )}
                                </span>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

Subscriptions.layout = {
    breadcrumbs: [{ title: 'Langganan', href: index() }],
};
