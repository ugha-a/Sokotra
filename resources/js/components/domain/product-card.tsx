import { Link } from '@inertiajs/react';
import { Package, Star } from 'lucide-react';
import { RankBadge } from '@/components/domain/rank-badge';
import { formatRupiah } from '@/lib/format';
import { show } from '@/routes/marketplace';
import type { ProductCardData } from '@/types';

export function ProductCard({ product }: { product: ProductCardData }) {
    return (
        <Link
            href={show(product.slug)}
            className="group grid content-start overflow-hidden rounded-xl border bg-card transition-colors hover:border-brand/50"
        >
            <div className="flex aspect-video items-center justify-center bg-muted">
                {product.cover_url ? (
                    <img
                        src={product.cover_url}
                        alt=""
                        className="size-full object-cover"
                        loading="lazy"
                    />
                ) : (
                    <Package className="size-10 text-muted-foreground" />
                )}
            </div>
            <div className="grid gap-2 p-4">
                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                    <span className="rounded-full border px-2 py-0.5">
                        {product.platform_label}
                    </span>
                    <span>{product.category}</span>
                </div>
                <h3 className="font-medium group-hover:text-brand">
                    {product.name}
                </h3>
                <p className="line-clamp-2 text-sm text-muted-foreground">
                    {product.summary}
                </p>
                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                    <span className="text-foreground">
                        {product.seller.name}
                    </span>
                    {product.seller.rank && (
                        <RankBadge
                            name={product.seller.rank.name}
                            color={product.seller.rank.color}
                        />
                    )}
                </div>
                <div className="flex items-center justify-between pt-1">
                    <span className="font-mono font-semibold tabular-nums">
                        {product.price === 0
                            ? 'Gratis'
                            : formatRupiah(product.price)}
                    </span>
                    <span className="flex items-center gap-1 text-xs text-muted-foreground">
                        <Star className="size-3.5 fill-premium text-premium" />
                        {product.rating_count > 0
                            ? product.rating_avg.toLocaleString('id-ID', {
                                  maximumFractionDigits: 1,
                              })
                            : '—'}{' '}
                        · {product.sales_count} terjual
                    </span>
                </div>
            </div>
        </Link>
    );
}
