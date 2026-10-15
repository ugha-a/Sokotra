import { Clock, Lock, Wallet } from 'lucide-react';
import { formatRupiah } from '@/lib/format';
import type { WalletSummary } from '@/types';

/**
 * Available / pending / locked with a short explanation of each bucket.
 */
export function WalletBalance({
    wallet,
    holdDays,
}: {
    wallet: WalletSummary;
    holdDays: number;
}) {
    const items = [
        {
            icon: Wallet,
            label: 'Saldo tersedia',
            value: wallet.available_balance,
            hint: 'Bisa ditarik ke rekening.',
            primary: true,
        },
        {
            icon: Clock,
            label: 'Tertahan',
            value: wallet.pending_balance,
            hint: `Pendapatan baru ditahan ${holdDays} hari untuk antisipasi refund.`,
        },
        {
            icon: Lock,
            label: 'Sedang diproses',
            value: wallet.locked_balance,
            hint: 'Penarikan yang menunggu transfer.',
        },
    ];

    return (
        <div className="grid gap-3 md:grid-cols-3">
            {items.map(({ icon: Icon, label, value, hint, primary }) => (
                <div
                    key={label}
                    className="grid content-start gap-1 rounded-xl border bg-card p-4"
                >
                    <span className="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Icon className="size-3.5" /> {label}
                    </span>
                    <span
                        className={
                            primary
                                ? 'font-mono text-3xl font-semibold tabular-nums'
                                : 'font-mono text-xl font-semibold tabular-nums'
                        }
                    >
                        {formatRupiah(value)}
                    </span>
                    <span className="text-xs text-muted-foreground">
                        {hint}
                    </span>
                </div>
            ))}
        </div>
    );
}
