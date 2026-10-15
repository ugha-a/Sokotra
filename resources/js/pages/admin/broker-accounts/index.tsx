import { Head, router } from '@inertiajs/react';
import Heading from '@/components/heading';
import { ConfirmDialog } from '@/components/domain/confirm-dialog';
import { Pagination } from '@/components/domain/pagination';
import { ReasonDialog } from '@/components/domain/reason-dialog';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { index, link, reject } from '@/routes/admin/broker-accounts';
import type { Paginated } from '@/types';

type Row = {
    id: number;
    user: { name: string; email: string };
    broker: string;
    account_login: string;
    account_type: string | null;
    status: string;
    status_label: string;
    notes: string | null;
    created_at: string | null;
};

export default function AdminBrokerAccounts({
    accounts,
}: {
    accounts: Paginated<Row>;
}) {
    return (
        <>
            <Head title="Akun Broker" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Akun Broker"
                    description="Cocokkan nomor akun dengan laporan IB broker sebelum menghubungkan. Pengajuan baru tampil paling atas."
                />
                <ul className="divide-y rounded-xl border">
                    {accounts.data.length === 0 && (
                        <li className="p-6 text-center text-sm text-muted-foreground">
                            Belum ada pengajuan.
                        </li>
                    )}
                    {accounts.data.map((account) => (
                        <li
                            key={account.id}
                            className="flex flex-wrap items-center justify-between gap-3 p-4 text-sm"
                        >
                            <span>
                                <span className="block font-medium">
                                    {account.broker} ·{' '}
                                    <span className="font-mono">
                                        {account.account_login}
                                    </span>
                                    {account.account_type && (
                                        <span className="font-normal text-muted-foreground">
                                            {' '}
                                            ({account.account_type})
                                        </span>
                                    )}
                                </span>
                                <span className="text-xs text-muted-foreground">
                                    {account.user.name} · {account.user.email}
                                    {account.created_at &&
                                        ` · ${formatDate(account.created_at)}`}
                                </span>
                                {account.notes && (
                                    <span className="block text-xs text-loss">
                                        {account.notes}
                                    </span>
                                )}
                            </span>
                            <span className="flex items-center gap-2">
                                <StatusPill
                                    label={account.status_label}
                                    tone={statusTone(account.status)}
                                />
                                {account.status === 'submitted' && (
                                    <>
                                        <ConfirmDialog
                                            trigger={
                                                <Button size="sm">
                                                    Hubungkan
                                                </Button>
                                            }
                                            title="Hubungkan akun?"
                                            description="Pastikan login ada di laporan IB broker."
                                            confirmLabel="Hubungkan"
                                            onConfirm={() =>
                                                router.visit(link(account.id), {
                                                    preserveScroll: true,
                                                })
                                            }
                                        />
                                        <ReasonDialog
                                            trigger={
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                >
                                                    Tolak
                                                </Button>
                                            }
                                            title="Tolak akun"
                                            description="Alasan dikirim ke member."
                                            confirmLabel="Tolak"
                                            action={reject(account.id)}
                                        />
                                    </>
                                )}
                            </span>
                        </li>
                    ))}
                </ul>
                <Pagination paginator={accounts} />
            </div>
        </>
    );
}

AdminBrokerAccounts.layout = {
    breadcrumbs: [{ title: 'Akun Broker', href: index() }],
};
