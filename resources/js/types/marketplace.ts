import type { RankSummary } from './trader';

export type ProductCardData = {
    id: number;
    name: string;
    slug: string;
    summary: string;
    category: string;
    platform: string;
    platform_label: string;
    price: number;
    version: string;
    rating_avg: number;
    rating_count: number;
    sales_count: number;
    cover_url: string | null;
    status: string;
    status_label: string;
    seller: { name: string; slug: string | null; rank: RankSummary | null };
};

export type ProductDetail = ProductCardData & {
    description: string;
    screenshot_urls: string[];
    published_at: string | null;
};

export type ProductReviewItem = {
    id: number;
    name: string;
    rating: number;
    comment: string | null;
    created_at: string | null;
};
