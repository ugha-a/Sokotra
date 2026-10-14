import { cn } from '@/lib/utils';

/**
 * Rank pill coloured by the rank's design token (rank-1 … rank-6).
 */
export function RankBadge({
    name,
    color,
    className,
}: {
    name: string;
    color: string;
    className?: string;
}) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                className,
            )}
            style={{
                color: `var(--${color})`,
                backgroundColor: `color-mix(in oklch, var(--${color}) 14%, transparent)`,
            }}
        >
            {name}
        </span>
    );
}
