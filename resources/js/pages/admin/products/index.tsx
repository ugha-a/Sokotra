import { Head, Link, router, usePage } from '@inertiajs/react';
import { Download, Package } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { ConfirmDialog } from '@/components/domain/confirm-dialog';
import { Pagination } from '@/components/domain/pagination';
import { ReasonDialog } from '@/components/domain/reason-dialog';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { cn } from '@/lib/utils';
import { approve, index, reject } from '@/routes/admin/products';
import type { Paginated, ProductCardData, TraderStatusFilter } from '@/types';

type Row = ProductCardData & {
    description: string;
    tradingview_url: string | null;
    screenshot_urls: string[];
    rejection_reason: string | null;
    file_url: string | null;
};

export default function AdminProducts({
    status,
    statuses,
    products,
}: {
    status: string;
    statuses: TraderStatusFilter[];
    products: Paginated<Row>;
}) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;

    return (
        <>
            <Head title="Produk" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Moderasi Produk"
                    description="Periksa file (bukan malware), screenshot asli, dan tidak ada klaim profit sebelum menayangkan."
                />
                <nav
                    aria-label="Status"
                    className="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1"
                >
                    {statuses.map((item) => (
                        <Link
                            key={item.value}
                            href={index({ query: { status: item.value } })}
                            className={cn(
                                'flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm whitespace-nowrap',
                                item.value === status
                                    ? 'border-brand bg-brand text-brand-foreground'
                                    : 'hover:bg-accent',
                            )}
                        >
                            {item.label}{' '}
                            <span className="font-mono text-xs tabular-nums opacity-80">
                                {item.count}
                            </span>
                        </Link>
                    ))}
                </nav>
                <InputError message={errors.product} />
                {products.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <Package className="size-8 text-muted-foreground" />
                        <p className="font-medium">
                            Tidak ada produk di status ini
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {products.data.map((product) => (
                            <article
                                key={product.id}
                                className="grid gap-3 rounded-xl border p-4"
                            >
                                <header className="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <h3 className="font-medium">
                                            {product.name}
                                        </h3>
                                        <p className="text-xs text-muted-foreground">
                                            {product.seller.name} ·{' '}
                                            {product.category} ·{' '}
                                            {product.platform_label} · v
                                            {product.version} ·{' '}
                                            {formatRupiah(product.price)}
                                        </p>
                                    </div>
                                    <StatusPill
                                        label={product.status_label}
                                        tone={statusTone(product.status)}
                                    />
                                </header>
                                <p className="text-sm font-medium">
                                    {product.summary}
                                </p>
                                <p className="max-h-40 overflow-y-auto text-sm whitespace-pre-line text-muted-foreground">
                                    {product.description}
                                </p>
                                {product.screenshot_urls.length > 0 && (
                                    <div className="grid grid-cols-3 gap-2 sm:grid-cols-5">
                                        {product.screenshot_urls.map((url) => (
                                            <a
                                                key={url}
                                                href={url}
                                                target="_blank"
                                                rel="noreferrer"
                                            >
                                                <img
                                                    src={url}
                                                    alt=""
                                                    className="aspect-video rounded-md border object-cover"
                                                />
                                            </a>
                                        ))}
                                    </div>
                                )}
                                <div className="flex flex-wrap items-center gap-2">
                                    {product.file_url && (
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            asChild
                                        >
                                            <a href={product.file_url}>
                                                <Download /> Download file
                                            </a>
                                        </Button>
                                    )}
                                    {product.tradingview_url && (
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            asChild
                                        >
                                            <a
                                                href={product.tradingview_url}
                                                target="_blank"
                                                rel="noreferrer"
                                            >
                                                Buka TradingView
                                            </a>
                                        </Button>
                                    )}
                                    {product.status === 'in_review' && (
                                        <>
                                            <ConfirmDialog
                                                trigger={
                                                    <Button size="sm">
                                                        Tayangkan
                                                    </Button>
                                                }
                                                title="Tayangkan produk?"
                                                description="Produk akan langsung bisa dibeli."
                                                confirmLabel="Tayangkan"
                                                onConfirm={() =>
                                                    router.visit(
                                                        approve(product.id),
                                                        {
                                                            preserveScroll: true,
                                                        },
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
                                                title="Tolak produk"
                                                description="Alasan dikirim ke penjual."
                                                confirmLabel="Tolak"
                                                action={reject(product.id)}
                                            />
                                        </>
                                    )}
                                </div>
                                {product.rejection_reason && (
                                    <p className="text-xs text-loss">
                                        Ditolak: {product.rejection_reason}
                                    </p>
                                )}
                            </article>
                        ))}
                    </div>
                )}
                <Pagination paginator={products} />
            </div>
        </>
    );
}

AdminProducts.layout = {
    breadcrumbs: [{ title: 'Produk', href: index() }],
};
