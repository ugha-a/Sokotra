import { Head, Link } from '@inertiajs/react';
import { ChevronRight, UserCheck } from 'lucide-react';
import Heading from '@/components/heading';
import { Pagination } from '@/components/domain/pagination';
import { RankBadge } from '@/components/domain/rank-badge';
import { StatusPill } from '@/components/domain/status-pill';
import { formatDate } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { cn } from '@/lib/utils';
import { index, show } from '@/routes/admin/traders';
import type {
    Paginated,
    TraderApplicationRow,
    TraderStatusFilter,
} from '@/types';

type Props = {
    status: string;
    statuses: TraderStatusFilter[];
    profiles: Paginated<TraderApplicationRow>;
};

export default function TraderApplications({
    status,
    statuses,
    profiles,
}: Props) {
    return (
        <>
            <Head title="Aplikasi Trader" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Aplikasi Trader"
                    description="Verifikasi KYC lebih dulu, lalu setujui atau tolak aplikasi."
                />

                <nav
                    aria-label="Status"
                    className="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1"
                >
                    {statuses.map((item) => (
                        <Link
                            key={item.value}
                            href={index({ query: { status: item.value } })}
                            preserveScroll
                            className={cn(
                                'flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm whitespace-nowrap transition-colors',
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

                {profiles.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <UserCheck className="size-8 text-muted-foreground" />
                        <p className="font-medium">
                            Tidak ada aplikasi di status ini
                        </p>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {profiles.data.map((profile) => (
                            <li key={profile.id}>
                                <Link
                                    href={show(profile.id)}
                                    className="flex items-center gap-4 p-4 transition-colors hover:bg-accent/50"
                                >
                                    <div className="grid flex-1 gap-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span className="font-medium">
                                                {profile.display_name}
                                            </span>
                                            {profile.rank && (
                                                <RankBadge
                                                    name={profile.rank.name}
                                                    color={profile.rank.color}
                                                />
                                            )}
                                        </div>
                                        <p className="text-sm text-muted-foreground">
                                            {profile.user.name} ·{' '}
                                            {profile.user.email} ·{' '}
                                            {profile.trading_style}
                                        </p>
                                    </div>
                                    <div className="hidden flex-col items-end gap-1 sm:flex">
                                        {profile.kyc_status_label && (
                                            <StatusPill
                                                label={`KYC: ${profile.kyc_status_label}`}
                                                tone={statusTone(
                                                    profile.kyc_status,
                                                )}
                                            />
                                        )}
                                        {profile.applied_at && (
                                            <span className="text-xs text-muted-foreground">
                                                {formatDate(profile.applied_at)}
                                            </span>
                                        )}
                                    </div>
                                    <ChevronRight className="size-4 text-muted-foreground" />
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}

                <Pagination paginator={profiles} />
            </div>
        </>
    );
}

TraderApplications.layout = {
    breadcrumbs: [{ title: 'Aplikasi Trader', href: index() }],
};
