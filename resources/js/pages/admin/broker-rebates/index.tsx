import { Head, router, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { StatTile } from '@/components/domain/stat-tile';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatRupiah } from '@/lib/format';
import { importMethod, index } from '@/routes/admin/broker-rebates';
import type { MasterOption } from '@/types';

type Row = {
    id: number;
    broker: string;
    account_login: string;
    member: string | null;
    lots: number;
    rebate_usd: number;
    rebate_idr: number;
    matched: boolean;
};

type Props = {
    period: string;
    brokers: MasterOption[];
    rows: Row[];
    totals: {
        lots: number;
        rebate_usd: number;
        rebate_idr: number;
        unmatched: number;
    };
    byBroker: { broker: string; rebate_idr: number; lots: number }[];
};

export default function BrokerRebates({
    period,
    brokers,
    rows,
    totals,
    byBroker,
}: Props) {
    const form = useForm<{
        broker_id: string;
        period: string;
        fx_rate: string;
        file: File | null;
    }>({
        broker_id: brokers[0] ? String(brokers[0].value) : '',
        period,
        fx_rate: '',
        file: null,
    });
    const errors = form.errors as Record<string, string | undefined>;

    return (
        <>
            <Head title="Rebate IB" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Rebate IB"
                    description="Impor laporan IB dari portal broker (CSV dengan kolom account_login, lots, rebate_usd). Impor ulang periode yang sama akan menggantikan data sebelumnya."
                />
                <form
                    className="grid gap-4 rounded-xl border p-4 md:grid-cols-5 md:items-end md:p-6"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.submit(importMethod(), {
                            forceFormData: true,
                            preserveScroll: true,
                        });
                    }}
                >
                    <div className="grid gap-2">
                        <Label htmlFor="broker_id">Broker</Label>
                        <Select
                            value={form.data.broker_id}
                            onValueChange={(value) =>
                                form.setData('broker_id', value)
                            }
                        >
                            <SelectTrigger id="broker_id">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {brokers.map((option) => (
                                    <SelectItem
                                        key={option.value}
                                        value={String(option.value)}
                                    >
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="period">Periode</Label>
                        <Input
                            id="period"
                            type="month"
                            value={form.data.period}
                            onChange={(e) =>
                                form.setData('period', e.target.value)
                            }
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="fx_rate">Kurs USD → IDR</Label>
                        <Input
                            id="fx_rate"
                            type="number"
                            step="0.01"
                            className="font-mono"
                            value={form.data.fx_rate}
                            onChange={(e) =>
                                form.setData('fx_rate', e.target.value)
                            }
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="file">File CSV</Label>
                        <Input
                            id="file"
                            type="file"
                            accept=".csv,text/csv"
                            onChange={(e) =>
                                form.setData(
                                    'file',
                                    e.target.files?.[0] ?? null,
                                )
                            }
                        />
                    </div>
                    <Button type="submit" disabled={form.processing}>
                        Impor
                    </Button>
                    <InputError
                        className="md:col-span-5"
                        message={
                            errors.file ??
                            errors.fx_rate ??
                            errors.period ??
                            errors.broker_id
                        }
                    />
                </form>

                <div className="flex items-end gap-2">
                    <div className="grid gap-1">
                        <Label htmlFor="view-period">Lihat periode</Label>
                        <Input
                            id="view-period"
                            type="month"
                            defaultValue={period}
                            className="w-44"
                            onChange={(e) =>
                                e.target.value &&
                                router.get(
                                    index().url,
                                    { period: e.target.value },
                                    { preserveScroll: true },
                                )
                            }
                        />
                    </div>
                </div>

                <div className="grid gap-3 sm:grid-cols-3">
                    <StatTile
                        label="Rebate (IDR)"
                        value={formatRupiah(totals.rebate_idr)}
                        hint={`$${totals.rebate_usd.toLocaleString('id-ID')} · ${totals.lots.toLocaleString('id-ID')} lot`}
                    />
                    <StatTile
                        label="Broker"
                        value={byBroker.length.toLocaleString('id-ID')}
                        hint={
                            byBroker
                                .map(
                                    (item) =>
                                        `${item.broker}: ${formatRupiah(item.rebate_idr)}`,
                                )
                                .join(' · ') || '—'
                        }
                    />
                    <StatTile
                        label="Belum cocok"
                        value={totals.unmatched.toLocaleString('id-ID')}
                        hint="Login yang belum terhubung ke member"
                    />
                </div>

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left text-xs text-muted-foreground">
                            <tr>
                                <th className="px-4 py-2 font-medium">
                                    Broker
                                </th>
                                <th className="px-4 py-2 font-medium">Login</th>
                                <th className="px-4 py-2 font-medium">
                                    Member
                                </th>
                                <th className="px-4 py-2 text-right font-medium">
                                    Lot
                                </th>
                                <th className="px-4 py-2 text-right font-medium">
                                    Rebate
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {rows.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        Belum ada data untuk periode ini.
                                    </td>
                                </tr>
                            )}
                            {rows.map((row) => (
                                <tr key={row.id}>
                                    <td className="px-4 py-2">{row.broker}</td>
                                    <td className="px-4 py-2 font-mono">
                                        {row.account_login}
                                    </td>
                                    <td className="px-4 py-2">
                                        {row.matched ? (
                                            row.member
                                        ) : (
                                            <StatusPill
                                                label="Belum cocok"
                                                tone="pending"
                                            />
                                        )}
                                    </td>
                                    <td className="px-4 py-2 text-right font-mono tabular-nums">
                                        {row.lots.toLocaleString('id-ID')}
                                    </td>
                                    <td className="px-4 py-2 text-right font-mono tabular-nums">
                                        {formatRupiah(row.rebate_idr)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

BrokerRebates.layout = {
    breadcrumbs: [{ title: 'Rebate IB', href: index() }],
};
