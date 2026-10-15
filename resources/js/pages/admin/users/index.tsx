import { Head, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Pagination } from '@/components/domain/pagination';
import { ReasonDialog } from '@/components/domain/reason-dialog';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatRelative } from '@/lib/format';
import { flag, index, unflag } from '@/routes/admin/users';
import type { Paginated } from '@/types';

type Row = {
    id: number;
    name: string;
    email: string;
    roles: string[];
    trader_status: string | null;
    is_fraud_flagged: boolean;
    email_verified: boolean;
    last_active_at: string | null;
};

export default function AdminUsers({
    search,
    users,
}: {
    search: string | null;
    users: Paginated<Row>;
}) {
    const [term, setTerm] = useState(search ?? '');

    return (
        <>
            <Head title="Pengguna" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Pengguna"
                    description="Akun yang ditandai fraud tidak dihitung sebagai follower valid dan tidak menerima gaji."
                />
                <form
                    className="flex max-w-md gap-2"
                    onSubmit={(event) => {
                        event.preventDefault();
                        router.get(
                            index().url,
                            { search: term || undefined },
                            { preserveState: true, replace: true },
                        );
                    }}
                >
                    <Input
                        placeholder="Cari nama atau email"
                        value={term}
                        onChange={(e) => setTerm(e.target.value)}
                    />
                    <Button type="submit" variant="outline" aria-label="Cari">
                        <Search />
                    </Button>
                </form>
                <ul className="divide-y rounded-xl border">
                    {users.data.length === 0 && (
                        <li className="p-6 text-center text-sm text-muted-foreground">
                            Tidak ada pengguna.
                        </li>
                    )}
                    {users.data.map((user) => (
                        <li
                            key={user.id}
                            className="flex flex-wrap items-center justify-between gap-3 p-4 text-sm"
                        >
                            <span>
                                <span className="block font-medium">
                                    {user.name}
                                </span>
                                <span className="text-xs text-muted-foreground">
                                    {user.email} · {user.roles.join(', ')}
                                    {user.trader_status &&
                                        ` · trader ${user.trader_status.toLowerCase()}`}
                                    {user.last_active_at &&
                                        ` · aktif ${formatRelative(user.last_active_at)}`}
                                </span>
                            </span>
                            <span className="flex items-center gap-2">
                                {!user.email_verified && (
                                    <StatusPill
                                        label="Email belum verifikasi"
                                        tone="neutral"
                                    />
                                )}
                                {user.is_fraud_flagged ? (
                                    <>
                                        <StatusPill
                                            label="Ditandai fraud"
                                            tone="loss"
                                        />
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            onClick={() =>
                                                router.visit(unflag(user.id), {
                                                    preserveScroll: true,
                                                })
                                            }
                                        >
                                            Hapus tanda
                                        </Button>
                                    </>
                                ) : (
                                    <ReasonDialog
                                        trigger={
                                            <Button size="sm" variant="outline">
                                                Tandai fraud
                                            </Button>
                                        }
                                        title={`Tandai ${user.name}?`}
                                        description="Akun tidak lagi dihitung sebagai follower valid. Alasan tercatat di audit log."
                                        confirmLabel="Tandai"
                                        action={flag(user.id)}
                                    />
                                )}
                            </span>
                        </li>
                    ))}
                </ul>
                <Pagination paginator={users} />
            </div>
        </>
    );
}

AdminUsers.layout = {
    breadcrumbs: [{ title: 'Pengguna', href: index() }],
};
