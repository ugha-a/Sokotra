import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

/**
 * Whole-rupiah input. Values stay integers; formatting happens on display only.
 */
export function MoneyInput({
    className,
    ...props
}: Omit<React.ComponentProps<typeof Input>, 'type' | 'step'>) {
    return (
        <div className="relative">
            <span className="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-muted-foreground">
                Rp
            </span>
            <Input
                type="number"
                inputMode="numeric"
                step={1}
                min={0}
                className={cn('pl-9 font-mono tabular-nums', className)}
                {...props}
            />
        </div>
    );
}
