import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

/**
 * Page links for a Laravel paginator. Renders nothing for a single page.
 */
export function Pagination<T>({ paginator }: { paginator: Paginated<T> }) {
    if (paginator.last_page <= 1) {
        return null;
    }

    return (
        <nav aria-label="Halaman" className="flex flex-wrap gap-1">
            {paginator.links.map((link, i) => (
                <Link
                    key={i}
                    href={link.url ?? '#'}
                    preserveScroll
                    className={cn(
                        'rounded-md border px-3 py-1 text-sm',
                        link.active &&
                            'border-brand bg-brand text-brand-foreground',
                        !link.url && 'pointer-events-none opacity-50',
                    )}
                >
                    {link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                </Link>
            ))}
        </nav>
    );
}
