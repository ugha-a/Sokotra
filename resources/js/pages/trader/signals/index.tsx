import { Head, Link, router, usePage } from '@inertiajs/react';
import { ListChecks, SquarePen } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { ConfirmDialog } from '@/components/domain/confirm-dialog';
import { Pagination } from '@/components/domain/pagination';
import { SignalCard } from '@/components/domain/signal-card';
import { SignalFilters } from '@/components/domain/signal-filters';
import { Button } from '@/components/ui/button';
import { cancel, create, index } from '@/routes/trader/signals';
import type {
    MasterOption,
    Paginated,
    SignalFilters as Filters,
    SignalItem,
} from '@/types';

type Props = {
    filters: Filters;
    statusOptions: MasterOption[];
    signals: Paginated<SignalItem>;
};

export default function TraderSignals({
    filters,
    statusOptions,
    signals,
}: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;

    return (
        <>
            <Head title="Sinyal Saya" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        title="Sinyal Saya"
                        description="Sinyal yang sudah dipublish tidak bisa diubah. Hanya sinyal pending yang bisa dibatalkan."
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <SquarePen /> Posting Sinyal
                        </Link>
                    </Button>
                </div>

                <InputError message={errors.signal} />

                <SignalFilters
                    url={index().url}
                    filters={filters}
                    statusOptions={statusOptions}
                />

                {signals.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <ListChecks className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada sinyal</p>
                        <p className="text-sm text-muted-foreground">
                            Sinyal pertama kamu akan muncul di sini.
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {signals.data.map((signal) => (
                            <SignalCard
                                key={signal.id}
                                signal={signal}
                                actions={
                                    signal.status === 'pending' && (
                                        <ConfirmDialog
                                            trigger={
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                >
                                                    Batalkan
                                                </Button>
                                            }
                                            title="Batalkan sinyal pending?"
                                            description="Sinyal yang dibatalkan tidak dihitung ke statistik, tetapi jumlah pembatalan per minggu dibatasi."
                                            confirmLabel="Batalkan sinyal"
                                            tone="destructive"
                                            onConfirm={() =>
                                                router.visit(
                                                    cancel(signal.id),
                                                    { preserveScroll: true },
                                                )
                                            }
                                        />
                                    )
                                }
                            />
                        ))}
                    </div>
                )}

                <Pagination paginator={signals} />
            </div>
        </>
    );
}

TraderSignals.layout = {
    breadcrumbs: [{ title: 'Sinyal Saya', href: index() }],
};
