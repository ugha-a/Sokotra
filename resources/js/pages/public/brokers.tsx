import { Head } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import Heading from '@/components/heading';
import { BrokerCard } from '@/components/domain/broker-card';
import type { BrokerCardData } from '@/components/domain/broker-card';
import { open } from '@/routes/brokers';

export default function Brokers({ brokers }: { brokers: BrokerCardData[] }) {
    return (
        <>
            <Head title="Broker Partner" />
            <div className="grid gap-6">
                <Heading
                    title="Broker Partner"
                    description="Sokotra hanya bermitra dengan broker berizin Bappebti dan tidak pernah mengelola dana trading kamu. Setelah membuka akun, hubungkan nomor akunnya di menu Akun Broker."
                />
                {brokers.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
                        <Building2 className="size-8 text-muted-foreground" />
                        <p className="font-medium">Belum ada broker partner</p>
                    </div>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2">
                        {brokers.map((broker) => (
                            <BrokerCard
                                key={broker.id}
                                broker={broker}
                                openUrl={open(broker.id).url}
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
