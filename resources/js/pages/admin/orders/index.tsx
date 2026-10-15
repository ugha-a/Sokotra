import { Head, router } from '@inertiajs/react';
import { ExternalLink } from 'lucide-react';
import Heading from '@/components/heading';
import { ConfirmDialog } from '@/components/domain/confirm-dialog';
import { Pagination } from '@/components/domain/pagination';
import { ReasonDialog } from '@/components/domain/reason-dialog';
import { StatTile } from '@/components/domain/stat-tile';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { formatDate, formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { index, refund } from '@/routes/admin/orders';
import { confirm, reject } from '@/routes/admin/payments';
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
    payment?: {
        id: number;
        gateway: string;
        gateway_reference: string;
        status: string;
        has_proof: boolean;
        sender_name: string | null;
        sender_bank: string | null;
        proof_url: string | null;
    } | null;
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
                    description="Verifikasi pembayaran transfer bank dan kelola refund pesanan."
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
                                {order.payment && (
                                    <span className="block text-xs text-muted-foreground">
                                        Ref: {order.payment.gateway_reference}
                                        {order.payment.sender_name &&
                                            ` · Pengirim: ${order.payment.sender_name}`}
                                        {order.payment.sender_bank &&
                                            ` (${order.payment.sender_bank})`}
                                    </span>
                                )}
                                {order.refund_reason && (
                                    <span className="block text-xs text-loss">
                                        Refund: {order.refund_reason}
                                    </span>
                                )}
                            </span>
                            <span className="flex flex-wrap items-center gap-2">
                                <StatusPill
                                    label={order.status_label}
                                    tone={statusTone(order.status)}
                                />
                                {order.status === 'pending_payment' &&
                                    order.payment && (
                                        <>
                                            {order.payment.has_proof &&
                                                order.payment.proof_url && (
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        asChild
                                                    >
                                                        <a
                                                            href={
                                                                order.payment
                                                                    .proof_url
                                                            }
                                                            target="_blank"
                                                            rel="noreferrer"
                                                        >
                                                            <ExternalLink className="size-3.5" />
                                                            Bukti
                                                        </a>
                                                    </Button>
                                                )}
                                            <ConfirmDialog
                                                trigger={
                                                    <Button size="sm">
                                                        Konfirmasi
                                                    </Button>
                                                }
                                                title="Konfirmasi Pembayaran?"
                                                description="Pastikan dana telah masuk ke rekening tujuan admin. Setelah dikonfirmasi, pembeli dapat langsung mengunduh produk."
                                                confirmLabel="Konfirmasi Pembayaran"
                                                onConfirm={() =>
                                                    router.post(
                                                        confirm.url(
                                                            order.payment!.id,
                                                        ),
                                                    )
                                                }
                                            />
                                            <ReasonDialog
                                                trigger={
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                    >
                                                        Tolak
                                                    </Button>
                                                }
                                                title="Tolak Pembayaran?"
                                                description="Pembayaran akan ditandai gagal dan pesanan dibatalkan. Berikan alasan penolakan."
                                                confirmLabel="Tolak Pembayaran"
                                                action={reject(
                                                    order.payment.id,
                                                )}
                                            />
                                        </>
                                    )}
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
