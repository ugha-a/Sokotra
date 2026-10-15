import { Head, Link, useForm } from '@inertiajs/react';
import { Copy, Download, ShoppingBag, Star } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { formatDate, formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { index as marketplace } from '@/routes/marketplace';
import { download, index, review } from '@/routes/purchases';
import type { ProductCardData } from '@/types';

type OrderRow = {
    id: number;
    product: ProductCardData;
    price_paid: number;
    status: string;
    status_label: string;
    license_key: string | null;
    paid_at: string | null;
    review: { rating: number; comment: string | null } | null;
};

function ReviewForm({ orderId }: { orderId: number }) {
    const form = useForm({ rating: 0, comment: '' });
    const errors = form.errors as Record<string, string | undefined>;

    return (
        <form
            className="grid gap-2"
            onSubmit={(event) => {
                event.preventDefault();
                form.submit(review(orderId), { preserveScroll: true });
            }}
        >
            <div className="flex gap-1" role="radiogroup" aria-label="Rating">
                {[1, 2, 3, 4, 5].map((value) => (
                    <button
                        key={value}
                        type="button"
                        role="radio"
                        aria-checked={form.data.rating === value}
                        aria-label={`${value} bintang`}
                        onClick={() => form.setData('rating', value)}
                    >
                        <Star
                            className={
                                value <= form.data.rating
                                    ? 'size-5 fill-premium text-premium'
                                    : 'size-5 text-muted-foreground'
                            }
                        />
                    </button>
                ))}
            </div>
            <Textarea
                rows={2}
                placeholder="Ceritakan pengalamanmu (opsional)"
                value={form.data.comment}
                onChange={(e) => form.setData('comment', e.target.value)}
            />
            <InputError message={errors.rating ?? errors.review} />
            <Button
                type="submit"
                size="sm"
                variant="outline"
                className="justify-self-start"
                disabled={form.processing || form.data.rating === 0}
            >
                Kirim ulasan
            </Button>
        </form>
    );
}

export default function Purchases({ orders }: { orders: OrderRow[] }) {
    const [copied, setCopied] = useState<number | null>(null);

    return (
        <>
            <Head title="Pembelian" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Pembelian"
                    description="Download produk dan license key kamu. Link download berlaku 10 menit dan dibuat ulang setiap kali diklik."
                />
                {orders.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed p-10 text-center">
                        <ShoppingBag className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada pembelian</p>
                        <Button asChild>
                            <Link href={marketplace()}>Buka marketplace</Link>
                        </Button>
                    </div>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {orders.map((order) => (
                            <article
                                key={order.id}
                                className="grid content-start gap-3 rounded-xl border p-4"
                            >
                                <header className="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 className="font-medium">
                                            {order.product.name}
                                        </h3>
                                        <p className="text-xs text-muted-foreground">
                                            {order.product.platform_label} · v
                                            {order.product.version} ·{' '}
                                            {formatRupiah(order.price_paid)}
                                            {order.paid_at &&
                                                ` · ${formatDate(order.paid_at)}`}
                                        </p>
                                    </div>
                                    <StatusPill
                                        label={order.status_label}
                                        tone={statusTone(order.status)}
                                    />
                                </header>
                                {order.license_key && (
                                    <div className="flex items-center justify-between gap-2 rounded-lg bg-muted/50 px-3 py-2">
                                        <code className="font-mono text-sm">
                                            {order.license_key}
                                        </code>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            onClick={() => {
                                                void navigator.clipboard.writeText(
                                                    order.license_key ?? '',
                                                );
                                                setCopied(order.id);
                                            }}
                                        >
                                            <Copy />{' '}
                                            {copied === order.id
                                                ? 'Tersalin'
                                                : 'Salin'}
                                        </Button>
                                    </div>
                                )}
                                {order.status === 'paid' && (
                                    <Button
                                        asChild
                                        className="justify-self-start"
                                    >
                                        <a href={download(order.id).url}>
                                            <Download />{' '}
                                            {order.product.platform ===
                                            'tradingview'
                                                ? 'Buka di TradingView'
                                                : 'Download'}
                                        </a>
                                    </Button>
                                )}
                                {order.status === 'paid' &&
                                    (order.review ? (
                                        <p className="flex items-center gap-1 text-sm text-muted-foreground">
                                            <Star className="size-4 fill-premium text-premium" />{' '}
                                            Ulasanmu: {order.review.rating}/5
                                        </p>
                                    ) : (
                                        <ReviewForm orderId={order.id} />
                                    ))}
                            </article>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

Purchases.layout = {
    breadcrumbs: [{ title: 'Pembelian', href: index() }],
};
