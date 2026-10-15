import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Pagination } from '@/components/domain/pagination';
import { ReasonDialog } from '@/components/domain/reason-dialog';
import { StatTile } from '@/components/domain/stat-tile';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { formatDate, formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { index, refund } from '@/routes/admin/subscriptions';
import type { Paginated } from '@/types';

type Row = {
    id: number;
    subscriber: { name: string; email: string };
    trader: string;
    months: number;
    price_paid: number;
    platform_fee: number;
    trader_income: number;
    status: string;
    status_label: string;
    starts_at: string | null;
    ends_at: string | null;
    refund_reason: string | null;
};

export default function AdminSubscriptions({
    subscriptions,
    revenue,
}: {
    subscriptions: Paginated<Row>;
    revenue: { platform_fee: number; active: number };
}) {
    return (
        <>
            <Head title="Subscription" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Subscription"
                    description="Refund hanya bisa selama pendapatan trader masih tertahan."
                />
                <div className="grid gap-3 sm:grid-cols-2">
                    <StatTile
                        label="Fee platform (langganan dibayar)"
                        value={formatRupiah(revenue.platform_fee)}
                    />
                    <StatTile
                        label="Langganan aktif"
                        value={revenue.active.toLocaleString('id-ID')}
                    />
                </div>
                <ul className="divide-y rounded-xl border">
                    {subscriptions.data.length === 0 && (
                        <li className="p-6 text-center text-sm text-muted-foreground">
                            Belum ada langganan.
                        </li>
                    )}
                    {subscriptions.data.map((item) => (
                        <li
                            key={item.id}
                            className="flex flex-wrap items-center justify-between gap-3 p-4 text-sm"
                        >
                            <span>
                                <span className="block font-medium">
                                    {item.subscriber.name} → {item.trader}
                                </span>
                                <span className="text-xs text-muted-foreground">
                                    {item.months} bulan ·{' '}
                                    {formatRupiah(item.price_paid)} (fee{' '}
                                    {formatRupiah(item.platform_fee)}) ·{' '}
                                    {item.starts_at
                                        ? formatDate(item.starts_at)
                                        : '—'}
                                </span>
                                {item.refund_reason && (
                                    <span className="block text-xs text-loss">
                                        Refund: {item.refund_reason}
                                    </span>
                                )}
                            </span>
                            <span className="flex items-center gap-2">
                                <StatusPill
                                    label={item.status_label}
                                    tone={statusTone(item.status)}
                                />
                                {item.status === 'active' && (
                                    <ReasonDialog
                                        trigger={
                                            <Button size="sm" variant="outline">
                                                Refund
                                            </Button>
                                        }
                                        title="Refund langganan?"
                                        description="Pendapatan trader yang masih tertahan dikembalikan dan akses subscriber dicabut. Pengembalian dana ke pembeli dilakukan di dashboard payment gateway."
                                        confirmLabel="Refund"
                                        action={refund(item.id)}
                                    />
                                )}
                            </span>
                        </li>
                    ))}
                </ul>
                <Pagination paginator={subscriptions} />
            </div>
        </>
    );
}

AdminSubscriptions.layout = {
    breadcrumbs: [{ title: 'Subscription', href: index() }],
};
