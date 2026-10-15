import { Flame } from 'lucide-react';

/**
 * Progress towards the payroll streak requirement (docs/logic/payroll-rewards.md).
 */
export function StreakIndicator({
    current,
    bestThisMonth,
    required,
}: {
    current: number;
    bestThisMonth: number;
    required: number | null;
}) {
    const target = required ?? 0;
    const percent =
        target > 0
            ? Math.min(100, Math.round((bestThisMonth / target) * 100))
            : 0;

    return (
        <div className="grid gap-3 rounded-xl border bg-card p-4">
            <div className="flex items-center gap-2">
                <Flame className="size-4 text-pending" />
                <span className="text-sm font-medium">Streak menang</span>
            </div>
            <div className="flex items-baseline gap-2">
                <span className="font-mono text-3xl font-semibold tabular-nums">
                    {current}
                </span>
                <span className="text-sm text-muted-foreground">
                    berturut-turut saat ini
                </span>
            </div>
            {required !== null && (
                <>
                    <div
                        className="h-2 overflow-hidden rounded-full bg-pending-soft"
                        role="progressbar"
                        aria-valuemin={0}
                        aria-valuemax={target}
                        aria-valuenow={Math.min(bestThisMonth, target)}
                        aria-label="Streak terbaik bulan ini menuju syarat gaji"
                    >
                        <div
                            className="h-full rounded-full bg-pending"
                            style={{ width: `${percent}%` }}
                        />
                    </div>
                    <p className="text-xs text-muted-foreground">
                        Terbaik bulan ini {bestThisMonth} dari syarat gaji{' '}
                        {required} kemenangan berturut-turut.
                    </p>
                </>
            )}
        </div>
    );
}
