import { cn } from '@/lib/utils';

/**
 * KPI figure: label, value and context (period or sample size) — docs/ui-ux/components.md.
 */
export function StatTile({
    label,
    value,
    hint,
    tone,
}: {
    label: string;
    value: string;
    hint?: string;
    tone?: 'profit' | 'loss';
}) {
    return (
        <div className="grid content-start gap-1 rounded-xl border bg-card p-4">
            <span className="text-xs text-muted-foreground">{label}</span>
            <span
                className={cn(
                    'font-mono text-2xl font-semibold tabular-nums',
                    tone === 'profit' && 'text-profit',
                    tone === 'loss' && 'text-loss',
                )}
            >
                {value}
            </span>
            {hint && (
                <span className="text-xs text-muted-foreground">{hint}</span>
            )}
        </div>
    );
}
