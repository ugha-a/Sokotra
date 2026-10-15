import { router } from '@inertiajs/react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import type { MasterOption, SignalFilters as Filters } from '@/types';

/**
 * Status tabs and optional instrument select; updates the query string.
 */
export function SignalFilters({
    url,
    filters,
    statusOptions,
    instrumentOptions,
}: {
    url: string;
    filters: Filters;
    statusOptions: MasterOption[];
    instrumentOptions?: MasterOption[];
}) {
    const apply = (next: Partial<Filters>) => {
        const query = { ...filters, ...next };

        router.get(
            url,
            {
                status: query.status === 'all' ? undefined : query.status,
                instrument: query.instrument ?? undefined,
            },
            { preserveScroll: true, preserveState: true, replace: true },
        );
    };

    return (
        <div className="flex flex-wrap items-center gap-2">
            <div role="tablist" className="flex gap-2 overflow-x-auto">
                {statusOptions.map((option) => (
                    <button
                        key={option.value}
                        type="button"
                        role="tab"
                        aria-selected={filters.status === option.value}
                        onClick={() => apply({ status: String(option.value) })}
                        className={cn(
                            'rounded-full border px-3 py-1.5 text-sm whitespace-nowrap transition-colors',
                            filters.status === option.value
                                ? 'border-brand bg-brand text-brand-foreground'
                                : 'hover:bg-accent',
                        )}
                    >
                        {option.label}
                    </button>
                ))}
            </div>
            {instrumentOptions && (
                <Select
                    value={
                        filters.instrument === null
                            ? 'all'
                            : String(filters.instrument)
                    }
                    onValueChange={(value) =>
                        apply({
                            instrument: value === 'all' ? null : Number(value),
                        })
                    }
                >
                    <SelectTrigger className="w-40" aria-label="Instrumen">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua instrumen</SelectItem>
                        {instrumentOptions.map((option) => (
                            <SelectItem
                                key={option.value}
                                value={String(option.value)}
                            >
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}
        </div>
    );
}
