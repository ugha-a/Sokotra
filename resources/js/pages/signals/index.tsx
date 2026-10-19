import { Head } from '@inertiajs/react';
import { Radio } from 'lucide-react';
import Heading from '@/components/heading';
import { Pagination } from '@/components/domain/pagination';
import { RiskDisclaimer } from '@/components/domain/risk-disclaimer';
import { SignalCard } from '@/components/domain/signal-card';
import { SignalFilters } from '@/components/domain/signal-filters';
import { index } from '@/routes/signals';
import type {
    MasterOption,
    Paginated,
    SignalFilters as Filters,
    SignalItem,
} from '@/types';

type Props = {
    filters: Filters;
    statusOptions: MasterOption[];
    instrumentOptions: MasterOption[];
    signals: Paginated<SignalItem>;
    riskDisclaimer: string;
};

export default function SignalFeed({
    filters,
    statusOptions,
    instrumentOptions,
    signals,
    riskDisclaimer,
}: Props) {
    return (
        <>
            <Head title="Sinyal" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Sinyal"
                    description="Semua analisa dari trader SooTa. Hasilnya dinilai otomatis dari harga pasar, termasuk yang kalah."
                />
                <RiskDisclaimer text={riskDisclaimer} />
                <SignalFilters
                    url={index().url}
                    filters={filters}
                    statusOptions={statusOptions}
                    instrumentOptions={instrumentOptions}
                />

                {signals.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <Radio className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada sinyal</p>
                        <p className="text-sm text-muted-foreground">
                            Coba ubah filter atau kembali nanti.
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {signals.data.map((signal) => (
                            <SignalCard key={signal.id} signal={signal} />
                        ))}
                    </div>
                )}

                <Pagination paginator={signals} />
            </div>
        </>
    );
}

SignalFeed.layout = {
    breadcrumbs: [{ title: 'Sinyal', href: index() }],
};
