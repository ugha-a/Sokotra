import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Banknote, TriangleAlert } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { ConfirmDialog } from '@/components/domain/confirm-dialog';
import { Pagination } from '@/components/domain/pagination';
import { ReasonDialog } from '@/components/domain/reason-dialog';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate, formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { cn } from '@/lib/utils';
import { approve, index, paid, reject } from '@/routes/admin/withdrawals';
import type {
    AdminWithdrawalRow,
    Paginated,
    TraderStatusFilter,
} from '@/types';

type Props = {
    status: string;
    statuses: TraderStatusFilter[];
    withdrawals: Paginated<AdminWithdrawalRow>;
};

function MarkPaidDialog({ withdrawal }: { withdrawal: AdminWithdrawalRow }) {
    const [open, setOpen] = useState(false);
    const form = useForm({ reference: '' });

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm">Tandai dibayar</Button>
            </DialogTrigger>
            <DialogContent>
                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.submit(paid(withdrawal.id), {
                            preserveScroll: true,
                            onSuccess: () => setOpen(false),
                        });
                    }}
                >
                    <DialogHeader>
                        <DialogTitle>Tandai dibayar</DialogTitle>
                        <DialogDescription>
                            Pastikan {formatRupiah(withdrawal.amount)} sudah
                            ditransfer ke {withdrawal.bank}{' '}
                            {withdrawal.account_number} a.n.{' '}
                            {withdrawal.account_name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label htmlFor={`reference-${withdrawal.id}`}>
                            Referensi transfer
                        </Label>
                        <Input
                            id={`reference-${withdrawal.id}`}
                            value={form.data.reference}
                            onChange={(e) =>
                                form.setData('reference', e.target.value)
                            }
                        />
                        <InputError message={form.errors.reference} />
                    </div>
                    <DialogFooter>
                        <Button type="submit" disabled={form.processing}>
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function AdminWithdrawals({
    status,
    statuses,
    withdrawals,
}: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;

    return (
        <>
            <Head title="Penarikan" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Penarikan"
                    description="Setujui, transfer manual, lalu tandai dibayar. Biaya penarikan menjadi pendapatan platform."
                />
                <nav
                    aria-label="Status"
                    className="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1"
                >
                    {statuses.map((item) => (
                        <Link
                            key={item.value}
                            href={index({ query: { status: item.value } })}
                            className={cn(
                                'flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm whitespace-nowrap',
                                item.value === status
                                    ? 'border-brand bg-brand text-brand-foreground'
                                    : 'hover:bg-accent',
                            )}
                        >
                            {item.label}
                            <span className="font-mono text-xs tabular-nums opacity-80">
                                {item.count}
                            </span>
                        </Link>
                    ))}
                </nav>
                <InputError message={errors.withdrawal} />

                {withdrawals.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <Banknote className="size-8 text-muted-foreground" />
                        <p className="font-medium">
                            Tidak ada penarikan di status ini
                        </p>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {withdrawals.data.map((item) => (
                            <li
                                key={item.id}
                                className="grid gap-3 p-4 md:grid-cols-[1fr_auto] md:items-center"
                            >
                                <div className="grid gap-1 text-sm">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className="font-mono text-base font-semibold tabular-nums">
                                            {formatRupiah(item.amount)}
                                        </span>
                                        <StatusPill
                                            label={item.status_label}
                                            tone={statusTone(item.status)}
                                        />
                                        <span className="text-xs text-muted-foreground">
                                            + biaya {formatRupiah(item.fee)}
                                        </span>
                                    </div>
                                    <span>
                                        {item.user.name} · {item.user.email}
                                    </span>
                                    <span className="text-muted-foreground">
                                        {item.bank}{' '}
                                        <span className="font-mono">
                                            {item.account_number}
                                        </span>{' '}
                                        a.n. {item.account_name} · KYC:{' '}
                                        {item.kyc_name ?? '—'}
                                    </span>
                                    {item.name_mismatch && (
                                        <span className="flex items-center gap-1 text-xs text-pending">
                                            <TriangleAlert className="size-3.5" />{' '}
                                            Nama rekening berbeda dengan nama
                                            KYC — periksa sebelum transfer.
                                        </span>
                                    )}
                                    {item.created_at && (
                                        <span className="text-xs text-muted-foreground">
                                            Diajukan{' '}
                                            {formatDate(item.created_at)}
                                        </span>
                                    )}
                                    {item.disbursement_reference && (
                                        <span className="text-xs text-muted-foreground">
                                            Ref. {item.disbursement_reference}
                                        </span>
                                    )}
                                    {item.rejection_reason && (
                                        <span className="text-xs text-loss">
                                            {item.rejection_reason}
                                        </span>
                                    )}
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {item.status === 'requested' && (
                                        <ConfirmDialog
                                            trigger={
                                                <Button size="sm">
                                                    Setujui
                                                </Button>
                                            }
                                            title="Setujui penarikan?"
                                            description="Setelah disetujui, lakukan transfer lalu tandai dibayar."
                                            confirmLabel="Setujui"
                                            onConfirm={() =>
                                                router.visit(approve(item.id), {
                                                    preserveScroll: true,
                                                })
                                            }
                                        />
                                    )}
                                    {item.status === 'processing' && (
                                        <MarkPaidDialog withdrawal={item} />
                                    )}
                                    {(item.status === 'requested' ||
                                        item.status === 'processing') && (
                                        <ReasonDialog
                                            trigger={
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                >
                                                    {item.status === 'requested'
                                                        ? 'Tolak'
                                                        : 'Gagal transfer'}
                                                </Button>
                                            }
                                            title={
                                                item.status === 'requested'
                                                    ? 'Tolak penarikan'
                                                    : 'Tandai transfer gagal'
                                            }
                                            description="Saldo dikembalikan ke wallet pengguna. Alasan dikirim ke pengguna."
                                            confirmLabel="Simpan"
                                            action={reject(item.id)}
                                        />
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
                <Pagination paginator={withdrawals} />
            </div>
        </>
    );
}

AdminWithdrawals.layout = {
    breadcrumbs: [{ title: 'Penarikan', href: index() }],
};
