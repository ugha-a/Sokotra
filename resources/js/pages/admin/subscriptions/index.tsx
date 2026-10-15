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
import { confirm, reject } from '@/routes/admin/payments';
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
                    description="Verifikasi pembayaran transfer bank dan kelola refund langganan."
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
                                        : 'Belum aktif'}
                                </span>
                                {item.payment && (
                                    <span className="block text-xs text-muted-foreground">
                                        Ref: {item.payment.gateway_reference}
                                        {item.payment.sender_name &&
                                            ` · Pengirim: ${item.payment.sender_name}`}
                                        {item.payment.sender_bank &&
                                            ` (${item.payment.sender_bank})`}
                                    </span>
                                )}
                                {item.refund_reason && (
                                    <span className="block text-xs text-loss">
                                        Refund: {item.refund_reason}
                                    </span>
                                )}
                            </span>
                            <span className="flex flex-wrap items-center gap-2">
                                <StatusPill
                                    label={item.status_label}
                                    tone={statusTone(item.status)}
                                />
                                {item.status === 'pending_payment' &&
                                    item.payment && (
                                        <>
                                            {item.payment.has_proof &&
                                                item.payment.proof_url && (
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        asChild
                                                    >
                                                        <a
                                                            href={
                                                                item.payment
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
                                                description="Pastikan dana telah masuk ke rekening tujuan admin. Setelah dikonfirmasi, langganan akan langsung aktif."
                                                confirmLabel="Konfirmasi Pembayaran"
                                                onConfirm={() =>
                                                    router.post(
                                                        confirm.url(
                                                            item.payment!.id,
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
                                                description="Pembayaran akan ditandai gagal dan langganan dibatalkan. Berikan alasan penolakan."
                                                confirmLabel="Tolak Pembayaran"
                                                action={reject(item.payment.id)}
                                            />
                                        </>
                                    )}
                                {item.status === 'active' && (
                                    <ReasonDialog
                                        trigger={
                                            <Button size="sm" variant="outline">
                                                Refund
                                            </Button>
                                        }
                                        title="Refund langganan?"
                                        description="Pendapatan trader yang masih tertahan dikembalikan dan akses subscriber dicabut. Pengembalian dana ke pembeli dilakukan manual."
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
