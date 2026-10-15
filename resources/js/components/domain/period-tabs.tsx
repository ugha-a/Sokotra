import { cn } from '@/lib/utils';

export type StatPeriod = 'week' | 'month' | 'rolling' | 'all_time';

export function periodLabel(period: StatPeriod, windowDays: number): string {
    return {
        week: 'Minggu ini',
        month: 'Bulan ini',
        rolling: `${windowDays} hari`,
        all_time: 'Sepanjang waktu',
    }[period];
}

export function PeriodTabs({
    value,
    onChange,
    windowDays,
    periods = ['week', 'month', 'rolling', 'all_time'],
}: {
    value: StatPeriod;
    onChange: (period: StatPeriod) => void;
    windowDays: number;
    periods?: StatPeriod[];
}) {
    return (
        <div
            role="tablist"
            className="flex gap-1 overflow-x-auto rounded-lg border p-1"
        >
            {periods.map((period) => (
                <button
                    key={period}
                    type="button"
                    role="tab"
                    aria-selected={value === period}
                    onClick={() => onChange(period)}
                    className={cn(
                        'rounded-md px-3 py-1 text-sm whitespace-nowrap transition-colors',
                        value === period
                            ? 'bg-brand text-brand-foreground'
                            : 'hover:bg-accent',
                    )}
                >
                    {periodLabel(period, windowDays)}
                </button>
            ))}
        </div>
    );
}
