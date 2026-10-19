import { useMemo, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    CheckCircle2,
    Lock,
    Search,
    ShieldCheck,
    Store,
    X,
} from 'lucide-react';
import Heading from '@/components/heading';
import { Pagination } from '@/components/domain/pagination';
import { ProductCard } from '@/components/domain/product-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index } from '@/routes/marketplace';
import type { MasterOption, Paginated, ProductCardData } from '@/types';

type Filters = {
    category: string | null;
    platform: string | null;
    sort: string;
};

const SORTS = [
    { value: 'popular', label: 'Terlaris' },
    { value: 'rating', label: 'Rating Tertinggi' },
    { value: 'newest', label: 'Terbaru' },
    { value: 'price_low', label: 'Harga Terendah' },
];

export default function Marketplace({
    filters,
    categories,
    platforms,
    products,
}: {
    filters: Filters;
    categories: MasterOption[];
    platforms: MasterOption[];
    products: Paginated<ProductCardData>;
}) {
    const [searchQuery, setSearchQuery] = useState('');
    const [priceFilter, setPriceFilter] = useState<'all' | 'free' | 'paid'>(
        'all',
    );

    const apply = (next: Partial<Filters>) => {
        const query = { ...filters, ...next };
        router.get(
            index().url,
            {
                category: query.category ?? undefined,
                platform: query.platform ?? undefined,
                sort: query.sort === 'popular' ? undefined : query.sort,
            },
            { preserveScroll: true, preserveState: true, replace: true },
        );
    };

    // Filter products client-side for immediate responsive search experience
    const filteredProducts = useMemo(() => {
        return products.data.filter((item) => {
            const matchesQuery =
                searchQuery.trim() === '' ||
                item.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                item.summary
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase()) ||
                item.seller.name
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase()) ||
                item.platform_label
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase());

            const matchesPrice =
                priceFilter === 'all' ||
                (priceFilter === 'free' && item.price === 0) ||
                (priceFilter === 'paid' && item.price > 0);

            return matchesQuery && matchesPrice;
        });
    }, [products.data, searchQuery, priceFilter]);

    const select = (
        label: string,
        value: string | null,
        options: MasterOption[],
        onChange: (value: string | null) => void,
    ) => (
        <Select
            value={value ?? 'all'}
            onValueChange={(next) => onChange(next === 'all' ? null : next)}
        >
            <SelectTrigger
                className="h-9 w-full text-xs sm:w-44"
                aria-label={label}
            >
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all" className="text-xs">
                    Semua {label}
                </SelectItem>
                {options.map((option) => (
                    <SelectItem
                        key={option.value}
                        value={String(option.value)}
                        className="text-xs"
                    >
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );

    return (
        <>
            <Head title="Marketplace Indikator & EA" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Marketplace Indikator & EA"
                    description="Katalog alat analisis teknikal, script otomatisasi, dan Expert Advisor karya trader SooTa. Setiap produk dikurasi dan diawasi sebelum tayang."
                />

                {/* MQL5 + TradingView Benchmark: Trust & Standard Bar */}
                <div className="grid grid-cols-1 gap-3 rounded-xl border border-border bg-card p-3 sm:grid-cols-3">
                    <div className="flex items-center gap-2.5">
                        <ShieldCheck className="size-4 shrink-0 text-brand" />
                        <div>
                            <p className="text-xs font-semibold text-foreground">
                                Kurasi Kode Ketat
                            </p>
                            <p className="text-[11px] text-muted-foreground">
                                Kode ditinjau sebelum listing publik.
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2.5 border-t border-border/60 pt-2 sm:border-t-0 sm:border-l sm:pt-0 sm:pl-3">
                        <Lock className="size-4 shrink-0 text-brand" />
                        <div>
                            <p className="text-xs font-semibold text-foreground">
                                Lisensi Unik Terikat
                            </p>
                            <p className="text-[11px] text-muted-foreground">
                                Terikat dengan nomor akun MT4/MT5 kamu.
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2.5 border-t border-border/60 pt-2 sm:border-t-0 sm:border-l sm:pt-0 sm:pl-3">
                        <CheckCircle2 className="size-4 shrink-0 text-brand" />
                        <div>
                            <p className="text-xs font-semibold text-foreground">
                                Standar Objektif
                            </p>
                            <p className="text-[11px] text-muted-foreground">
                                Kinerja masa lalu tidak menjamin hasil masa
                                depan.
                            </p>
                        </div>
                    </div>
                </div>

                {/* Filter and Real-Time Search Bar */}
                <div className="flex flex-col gap-3 rounded-xl border border-border bg-card p-3">
                    <div className="flex flex-col gap-2.5 lg:flex-row lg:items-center lg:justify-between">
                        {/* Search Input */}
                        <div className="relative flex-1">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                placeholder="Cari nama indikator, EA, algoritma, atau nama analis..."
                                className="h-9 pr-8 pl-9 text-xs"
                            />
                            {searchQuery && (
                                <button
                                    type="button"
                                    onClick={() => setSearchQuery('')}
                                    className="absolute top-1/2 right-2.5 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                >
                                    <X className="size-3.5" />
                                </button>
                            )}
                        </div>

                        {/* Dropdown Filters */}
                        <div className="flex flex-wrap items-center gap-2">
                            {select(
                                'Kategori',
                                filters.category,
                                categories,
                                (category) => apply({ category }),
                            )}
                            {select(
                                'Platform',
                                filters.platform,
                                platforms,
                                (platform) => apply({ platform }),
                            )}
                            <Select
                                value={filters.sort}
                                onValueChange={(sort) => apply({ sort })}
                            >
                                <SelectTrigger
                                    className="h-9 w-full text-xs sm:w-40"
                                    aria-label="Urutkan"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {SORTS.map((sort) => (
                                        <SelectItem
                                            key={sort.value}
                                            value={sort.value}
                                            className="text-xs"
                                        >
                                            {sort.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    {/* Price Toggles & Result Count */}
                    <div className="flex flex-wrap items-center justify-between border-t border-border/60 pt-2.5 text-xs text-muted-foreground">
                        <div className="flex items-center gap-1.5">
                            <span className="font-medium text-foreground">
                                Filter Harga:
                            </span>
                            <div className="inline-flex rounded-lg border border-border bg-muted/40 p-0.5">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => setPriceFilter('all')}
                                    className={`h-7 px-2.5 text-xs ${
                                        priceFilter === 'all'
                                            ? 'bg-background font-semibold text-foreground shadow-xs'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    Semua
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => setPriceFilter('free')}
                                    className={`h-7 px-2.5 text-xs ${
                                        priceFilter === 'free'
                                            ? 'bg-background font-semibold text-foreground shadow-xs'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    Gratis
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => setPriceFilter('paid')}
                                    className={`h-7 px-2.5 text-xs ${
                                        priceFilter === 'paid'
                                            ? 'bg-background font-semibold text-foreground shadow-xs'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    Berbayar
                                </Button>
                            </div>
                        </div>

                        <div className="font-mono text-xs text-muted-foreground tabular-nums">
                            Menampilkan{' '}
                            <span className="font-semibold text-foreground">
                                {filteredProducts.length}
                            </span>{' '}
                            dari <span>{products.total}</span> produk
                        </div>
                    </div>
                </div>

                {/* Product Catalog Grid */}
                {filteredProducts.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed border-border bg-card p-12 text-center">
                        <Store className="size-10 text-muted-foreground" />
                        <div className="grid gap-1">
                            <p className="font-semibold text-foreground">
                                {searchQuery || priceFilter !== 'all'
                                    ? 'Tidak ada produk yang cocok'
                                    : 'Belum ada produk tayang'}
                            </p>
                            <p className="max-w-sm text-xs text-muted-foreground">
                                {searchQuery || priceFilter !== 'all'
                                    ? 'Coba atur ulang kata kunci pencarian atau sesuaikan filter kategori dan platform.'
                                    : 'Katalog produk sedang dalam kurasi oleh tim SooTa. Periksa kembali nanti.'}
                            </p>
                        </div>
                        {(searchQuery || priceFilter !== 'all') && (
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => {
                                    setSearchQuery('');
                                    setPriceFilter('all');
                                }}
                                className="h-8 text-xs"
                            >
                                Reset Filter
                            </Button>
                        )}
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {filteredProducts.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                )}

                <Pagination paginator={products} />
            </div>
        </>
    );
}

Marketplace.layout = {
    breadcrumbs: [{ title: 'Marketplace', href: index() }],
};
