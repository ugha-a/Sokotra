import { Head } from '@inertiajs/react';
import { History } from 'lucide-react';
import Heading from '@/components/heading';
import { Pagination } from '@/components/domain/pagination';
import { formatDate } from '@/lib/format';
import { index } from '@/routes/admin/audit-logs';
import type { AuditLog, Paginated } from '@/types';

const ACTION_LABELS: Record<string, string> = {
    'master.created': 'Tambah master',
    'master.updated': 'Ubah master',
    'master.toggled': 'Aktif/nonaktif master',
    'settings.updated': 'Ubah pengaturan',
};

function subject(log: AuditLog): string {
    if (!log.auditable_type) {
        return 'Pengaturan platform';
    }

    return `${log.auditable_type.split('\\').pop()} #${log.auditable_id}`;
}

function show(value: unknown): string {
    if (value === null || value === undefined) {
        return '—';
    }

    return typeof value === 'string' ? value : JSON.stringify(value);
}

function Changes({ log }: { log: AuditLog }) {
    const keys = Object.keys(log.new_values ?? {});

    return (
        <ul className="grid gap-1 font-mono text-xs">
            {keys.map((key) => (
                <li key={key} className="break-all">
                    <span className="text-muted-foreground">{key}:</span>{' '}
                    {log.old_values && key in log.old_values && (
                        <>
                            <span className="text-loss line-through">
                                {show(log.old_values[key])}
                            </span>{' '}
                            →{' '}
                        </>
                    )}
                    <span>{show(log.new_values?.[key])}</span>
                </li>
            ))}
        </ul>
    );
}

export default function AuditLogs({ logs }: { logs: Paginated<AuditLog> }) {
    return (
        <>
            <Head title="Audit Log" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Audit Log"
                    description="Semua perubahan oleh admin, terbaru di atas. Log tidak bisa diubah atau dihapus."
                />

                {logs.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <History className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada perubahan</p>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {logs.data.map((log) => (
                            <li
                                key={log.id}
                                className="grid gap-2 p-4 md:grid-cols-[12rem_1fr_2fr] md:gap-4"
                            >
                                <div className="text-sm">
                                    <p className="font-medium">
                                        {ACTION_LABELS[log.action] ??
                                            log.action}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {formatDate(log.created_at)}
                                    </p>
                                </div>
                                <div className="text-sm">
                                    <p>{subject(log)}</p>
                                    <p className="text-xs text-muted-foreground">
                                        oleh {log.user?.name ?? 'sistem'}
                                    </p>
                                </div>
                                <Changes log={log} />
                            </li>
                        ))}
                    </ul>
                )}

                <Pagination paginator={logs} />
            </div>
        </>
    );
}

AuditLogs.layout = {
    breadcrumbs: [{ title: 'Audit Log', href: index() }],
};
