import { formatDate, formatRupiah } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { LedgerRow } from '@/types';

/**
 * Wallet ledger rows; signed amounts with explicit +/−.
 */
export function LedgerTable({ rows }: { rows: LedgerRow[] }) {
    if (rows.length === 0) {
        return (
            <p className="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
                Belum ada transaksi.
            </p>
        );
    }

    return (
        <div className="overflow-x-auto rounded-xl border">
            <table className="w-full text-sm">
                <thead className="bg-muted/50 text-left text-xs text-muted-foreground">
                    <tr>
                        <th className="px-4 py-2 font-medium">Waktu</th>
                        <th className="px-4 py-2 font-medium">Transaksi</th>
                        <th className="px-4 py-2 font-medium">Saldo</th>
                        <th className="px-4 py-2 text-right font-medium">
                            Nominal
                        </th>
                        <th className="hidden px-4 py-2 text-right font-medium md:table-cell">
                            Saldo setelah
                        </th>
                    </tr>
                </thead>
                <tbody className="divide-y">
                    {rows.map((row) => (
                        <tr key={row.id}>
                            <td className="px-4 py-2 text-xs whitespace-nowrap text-muted-foreground">
                                {row.created_at
                                    ? formatDate(row.created_at)
                                    : '—'}
                            </td>
                            <td className="px-4 py-2">
                                <span className="block">{row.type_label}</span>
                                {row.description && (
                                    <span className="text-xs text-muted-foreground">
                                        {row.description}
                                    </span>
                                )}
                            </td>
                            <td className="px-4 py-2 text-xs text-muted-foreground">
                                {row.bucket_label}
                            </td>
                            <td
                                className={cn(
                                    'px-4 py-2 text-right font-mono whitespace-nowrap tabular-nums',
                                    row.amount > 0
                                        ? 'text-profit'
                                        : 'text-loss',
                                )}
                            >
                                {row.amount > 0 ? '+' : ''}
                                {formatRupiah(row.amount)}
                            </td>
                            <td className="hidden px-4 py-2 text-right font-mono whitespace-nowrap text-muted-foreground tabular-nums md:table-cell">
                                {formatRupiah(row.balance_after)}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
