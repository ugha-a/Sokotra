import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Download, Star } from 'lucide-react';
import InputError from '@/components/input-error';
import { RankBadge } from '@/components/domain/rank-badge';
import { RiskDisclaimer } from '@/components/domain/risk-disclaimer';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDate, formatRupiah } from '@/lib/format';
import { login } from '@/routes';
import { index as marketplaceIndex } from '@/routes/marketplace';
import { store as buy } from '@/routes/orders';
import { index as purchases } from '@/routes/purchases';
import { show as traderProfile } from '@/routes/traders';
import type { ProductDetail, ProductReviewItem } from '@/types';

type Props = {
    product: ProductDetail;
    reviews: ProductReviewItem[];
    owned: boolean;
    isOwnProduct: boolean;
    riskDisclaimer: string;
};

export default function ProductPage({
    product,
    reviews,
    owned,
    isOwnProduct,
    riskDisclaimer,
}: Props) {
    const { auth } = usePage().props;
    const form = useForm({ accept_risk: false });
    const errors = form.errors as Record<string, string | undefined>;

    return (
        <>
            <Head title={product.name} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="grid gap-8 lg:grid-cols-[1fr_22rem]">
                    <article className="grid content-start gap-6">
                        <header className="grid gap-2">
                            <p className="text-sm text-muted-foreground">
                                {product.category} · {product.platform_label} ·
                                versi {product.version}
                            </p>
                            <h1 className="text-2xl font-semibold tracking-tight">
                                {product.name}
                            </h1>
                            <p className="text-muted-foreground">
                                {product.summary}
                            </p>
                            <div className="flex items-center gap-2 text-sm">
                                oleh{' '}
                                {product.seller.slug ? (
                                    <Link
                                        href={traderProfile(
                                            product.seller.slug,
                                        )}
                                        className="font-medium hover:underline"
                                    >
                                        {product.seller.name}
                                    </Link>
                                ) : (
                                    product.seller.name
                                )}
                                {product.seller.rank && (
                                    <RankBadge
                                        name={product.seller.rank.name}
                                        color={product.seller.rank.color}
                                    />
                                )}
                            </div>
                        </header>
                        {product.screenshot_urls.length > 0 && (
                            <div className="grid gap-3 sm:grid-cols-2">
                                {product.screenshot_urls.map((url) => (
                                    <a
                                        key={url}
                                        href={url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="overflow-hidden rounded-lg border"
                                    >
                                        <img
                                            src={url}
                                            alt={`Screenshot ${product.name}`}
                                            className="aspect-video w-full object-cover"
                                            loading="lazy"
                                        />
                                    </a>
                                ))}
                            </div>
                        )}
                        <section className="grid gap-2">
                            <h2 className="font-medium">Deskripsi</h2>
                            <p className="text-sm whitespace-pre-line">
                                {product.description}
                            </p>
                        </section>
                        <section className="grid gap-3">
                            <h2 className="flex items-center gap-2 font-medium">
                                Ulasan pembeli
                                <span className="flex items-center gap-1 text-sm font-normal text-muted-foreground">
                                    <Star className="size-4 fill-premium text-premium" />{' '}
                                    {product.rating_count > 0
                                        ? product.rating_avg.toLocaleString(
                                              'id-ID',
                                          )
                                        : '—'}{' '}
                                    ({product.rating_count})
                                </span>
                            </h2>
                            {reviews.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    Belum ada ulasan.
                                </p>
                            ) : (
                                <ul className="divide-y rounded-xl border">
                                    {reviews.map((review) => (
                                        <li
                                            key={review.id}
                                            className="grid gap-1 p-4 text-sm"
                                        >
                                            <span className="flex items-center gap-2">
                                                <span className="font-medium">
                                                    {review.name}
                                                </span>
                                                <span
                                                    className="flex"
                                                    aria-label={`${review.rating} dari 5`}
                                                >
                                                    {Array.from(
                                                        { length: 5 },
                                                        (_, i) => (
                                                            <Star
                                                                key={i}
                                                                className={
                                                                    i <
                                                                    review.rating
                                                                        ? 'size-3.5 fill-premium text-premium'
                                                                        : 'size-3.5 text-muted-foreground'
                                                                }
                                                            />
                                                        ),
                                                    )}
                                                </span>
                                                {review.created_at && (
                                                    <span className="text-xs text-muted-foreground">
                                                        {formatDate(
                                                            review.created_at,
                                                        )}
                                                    </span>
                                                )}
                                            </span>
                                            {review.comment && (
                                                <p className="text-muted-foreground">
                                                    {review.comment}
                                                </p>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </section>
                    </article>

                    <aside className="grid content-start gap-4 rounded-xl border p-4 lg:sticky lg:top-6">
                        <span className="font-mono text-2xl font-semibold tabular-nums">
                            {product.price === 0
                                ? 'Gratis'
                                : formatRupiah(product.price)}
                        </span>
                        <span className="text-sm text-muted-foreground">
                            {product.sales_count} terjual · lisensi untuk 1 akun
                        </span>
                        {owned ? (
                            <Button asChild>
                                <Link href={purchases()}>
                                    <Download /> Lihat di Pembelian Saya
                                </Link>
                            </Button>
                        ) : isOwnProduct ? (
                            <p className="text-sm text-muted-foreground">
                                Ini produk kamu.
                            </p>
                        ) : !auth.user ? (
                            <Button onClick={() => router.visit(login())}>
                                Masuk untuk membeli
                            </Button>
                        ) : (
                            <form
                                className="grid gap-3"
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    form.submit(buy(product.id));
                                }}
                            >
                                <RiskDisclaimer
                                    text={riskDisclaimer}
                                    variant="inline"
                                />
                                <div className="flex items-start gap-2">
                                    <Checkbox
                                        id="accept_risk"
                                        checked={form.data.accept_risk}
                                        onCheckedChange={(checked) =>
                                            form.setData(
                                                'accept_risk',
                                                checked === true,
                                            )
                                        }
                                    />
                                    <Label
                                        htmlFor="accept_risk"
                                        className="leading-snug font-normal"
                                    >
                                        Saya memahami bahwa alat ini tidak
                                        menjamin profit dan risiko trading
                                        sepenuhnya ada pada saya.
                                    </Label>
                                </div>
                                <InputError
                                    message={
                                        errors.accept_risk ??
                                        errors.order ??
                                        errors.checkout
                                    }
                                />
                                <Button
                                    type="submit"
                                    disabled={form.processing}
                                >
                                    {form.processing && <Spinner />}{' '}
                                    {product.price === 0
                                        ? 'Ambil gratis'
                                        : 'Beli sekarang'}
                                </Button>
                            </form>
                        )}
                    </aside>
                </div>
            </div>
        </>
    );
}

ProductPage.layout = {
    breadcrumbs: [
        { title: 'Marketplace', href: marketplaceIndex() },
        { title: 'Detail Produk' },
    ],
};
