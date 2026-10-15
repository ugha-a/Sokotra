import { TriangleAlert } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * Risk disclaimer from the content.risk_disclaimer setting (docs/ui-ux/copywriting.md).
 */
export function RiskDisclaimer({
    text,
    variant = 'card',
    className,
}: {
    text: string;
    variant?: 'card' | 'inline';
    className?: string;
}) {
    if (variant === 'inline') {
        return (
            <p className={cn('text-xs text-muted-foreground', className)}>
                {text}
            </p>
        );
    }

    return (
        <div
            className={cn(
                'flex gap-3 rounded-xl border border-pending/40 bg-pending-soft p-4 text-sm',
                className,
            )}
        >
            <TriangleAlert className="mt-0.5 size-4 shrink-0 text-pending" />
            <p>{text}</p>
        </div>
    );
}
