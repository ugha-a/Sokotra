import { Head, router } from '@inertiajs/react';
import { Store } from 'lucide-react';
import Heading from '@/components/heading';
import { Pagination } from '@/components/domain/pagination';
import { ProductCard } from '@/components/domain/product-card';
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
    { value: 'newest', label: 'Terbaru' },
    { value: 'rating', label: 'Rating tertinggi' },
    { value: 'price_low', label: 'Harga terendah' },
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
            <SelectTrigger className="w-48" aria-label={label}>
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">Semua {label.toLowerCase()}</SelectItem>
                {options.map((option) => (
                    <SelectItem key={option.value} value={String(option.value)}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );

    return (
        <>
            <Head title="Marketplace" />
            <div className="grid gap-6">
                <Heading
                    title="Marketplace Indikator & EA"
                    description="Alat analisa dari trader Sokotra. Setiap produk direview admin sebelum tayang. Hasil masa lalu tidak menjamin hasil di masa depan."
                />
                <div className="flex flex-wrap gap-2">
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
                        <SelectTrigger className="w-44" aria-label="Urutkan">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {SORTS.map((sort) => (
                                <SelectItem key={sort.value} value={sort.value}>
                                    {sort.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                {products.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <Store className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada produk</p>
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {products.data.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                )}
                <Pagination paginator={products} />
            </div>
        </>
    );
}
