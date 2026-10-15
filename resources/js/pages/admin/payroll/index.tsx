import { Head, Link, useForm } from '@inertiajs/react';
import { ChevronRight, ReceiptText } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { generate, index, show } from '@/routes/admin/payroll';
import type { PayrollPeriodSummary } from '@/types';

export default function PayrollIndex({
    periods,
    defaultMonth,
}: {
    periods: PayrollPeriodSummary[];
    defaultMonth: string;
}) {
    const form = useForm({ month: defaultMonth });

    return (
        <>
            <Head title="Payroll" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Payroll"
                    description="Draft dibuat otomatis tanggal 1 pukul 01.00 WIB untuk bulan sebelumnya. Nominal diatur di Master Data → Aturan Gaji & Bracket Followers."
                />
                <form
                    className="flex flex-wrap items-end gap-2"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.submit(generate());
                    }}
                >
                    <div className="grid gap-1">
                        <label htmlFor="month" className="text-sm font-medium">
                            Buat / perbarui draft
                        </label>
                        <Input
                            id="month"
                            type="month"
                            value={form.data.month}
                            onChange={(e) =>
                                form.setData('month', e.target.value)
                            }
                            className="w-44"
                        />
                    </div>
                    <Button
                        type="submit"
                        variant="outline"
                        disabled={form.processing}
                    >
                        Buat draft
                    </Button>
                    <InputError
                        message={
                            form.errors.month ??
                            (form.errors as Record<string, string>).payroll
                        }
                        className="w-full"
                    />
                </form>

                {periods.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <ReceiptText className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada payroll</p>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {periods.map((period) => (
                            <li key={period.id}>
                                <Link
                                    href={show(period.id)}
                                    className="flex items-center gap-4 p-4 hover:bg-accent/50"
                                >
                                    <span className="grid flex-1 gap-1">
                                        <span className="flex items-center gap-2 font-medium">
                                            {period.label}{' '}
                                            <StatusPill
                                                label={period.status_label}
                                                tone={statusTone(period.status)}
                                            />
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {period.eligible_count} dari{' '}
                                            {period.items_count} trader memenuhi
                                            syarat
                                        </span>
                                    </span>
                                    <span className="font-mono tabular-nums">
                                        {formatRupiah(period.total_amount)}
                                    </span>
                                    <ChevronRight className="size-4 text-muted-foreground" />
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

PayrollIndex.layout = {
    breadcrumbs: [{ title: 'Payroll', href: index() }],
};
