import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Pagination } from '@/components/domain/pagination';
import { ReasonDialog } from '@/components/domain/reason-dialog';
import { StatTile } from '@/components/domain/stat-tile';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { formatDate, formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { index, refund } from '@/routes/admin/orders';
import type { Paginated } from '@/types';

type Row = {
    id: number;
    buyer: { name: string; email: string };
    product: string;
    seller: string;
    price_paid: number;
    commission: number;
    status: string;
    status_label: string;
    paid_at: string | null;
    refund_reason: string | null;
};

export default function AdminOrders({
    orders,
    revenue,
}: {
    orders: Paginated<Row>;
    revenue: { commission: number; orders: number };
}) {
    return (
        <>
            <Head title="Pesanan" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Pesanan Marketplace"
                    description="Refund hanya bisa selama pendapatan penjual masih tertahan."
                />
                <div className="grid gap-3 sm:grid-cols-2">
                    <StatTile
                        label="Komisi platform"
                        value={formatRupiah(revenue.commission)}
                    />
                    <StatTile
                        label="Pesanan dibayar"
                        value={revenue.orders.toLocaleString('id-ID')}
                    />
                </div>
                <ul className="divide-y rounded-xl border">
                    {orders.data.length === 0 && (
                        <li className="p-6 text-center text-sm text-muted-foreground">
                            Belum ada pesanan.
                        </li>
                    )}
                    {orders.data.map((order) => (
                        <li
                            key={order.id}
                            className="flex flex-wrap items-center justify-between gap-3 p-4 text-sm"
                        >
                            <span>
                                <span className="block font-medium">
                                    {order.product}
                                </span>
                                <span className="text-xs text-muted-foreground">
                                    {order.buyer.name} membeli dari{' '}
                                    {order.seller} ·{' '}
                                    {formatRupiah(order.price_paid)} (komisi{' '}
                                    {formatRupiah(order.commission)})
                                    {order.paid_at &&
                                        ` · ${formatDate(order.paid_at)}`}
                                </span>
                                {order.refund_reason && (
                                    <span className="block text-xs text-loss">
                                        Refund: {order.refund_reason}
                                    </span>
                                )}
                            </span>
                            <span className="flex items-center gap-2">
                                <StatusPill
                                    label={order.status_label}
                                    tone={statusTone(order.status)}
                                />
                                {order.status === 'paid' && (
                                    <ReasonDialog
                                        trigger={
                                            <Button size="sm" variant="outline">
                                                Refund
                                            </Button>
                                        }
                                        title="Refund pesanan?"
                                        description="Pendapatan penjual yang masih tertahan dikembalikan dan akses download dicabut."
                                        confirmLabel="Refund"
                                        action={refund(order.id)}
                                    />
                                )}
                            </span>
                        </li>
                    ))}
                </ul>
                <Pagination paginator={orders} />
            </div>
        </>
    );
}

AdminOrders.layout = {
    breadcrumbs: [{ title: 'Pesanan', href: index() }],
};
