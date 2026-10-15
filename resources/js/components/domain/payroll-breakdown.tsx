import { CircleAlert } from 'lucide-react';
import { formatRupiah } from '@/lib/format';
import type { PayrollBreakdownData } from '@/types';

/**
 * Salary slip lines: base + wins × rate + follower bonus + streak bonus (docs/logic/payroll-rewards.md).
 */
export function PayrollBreakdown({
    data,
    perWin,
}: {
    data: PayrollBreakdownData;
    perWin?: number;
}) {
    return (
        <div className="grid gap-3">
            {!data.eligible && data.reasons.length > 0 && (
                <ul className="grid gap-1 rounded-lg bg-muted/50 p-3 text-sm">
                    {data.reasons.map((reason) => (
                        <li
                            key={reason}
                            className="flex items-center gap-2 text-muted-foreground"
                        >
                            <CircleAlert className="size-4 shrink-0 text-pending" />{' '}
                            {reason}
                        </li>
                    ))}
                </ul>
            )}
            <dl className="grid gap-1.5 text-sm">
                <Line label="Gaji pokok" value={data.base_amount} />
                <Line
                    label={`Analisa benar dibayar: ${data.wins_counted}${perWin ? ` × ${formatRupiah(perWin)}` : ''}`}
                    value={data.wins_amount}
                />
                <Line
                    label={`Bonus followers (${data.followers_counted} follower valid)`}
                    value={data.followers_amount}
                />
                <Line
                    label={`Bonus streak (terbaik ${data.best_streak}×)`}
                    value={data.streak_amount}
                />
                <div className="mt-1 flex justify-between border-t pt-2 font-medium">
                    <dt>Total</dt>
                    <dd className="font-mono text-base tabular-nums">
                        {formatRupiah(data.total_amount)}
                    </dd>
                </div>
            </dl>
            <p className="text-xs text-muted-foreground">
                {data.closed_signals} sinyal selesai · drawdown maksimal{' '}
                {data.max_drawdown_r.toLocaleString('id-ID')}R
            </p>
        </div>
    );
}

function Line({ label, value }: { label: string; value: number }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-muted-foreground">{label}</dt>
            <dd className="font-mono tabular-nums">{formatRupiah(value)}</dd>
        </div>
    );
}
