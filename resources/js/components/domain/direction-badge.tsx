import { TrendingDown, TrendingUp } from 'lucide-react';
import { cn } from '@/lib/utils';
import type { SignalDirection } from '@/types';

/** Buy/Sell with icon and text — never colour alone (docs/ui-ux/design-system.md). */
export function DirectionBadge({ direction }: { direction: SignalDirection }) {
    const isBuy = direction === 'buy';
    const Icon = isBuy ? TrendingUp : TrendingDown;

    return (
        <span
            className={cn(
                'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                isBuy ? 'bg-profit-soft text-profit' : 'bg-loss-soft text-loss',
            )}
        >
            <Icon className="size-3.5" />
            {isBuy ? 'Buy' : 'Sell'}
        </span>
    );
}
