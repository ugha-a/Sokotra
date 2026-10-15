import { Link } from '@inertiajs/react';
import { Bot, Code2, Star } from 'lucide-react';
import { RankBadge } from '@/components/domain/rank-badge';
import { formatRupiah } from '@/lib/format';
import { show } from '@/routes/marketplace';
import type { ProductCardData } from '@/types';

/**
 * Utilitarian, high data density product card inspired by TradingView Community Scripts & MQL5.
 * Strictly adheres to Anti-AI-Slop guidelines: no glowing drop shadows, no blur filters.
 */
export function ProductCard({ product }: { product: ProductCardData }) {
    const isFree = product.price === 0;

    return (
        <Link
            href={show(product.slug)}
            className="group flex flex-col justify-between overflow-hidden rounded-xl border border-border bg-card transition-all duration-200 hover:border-brand/60 hover:shadow-xs"
        >
            <div>
                {/* Visual Header / Cover Image or TradingView-style Algo Banner */}
                {product.cover_url ? (
                    <div className="aspect-[16/9] w-full overflow-hidden border-b border-border bg-muted">
                        <img
                            src={product.cover_url}
                            alt={product.name}
                            className="size-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                            loading="lazy"
                        />
                    </div>
                ) : (
                    <div className="relative flex aspect-[16/9] w-full flex-col justify-between border-b border-border bg-muted/40 p-3 font-mono text-[11px] text-muted-foreground select-none">
                        <div className="flex items-center justify-between border-b border-border/50 pb-1.5">
                            <span className="flex items-center gap-1.5 font-medium text-foreground">
                                {product.platform === 'tradingview' ? (
                                    <Code2 className="size-3.5 text-brand" />
                                ) : (
                                    <Bot className="size-3.5 text-brand" />
                                )}
                                {product.platform_label}
                            </span>
                            <span className="rounded border border-border bg-background px-1.5 py-0.5 text-[10px] text-muted-foreground">
                                v{product.version}
                            </span>
                        </div>
                        <div className="space-y-1 font-mono text-[10px] leading-tight text-muted-foreground/90">
                            <p className="truncate font-semibold text-foreground/80">
                                <span className="text-brand">algo</span>{' '}
                                {product.name
                                    .toLowerCase()
                                    .replace(/[^a-z0-9]/g, '_')}
                                ()
                            </p>
                            <p className="truncate text-muted-foreground">
                                &gt; Kategori:{' '}
                                <span className="text-foreground">
                                    {product.category}
                                </span>
                            </p>
                            <p className="truncate text-muted-foreground">
                                &gt; Kompatibilitas:{' '}
                                <span className="text-foreground">
                                    {product.platform_label}
                                </span>
                            </p>
                        </div>
                        <div className="flex items-center justify-between border-t border-border/40 pt-1 text-[10px] text-muted-foreground/70">
                            <span>Lisensi 1 Akun</span>
                            <span className="font-semibold text-foreground/80">
                                Kode Terverifikasi
                            </span>
                        </div>
                    </div>
                )}

                <div className="flex flex-col gap-2.5 p-4">
                    {/* Meta Tags: Platform, Category, Version */}
                    <div className="flex flex-wrap items-center gap-1.5 text-xs">
                        <span className="rounded-md border border-border bg-muted/70 px-2 py-0.5 font-mono text-[11px] font-medium text-foreground">
                            {product.platform_label}
                        </span>
                        <span className="rounded-md border border-border/60 bg-background px-2 py-0.5 text-[11px] text-muted-foreground">
                            {product.category}
                        </span>
                        {product.cover_url && (
                            <span className="ml-auto font-mono text-[10px] text-muted-foreground">
                                v{product.version}
                            </span>
                        )}
                    </div>

                    <h3 className="line-clamp-1 font-semibold text-foreground transition-colors group-hover:text-brand">
                        {product.name}
                    </h3>

                    <p className="line-clamp-2 text-xs leading-relaxed text-muted-foreground">
                        {product.summary}
                    </p>

                    {/* Author & Rank Information */}
                    <div className="flex items-center gap-1.5 pt-1 text-xs text-muted-foreground">
                        <span className="flex size-4.5 items-center justify-center rounded-full bg-muted font-mono text-[10px] font-bold text-foreground">
                            {product.seller.name.charAt(0).toUpperCase()}
                        </span>
                        <span className="max-w-[140px] truncate font-medium text-foreground">
                            {product.seller.name}
                        </span>
                        {product.seller.rank && (
                            <RankBadge
                                name={product.seller.rank.name}
                                color={product.seller.rank.color}
                            />
                        )}
                    </div>
                </div>
            </div>

            {/* Bottom Metrics: Price, Rating & Sales Count */}
            <div className="flex items-center justify-between border-t border-border/80 bg-muted/20 px-4 py-2.5 text-xs">
                <div>
                    {isFree ? (
                        <span className="font-mono text-sm font-bold text-profit">
                            Gratis
                        </span>
                    ) : (
                        <span className="font-mono text-sm font-bold text-foreground tabular-nums">
                            {formatRupiah(product.price)}
                        </span>
                    )}
                </div>
                <div className="flex items-center gap-1 font-mono text-xs text-muted-foreground tabular-nums">
                    <Star className="size-3.5 fill-premium text-premium" />
                    <span className="font-semibold text-foreground">
                        {product.rating_count > 0
                            ? product.rating_avg.toLocaleString('id-ID', {
                                  minimumFractionDigits: 1,
                                  maximumFractionDigits: 1,
                              })
                            : '—'}
                    </span>
                    <span className="text-muted-foreground/70">
                        ({product.rating_count})
                    </span>
                    <span className="text-muted-foreground/50">·</span>
                    <span>{product.sales_count} terjual</span>
                </div>
            </div>
        </Link>
    );
}
