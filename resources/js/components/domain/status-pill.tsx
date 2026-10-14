import { cn } from '@/lib/utils';

type Tone = 'profit' | 'loss' | 'pending' | 'neutral' | 'premium' | 'brand';

const tones: Record<Tone, string> = {
    profit: 'bg-profit-soft text-profit',
    loss: 'bg-loss-soft text-loss',
    pending: 'bg-pending-soft text-pending',
    neutral: 'bg-neutral-status-soft text-neutral-status',
    premium: 'bg-premium-soft text-premium',
    brand: 'bg-brand/12 text-brand',
};

export function StatusPill({
    label,
    tone,
    className,
}: {
    label: string;
    tone: Tone;
    className?: string;
}) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap',
                tones[tone],
                className,
            )}
        >
            {label}
        </span>
    );
}
