import { Head, Link, router } from '@inertiajs/react';
import { Package, Plus, TriangleAlert } from 'lucide-react';
import Heading from '@/components/heading';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { create, edit, index, submit } from '@/routes/trader/products';
import type { ProductCardData } from '@/types';

type Row = ProductCardData & { rejection_reason: string | null };

export default function TraderProducts({
    canSell,
    blockReason,
    products,
    commissionPercent,
}: {
    canSell: boolean;
    blockReason: string | null;
    products: Row[];
    commissionPercent: number;
}) {
    return (
        <>
            <Head title="Produk Saya" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        title="Produk Saya"
                        description={`Indikator dan EA yang kamu jual. Komisi platform ${commissionPercent}%, pendapatan ditahan sementara untuk antisipasi refund.`}
                    />
                    {canSell && (
                        <Button asChild>
                            <Link href={create()}>
                                <Plus /> Produk baru
                            </Link>
                        </Button>
                    )}
                </div>
                {!canSell && (
                    <div className="flex gap-3 rounded-xl border border-pending/40 bg-pending-soft p-4 text-sm">
                        <TriangleAlert className="size-4 shrink-0 text-pending" />
                        <p>{blockReason}</p>
                    </div>
                )}
                {products.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <Package className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada produk</p>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {products.map((product) => (
                            <li
                                key={product.id}
                                className="flex flex-wrap items-center justify-between gap-3 p-4 text-sm"
                            >
                                <span className="grid gap-0.5">
                                    <Link
                                        href={edit(product.id)}
                                        className="font-medium hover:underline"
                                    >
                                        {product.name}
                                    </Link>
                                    <span className="text-xs text-muted-foreground">
                                        {product.platform_label} ·{' '}
                                        {formatRupiah(product.price)} ·{' '}
                                        {product.sales_count} terjual
                                    </span>
                                    {product.rejection_reason && (
                                        <span className="text-xs text-loss">
                                            Ditolak: {product.rejection_reason}
                                        </span>
                                    )}
                                </span>
                                <span className="flex items-center gap-2">
                                    <StatusPill
                                        label={product.status_label}
                                        tone={statusTone(product.status)}
                                    />
                                    {(product.status === 'draft' ||
                                        product.status === 'rejected') && (
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            onClick={() =>
                                                router.visit(
                                                    submit(product.id),
                                                    { preserveScroll: true },
                                                )
                                            }
                                        >
                                            Ajukan review
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

TraderProducts.layout = {
    breadcrumbs: [{ title: 'Produk Saya', href: index() }],
};
