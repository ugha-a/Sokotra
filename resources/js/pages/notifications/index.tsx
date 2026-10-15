import { Head, Link, router } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import Heading from '@/components/heading';
import { Pagination } from '@/components/domain/pagination';
import { Button } from '@/components/ui/button';
import { formatRelative } from '@/lib/format';
import { cn } from '@/lib/utils';
import { index, open, readAll } from '@/routes/notifications';
import type { NotificationItem, Paginated } from '@/types';

export default function Notifications({
    notifications,
}: {
    notifications: Paginated<NotificationItem>;
}) {
    const hasUnread = notifications.data.some((item) => !item.read);

    return (
        <>
            <Head title="Notifikasi" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        title="Notifikasi"
                        description="Sinyal baru dan hasil dari trader yang kamu ikuti."
                    />
                    {hasUnread && (
                        <Button
                            variant="outline"
                            onClick={() =>
                                router.visit(readAll(), {
                                    preserveScroll: true,
                                })
                            }
                        >
                            Tandai semua dibaca
                        </Button>
                    )}
                </div>
                {notifications.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <Bell className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada notifikasi</p>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {notifications.data.map((item) => (
                            <li key={item.id}>
                                <Link
                                    href={open(item.id)}
                                    className={cn(
                                        'flex gap-3 p-4 transition-colors hover:bg-accent/50',
                                        !item.read && 'bg-brand/5',
                                    )}
                                >
                                    <span
                                        className={cn(
                                            'mt-1.5 size-2 shrink-0 rounded-full',
                                            item.read
                                                ? 'bg-transparent'
                                                : 'bg-brand',
                                        )}
                                        aria-label={
                                            item.read
                                                ? undefined
                                                : 'Belum dibaca'
                                        }
                                    />
                                    <span className="grid gap-0.5">
                                        <span
                                            className={cn(
                                                'text-sm',
                                                !item.read && 'font-medium',
                                            )}
                                        >
                                            {item.title}
                                        </span>
                                        <span className="text-sm text-muted-foreground">
                                            {item.body}
                                        </span>
                                        {item.created_at && (
                                            <span className="text-xs text-muted-foreground">
                                                {formatRelative(
                                                    item.created_at,
                                                )}
                                            </span>
                                        )}
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
                <Pagination paginator={notifications} />
            </div>
        </>
    );
}

Notifications.layout = {
    breadcrumbs: [{ title: 'Notifikasi', href: index() }],
};
