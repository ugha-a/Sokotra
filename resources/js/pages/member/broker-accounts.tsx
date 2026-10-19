import { Head, Link, useForm } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
import { statusTone } from '@/lib/status';
import { index, store } from '@/routes/broker-accounts';
import { index as brokers } from '@/routes/brokers';

type Account = {
    id: number;
    broker: string;
    account_login: string;
    account_type: string | null;
    status: string;
    status_label: string;
    notes: string | null;
};
type BrokerOption = { value: number; label: string; account_types: string[] };

export default function BrokerAccounts({
    accounts,
    brokers: options,
}: {
    accounts: Account[];
    brokers: BrokerOption[];
}) {
    const form = useForm({
        broker_id: options[0] ? String(options[0].value) : '',
        account_login: '',
        account_type: '',
    });
    const errors = form.errors as Record<string, string | undefined>;
    const types =
        options.find((option) => String(option.value) === form.data.broker_id)
            ?.account_types ?? [];

    return (
        <>
            <Head title="Akun Broker" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Akun Broker"
                    description="Hubungkan akun trading yang kamu buka lewat Sotra. Admin mencocokkannya dengan laporan broker."
                />
                <div className="grid gap-6 lg:grid-cols-2">
                    <form
                        className="grid content-start gap-4 rounded-xl border p-4 md:p-6"
                        onSubmit={(event) => {
                            event.preventDefault();
                            form.submit(store(), {
                                preserveScroll: true,
                                onSuccess: () => form.reset('account_login'),
                            });
                        }}
                    >
                        <h2 className="font-medium">Hubungkan akun</h2>
                        <div className="grid gap-2">
                            <Label htmlFor="broker_id">Broker</Label>
                            <Select
                                value={form.data.broker_id}
                                onValueChange={(value) =>
                                    form.setData('broker_id', value)
                                }
                            >
                                <SelectTrigger id="broker_id">
                                    <SelectValue placeholder="Pilih broker" />
                                </SelectTrigger>
                                <SelectContent>
                                    {options.map((option) => (
                                        <SelectItem
                                            key={option.value}
                                            value={String(option.value)}
                                        >
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.broker_id} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="account_login">
                                Nomor akun (login MetaTrader)
                            </Label>
                            <Input
                                id="account_login"
                                className="font-mono"
                                value={form.data.account_login}
                                onChange={(e) =>
                                    form.setData(
                                        'account_login',
                                        e.target.value,
                                    )
                                }
                            />
                            <InputError message={errors.account_login} />
                        </div>
                        {types.length > 0 && (
                            <div className="grid gap-2">
                                <Label htmlFor="account_type">Tipe akun</Label>
                                <Select
                                    value={form.data.account_type}
                                    onValueChange={(value) =>
                                        form.setData('account_type', value)
                                    }
                                >
                                    <SelectTrigger id="account_type">
                                        <SelectValue placeholder="Pilih tipe" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {types.map((type) => (
                                            <SelectItem key={type} value={type}>
                                                {type}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}
                        <Button
                            type="submit"
                            className="justify-self-start"
                            disabled={form.processing}
                        >
                            Kirim
                        </Button>
                        <p className="text-xs text-muted-foreground">
                            Belum punya akun?{' '}
                            <Link
                                href={brokers()}
                                className="text-brand hover:underline"
                            >
                                Pilih broker partner
                            </Link>
                            .
                        </p>
                    </form>
                    <section className="grid content-start gap-3">
                        <h2 className="font-medium">Akun kamu</h2>
                        {accounts.length === 0 ? (
                            <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-8 text-center">
                                <Building2 className="size-8 text-muted-foreground" />
                                <p className="text-sm text-muted-foreground">
                                    Belum ada akun terhubung.
                                </p>
                            </div>
                        ) : (
                            <ul className="divide-y rounded-xl border">
                                {accounts.map((account) => (
                                    <li
                                        key={account.id}
                                        className="flex flex-wrap items-center justify-between gap-2 p-4 text-sm"
                                    >
                                        <span>
                                            <span className="block font-medium">
                                                {account.broker}
                                            </span>
                                            <span className="font-mono text-muted-foreground">
                                                {account.account_login}
                                            </span>
                                            {account.account_type && (
                                                <span className="text-muted-foreground">
                                                    {' '}
                                                    · {account.account_type}
                                                </span>
                                            )}
                                            {account.notes && (
                                                <span className="block text-xs text-loss">
                                                    {account.notes}
                                                </span>
                                            )}
                                        </span>
                                        <StatusPill
                                            label={account.status_label}
                                            tone={statusTone(account.status)}
                                        />
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                </div>
            </div>
        </>
    );
}

BrokerAccounts.layout = {
    breadcrumbs: [{ title: 'Akun Broker', href: index() }],
};
