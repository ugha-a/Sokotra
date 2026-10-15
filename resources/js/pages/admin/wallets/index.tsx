import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Pagination } from '@/components/domain/pagination';
import { WalletBalance } from '@/components/domain/wallet-balance';
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
import { formatRupiah } from '@/lib/format';
import { adjust, index } from '@/routes/admin/wallets';
import type { Paginated, WalletSummary } from '@/types';

type WalletRow = WalletSummary & {
    user: { id: number; name: string; email: string };
};

function AdjustDialog({ row }: { row: WalletRow }) {
    const [open, setOpen] = useState(false);
    const form = useForm({ amount: '', note: '' });

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm" variant="outline">
                    Koreksi
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.submit(adjust(row.user.id), {
                            preserveScroll: true,
                            onSuccess: () => {
                                form.reset();
                                setOpen(false);
                            },
                        });
                    }}
                >
                    <DialogHeader>
                        <DialogTitle>Koreksi saldo {row.user.name}</DialogTitle>
                        <DialogDescription>
                            Nominal positif menambah, negatif mengurangi saldo
                            tersedia. Tercatat di ledger dan audit log.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label htmlFor={`amount-${row.user.id}`}>
                            Nominal (Rp)
                        </Label>
                        <Input
                            id={`amount-${row.user.id}`}
                            type="number"
                            step={1}
                            className="font-mono"
                            value={form.data.amount}
                            onChange={(e) =>
                                form.setData('amount', e.target.value)
                            }
                        />
                        <InputError message={form.errors.amount} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor={`note-${row.user.id}`}>Catatan</Label>
                        <Input
                            id={`note-${row.user.id}`}
                            value={form.data.note}
                            onChange={(e) =>
                                form.setData('note', e.target.value)
                            }
                        />
                        <InputError message={form.errors.note} />
                    </div>
                    <DialogFooter>
                        <Button type="submit" disabled={form.processing}>
                            Simpan koreksi
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function AdminWallets({
    wallets,
    totals,
}: {
    wallets: Paginated<WalletRow>;
    totals: WalletSummary;
}) {
    return (
        <>
            <Head title="Wallet Pengguna" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Wallet Pengguna"
                    description="Total saldo di platform dan koreksi manual per pengguna."
                />
                <WalletBalance wallet={totals} holdDays={0} />
                <ul className="divide-y rounded-xl border">
                    {wallets.data.length === 0 && (
                        <li className="p-6 text-center text-sm text-muted-foreground">
                            Belum ada wallet.
                        </li>
                    )}
                    {wallets.data.map((row) => (
                        <li
                            key={row.user.id}
                            className="flex flex-wrap items-center justify-between gap-3 p-4 text-sm"
                        >
                            <span>
                                <span className="block font-medium">
                                    {row.user.name}
                                </span>
                                <span className="text-xs text-muted-foreground">
                                    {row.user.email}
                                </span>
                            </span>
                            <span className="flex flex-wrap items-center gap-4 font-mono tabular-nums">
                                <span>
                                    {formatRupiah(row.available_balance)}
                                </span>
                                <span className="text-muted-foreground">
                                    tertahan {formatRupiah(row.pending_balance)}
                                </span>
                                <span className="text-muted-foreground">
                                    diproses {formatRupiah(row.locked_balance)}
                                </span>
                                <AdjustDialog row={row} />
                            </span>
                        </li>
                    ))}
                </ul>
                <Pagination paginator={wallets} />
            </div>
        </>
    );
}

AdminWallets.layout = {
    breadcrumbs: [{ title: 'Wallet Pengguna', href: index() }],
};
