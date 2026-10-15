import { Head, Link, useForm } from '@inertiajs/react';
import { ShieldAlert } from 'lucide-react';
import Heading from '@/components/heading';
import { Pagination } from '@/components/domain/pagination';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { cn } from '@/lib/utils';
import { confirm, dismiss, index } from '@/routes/admin/fraud-flags';
import type { Paginated } from '@/types';

type Flag = {
    id: number;
    type: string;
    type_label: string;
    status: string;
    status_label: string;
    users: { id: number; name: string; email: string | null }[];
    signals: { id: number; label: string }[];
    details: Record<string, unknown> | null;
    notes: string | null;
    created_at: string | null;
};

const CONSEQUENCES: Record<string, string> = {
    hedging:
        'Terbukti: kedua trader ditandai (keluar dari leaderboard & payroll). Diabaikan: sinyal kembali dihitung untuk payroll.',
    follower_farm:
        'Terbukti: akun-akun ini ditandai fraud dan tidak lagi dihitung sebagai follower valid.',
};

function Resolve({ flag }: { flag: Flag }) {
    const form = useForm({ notes: '' });

    return (
        <div className="grid gap-2">
            <Input
                placeholder="Catatan keputusan"
                value={form.data.notes}
                onChange={(e) => form.setData('notes', e.target.value)}
            />
            <div className="flex gap-2">
                <Button
                    size="sm"
                    variant="destructive"
                    disabled={form.processing}
                    onClick={() =>
                        form.submit(confirm(flag.id), { preserveScroll: true })
                    }
                >
                    Terbukti
                </Button>
                <Button
                    size="sm"
                    variant="outline"
                    disabled={form.processing}
                    onClick={() =>
                        form.submit(dismiss(flag.id), { preserveScroll: true })
                    }
                >
                    Abaikan
                </Button>
            </div>
        </div>
    );
}

export default function FraudFlags({
    status,
    statuses,
    flags,
}: {
    status: string;
    statuses: { value: string; label: string }[];
    flags: Paginated<Flag>;
}) {
    return (
        <>
            <Head title="Fraud Flags" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Fraud Flags"
                    description="Hedging dideteksi saat sinyal dipublish; follower palsu dipindai setiap hari. Ambang batas diatur di Pengaturan → Anti-Fraud."
                />
                <nav aria-label="Status" className="flex gap-2">
                    {statuses.map((item) => (
                        <Link
                            key={item.value}
                            href={index({ query: { status: item.value } })}
                            className={cn(
                                'rounded-full border px-3 py-1.5 text-sm',
                                item.value === status
                                    ? 'border-brand bg-brand text-brand-foreground'
                                    : 'hover:bg-accent',
                            )}
                        >
                            {item.label}
                        </Link>
                    ))}
                </nav>
                {flags.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <ShieldAlert className="size-8 text-muted-foreground" />
                        <p className="font-medium">
                            Tidak ada flag di status ini
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {flags.data.map((flag) => (
                            <article
                                key={flag.id}
                                className="grid content-start gap-3 rounded-xl border p-4 text-sm"
                            >
                                <header className="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 className="font-medium">
                                            {flag.type_label}
                                        </h3>
                                        {flag.created_at && (
                                            <p className="text-xs text-muted-foreground">
                                                {formatDate(flag.created_at)}
                                            </p>
                                        )}
                                    </div>
                                    <StatusPill
                                        label={flag.status_label}
                                        tone={statusTone(flag.status)}
                                    />
                                </header>
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Akun terlibat ({flag.users.length})
                                    </p>
                                    <ul className="mt-1 grid gap-0.5">
                                        {flag.users.slice(0, 8).map((user) => (
                                            <li key={user.id}>
                                                {user.name}
                                                {user.email && (
                                                    <span className="text-muted-foreground">
                                                        {' '}
                                                        · {user.email}
                                                    </span>
                                                )}
                                            </li>
                                        ))}
                                        {flag.users.length > 8 && (
                                            <li className="text-muted-foreground">
                                                +{flag.users.length - 8} lainnya
                                            </li>
                                        )}
                                    </ul>
                                </div>
                                {flag.signals.length > 0 && (
                                    <div>
                                        <p className="text-xs text-muted-foreground">
                                            Sinyal
                                        </p>
                                        <ul className="mt-1 grid gap-0.5 font-mono text-xs">
                                            {flag.signals.map((signal) => (
                                                <li key={signal.id}>
                                                    {signal.label}
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                                {typeof flag.details?.ip === 'string' && (
                                    <p className="text-xs text-muted-foreground">
                                        IP:{' '}
                                        <span className="font-mono">
                                            {flag.details.ip}
                                        </span>
                                    </p>
                                )}
                                <p className="text-xs text-muted-foreground">
                                    {CONSEQUENCES[flag.type]}
                                </p>
                                {flag.notes && (
                                    <p className="text-xs">
                                        Catatan: {flag.notes}
                                    </p>
                                )}
                                {flag.status === 'open' && (
                                    <Resolve flag={flag} />
                                )}
                            </article>
                        ))}
                    </div>
                )}
                <Pagination paginator={flags} />
            </div>
        </>
    );
}

FraudFlags.layout = {
    breadcrumbs: [{ title: 'Fraud Flags', href: index() }],
};
