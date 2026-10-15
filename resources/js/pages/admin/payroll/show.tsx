import { Head, router, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { ConfirmDialog } from '@/components/domain/confirm-dialog';
import { PayrollBreakdown } from '@/components/domain/payroll-breakdown';
import { RankBadge } from '@/components/domain/rank-badge';
import { ReasonDialog } from '@/components/domain/reason-dialog';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { approve, generate, index } from '@/routes/admin/payroll';
import { hold, release } from '@/routes/admin/payroll/items';
import type { PayrollItemRow, PayrollPeriodSummary } from '@/types';

type Props = {
    period: PayrollPeriodSummary;
    items: PayrollItemRow[];
    canApprove: boolean;
};

export default function PayrollShow({ period, items, canApprove }: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const payable = items.filter(
        (item) => item.eligible && item.status === 'pending',
    );
    const payableTotal = payable.reduce(
        (sum, item) => sum + item.total_amount,
        0,
    );

    return (
        <>
            <Head title={`Payroll ${period.label}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        title={`Payroll ${period.label}`}
                        description={`Total memenuhi syarat ${formatRupiah(period.total_amount)}`}
                    />
                    <div className="flex flex-wrap items-center gap-2">
                        <StatusPill
                            label={period.status_label}
                            tone={statusTone(period.status)}
                        />
                        {canApprove && (
                            <>
                                <Button
                                    variant="outline"
                                    onClick={() =>
                                        router.visit(generate(), {
                                            data: { month: period.month },
                                            preserveScroll: true,
                                        })
                                    }
                                >
                                    Hitung ulang
                                </Button>
                                <ConfirmDialog
                                    trigger={
                                        <Button disabled={payable.length === 0}>
                                            Setujui & bayar
                                        </Button>
                                    }
                                    title={`Setujui payroll ${period.label}?`}
                                    description={`${payable.length} trader akan menerima total ${formatRupiah(payableTotal)} di saldo tersedia. Item yang ditahan tidak dibayar. Tindakan ini tidak bisa dibatalkan.`}
                                    confirmLabel="Setujui & bayar"
                                    onConfirm={() =>
                                        router.visit(approve(period.id), {
                                            preserveScroll: true,
                                        })
                                    }
                                />
                            </>
                        )}
                    </div>
                </div>
                <InputError message={errors.payroll} />

                <div className="grid gap-4 lg:grid-cols-2">
                    {items.map((item) => (
                        <article
                            key={item.id}
                            className="grid content-start gap-3 rounded-xl border p-4"
                        >
                            <header className="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <div className="flex items-center gap-2 font-medium">
                                        {item.trader.name}
                                        {item.rank && (
                                            <RankBadge
                                                name={item.rank.name}
                                                color={item.rank.color}
                                            />
                                        )}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        {item.trader.email}
                                    </p>
                                </div>
                                <StatusPill
                                    label={item.status_label}
                                    tone={statusTone(item.status)}
                                />
                            </header>
                            <PayrollBreakdown data={item} />
                            {item.hold_note && (
                                <p className="text-xs text-premium">
                                    Ditahan: {item.hold_note}
                                </p>
                            )}
                            {canApprove &&
                                item.status === 'pending' &&
                                item.eligible && (
                                    <ReasonDialog
                                        trigger={
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                className="justify-self-start"
                                            >
                                                Tahan
                                            </Button>
                                        }
                                        title={`Tahan gaji ${item.trader.name}?`}
                                        description="Item yang ditahan tidak dibayar saat payroll disetujui."
                                        confirmLabel="Tahan"
                                        action={hold(item.id)}
                                    />
                                )}
                            {canApprove && item.status === 'held' && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className="justify-self-start"
                                    onClick={() =>
                                        router.visit(release(item.id), {
                                            preserveScroll: true,
                                        })
                                    }
                                >
                                    Lepas penahanan
                                </Button>
                            )}
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

PayrollShow.layout = {
    breadcrumbs: [{ title: 'Payroll', href: index() }],
};
