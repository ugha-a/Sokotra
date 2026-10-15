import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Landmark, Trash2 } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { ConfirmDialog } from '@/components/domain/confirm-dialog';
import { LedgerTable } from '@/components/domain/ledger-table';
import { MoneyInput } from '@/components/domain/money-input';
import { Pagination } from '@/components/domain/pagination';
import { StatusPill } from '@/components/domain/status-pill';
import { WalletBalance } from '@/components/domain/wallet-balance';
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
import { Spinner } from '@/components/ui/spinner';
import { formatDate, formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { show } from '@/routes/wallet';
import { destroy, store as storeAccount } from '@/routes/wallet/bank-accounts';
import { store as storeWithdrawal } from '@/routes/wallet/withdrawals';
import type { WalletPageProps } from '@/types';

function WithdrawForm({
    wallet,
    bankAccounts,
    rules,
    kycVerified,
}: Pick<WalletPageProps, 'wallet' | 'bankAccounts' | 'rules' | 'kycVerified'>) {
    const form = useForm({
        amount: '',
        bank_account_id: bankAccounts[0] ? String(bankAccounts[0].id) : '',
    });
    const errors = form.errors as Record<string, string | undefined>;
    const amount = Number(form.data.amount) || 0;
    const total = amount + rules.withdraw_fee;
    const disabled =
        !kycVerified || bankAccounts.length === 0 || form.processing;

    return (
        <section className="grid content-start gap-4 rounded-xl border p-4 md:p-6">
            <h2 className="font-medium">Tarik saldo</h2>
            {!kycVerified && (
                <p className="text-sm text-muted-foreground">
                    Penarikan butuh verifikasi identitas (KYC) yang sudah
                    disetujui.
                </p>
            )}
            {kycVerified && bankAccounts.length === 0 && (
                <p className="text-sm text-muted-foreground">
                    Tambahkan rekening tujuan dulu.
                </p>
            )}
            <div className="grid gap-2">
                <Label htmlFor="amount">Nominal</Label>
                <MoneyInput
                    id="amount"
                    min={rules.min_withdraw}
                    value={form.data.amount}
                    onChange={(e) => form.setData('amount', e.target.value)}
                />
                <InputError message={errors.amount} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="bank_account_id">Rekening tujuan</Label>
                <Select
                    value={form.data.bank_account_id}
                    onValueChange={(value) =>
                        form.setData('bank_account_id', value)
                    }
                >
                    <SelectTrigger id="bank_account_id">
                        <SelectValue placeholder="Pilih rekening" />
                    </SelectTrigger>
                    <SelectContent>
                        {bankAccounts.map((account) => (
                            <SelectItem
                                key={account.id}
                                value={String(account.id)}
                            >
                                {account.bank} · {account.account_number}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.bank_account_id} />
            </div>
            <dl className="grid gap-1 rounded-lg bg-muted/50 p-3 text-sm">
                <div className="flex justify-between">
                    <dt className="text-muted-foreground">
                        Ditransfer ke rekening
                    </dt>
                    <dd className="font-mono tabular-nums">
                        {formatRupiah(amount)}
                    </dd>
                </div>
                <div className="flex justify-between">
                    <dt className="text-muted-foreground">Biaya penarikan</dt>
                    <dd className="font-mono tabular-nums">
                        {formatRupiah(rules.withdraw_fee)}
                    </dd>
                </div>
                <div className="flex justify-between font-medium">
                    <dt>Dipotong dari saldo</dt>
                    <dd className="font-mono tabular-nums">
                        {formatRupiah(total)}
                    </dd>
                </div>
            </dl>
            <p className="text-xs text-muted-foreground">
                Minimum {formatRupiah(rules.min_withdraw)}. Saldo tersedia{' '}
                {formatRupiah(wallet.available_balance)}.
            </p>
            <InputError message={errors.withdrawal} />
            <ConfirmDialog
                trigger={
                    <Button className="justify-self-start" disabled={disabled}>
                        {form.processing && <Spinner />}
                        Ajukan penarikan
                    </Button>
                }
                title="Ajukan penarikan?"
                description={`${formatRupiah(total)} akan dikunci dari saldo tersedia sampai admin memproses transfer.`}
                confirmLabel="Ajukan"
                onConfirm={() =>
                    form.submit(storeWithdrawal(), {
                        preserveScroll: true,
                        onSuccess: () => form.reset('amount'),
                    })
                }
            />
        </section>
    );
}

function BankAccounts({
    bankAccounts,
    banks,
    rules,
}: Pick<WalletPageProps, 'bankAccounts' | 'banks' | 'rules'>) {
    const form = useForm({ bank_id: '', account_number: '', account_name: '' });
    const errors = form.errors as Record<string, string | undefined>;
    const pageErrors = usePage<{ errors: Record<string, string> }>().props
        .errors;

    return (
        <section className="grid content-start gap-4 rounded-xl border p-4 md:p-6">
            <h2 className="flex items-center gap-2 font-medium">
                <Landmark className="size-4" /> Rekening ({bankAccounts.length}/
                {rules.max_bank_accounts})
            </h2>
            <ul className="divide-y rounded-lg border">
                {bankAccounts.length === 0 && (
                    <li className="p-3 text-sm text-muted-foreground">
                        Belum ada rekening.
                    </li>
                )}
                {bankAccounts.map((account) => (
                    <li
                        key={account.id}
                        className="flex items-center justify-between gap-3 p-3 text-sm"
                    >
                        <span>
                            <span className="block font-medium">
                                {account.bank}
                            </span>
                            <span className="font-mono text-muted-foreground">
                                {account.account_number}
                            </span>{' '}
                            · {account.account_name}
                        </span>
                        <Button
                            variant="ghost"
                            size="icon"
                            aria-label="Hapus rekening"
                            onClick={() =>
                                router.visit(destroy(account.id), {
                                    preserveScroll: true,
                                })
                            }
                        >
                            <Trash2 />
                        </Button>
                    </li>
                ))}
            </ul>
            <InputError message={pageErrors.bank_account} />
            {bankAccounts.length < rules.max_bank_accounts && (
                <form
                    className="grid gap-3"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.submit(storeAccount(), {
                            preserveScroll: true,
                            onSuccess: () => form.reset(),
                        });
                    }}
                >
                    <Select
                        value={form.data.bank_id}
                        onValueChange={(value) =>
                            form.setData('bank_id', value)
                        }
                    >
                        <SelectTrigger aria-label="Bank">
                            <SelectValue placeholder="Pilih bank / e-wallet" />
                        </SelectTrigger>
                        <SelectContent>
                            {banks.map((bank) => (
                                <SelectItem
                                    key={bank.value}
                                    value={String(bank.value)}
                                >
                                    {bank.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.bank_id} />
                    <Input
                        placeholder="Nomor rekening"
                        inputMode="numeric"
                        className="font-mono"
                        value={form.data.account_number}
                        onChange={(e) =>
                            form.setData('account_number', e.target.value)
                        }
                    />
                    <InputError message={errors.account_number} />
                    <Input
                        placeholder="Nama pemilik (sesuai KTP)"
                        value={form.data.account_name}
                        onChange={(e) =>
                            form.setData('account_name', e.target.value)
                        }
                    />
                    <InputError message={errors.account_name} />
                    <Button
                        type="submit"
                        variant="outline"
                        className="justify-self-start"
                        disabled={form.processing}
                    >
                        Tambah rekening
                    </Button>
                </form>
            )}
        </section>
    );
}

export default function WalletPage(props: WalletPageProps) {
    const { wallet, transactions, withdrawals, rules } = props;

    return (
        <>
            <Head title="Wallet" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Wallet"
                    description="Gaji, reward, dan pendapatan kamu. Semua mutasi tercatat dan tidak bisa diubah."
                />
                <WalletBalance
                    wallet={wallet}
                    holdDays={rules.hold_period_days}
                />

                <div className="grid gap-6 lg:grid-cols-2">
                    <WithdrawForm {...props} />
                    <BankAccounts {...props} />
                </div>

                {withdrawals.length > 0 && (
                    <section className="grid gap-3">
                        <h2 className="font-medium">Penarikan terakhir</h2>
                        <ul className="divide-y rounded-xl border">
                            {withdrawals.map((item) => (
                                <li
                                    key={item.id}
                                    className="flex flex-wrap items-center justify-between gap-3 p-4 text-sm"
                                >
                                    <span>
                                        <span className="block font-mono font-medium tabular-nums">
                                            {formatRupiah(item.amount)}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {item.bank_account.bank} ·{' '}
                                            {item.bank_account.account_number}
                                            {item.created_at &&
                                                ` · ${formatDate(item.created_at)}`}
                                        </span>
                                        {item.rejection_reason && (
                                            <span className="block text-xs text-loss">
                                                {item.rejection_reason}
                                            </span>
                                        )}
                                    </span>
                                    <StatusPill
                                        label={item.status_label}
                                        tone={statusTone(item.status)}
                                    />
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                <section className="grid gap-3">
                    <h2 className="font-medium">Riwayat transaksi</h2>
                    <LedgerTable rows={transactions.data} />
                    <Pagination paginator={transactions} />
                </section>
            </div>
        </>
    );
}

WalletPage.layout = {
    breadcrumbs: [{ title: 'Wallet', href: show() }],
};
