import { Link, usePage, usePoll } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/notifications';

const POLL_MS = 60_000;

/**
 * Header bell with the unread count, refreshed by polling only the shared prop.
 */
export function NotificationBell() {
    const { notifications } = usePage().props;
    usePoll(POLL_MS, { only: ['notifications'] }, { keepAlive: false });

    const unread = notifications?.unread ?? 0;

    return (
        <Button variant="ghost" size="icon" asChild className="relative">
            <Link
                href={index()}
                aria-label={
                    unread > 0
                        ? `Notifikasi, ${unread} belum dibaca`
                        : 'Notifikasi'
                }
            >
                <Bell />
                {unread > 0 && (
                    <span className="absolute -top-0.5 -right-0.5 min-w-4 rounded-full bg-brand px-1 text-center font-mono text-xs leading-4 text-brand-foreground tabular-nums">
                        {unread > 99 ? '99+' : unread}
                    </span>
                )}
            </Link>
        </Button>
    );
}
