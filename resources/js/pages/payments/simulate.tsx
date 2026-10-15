import { Head, useForm } from '@inertiajs/react';
import { FlaskConical } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/format';
import { complete } from '@/routes/payments/simulate';

type Payment = { id: number; amount: number; description: string };

/** Local development only: stands in for the payment provider's checkout page. */
export default function SimulatePayment({ payment }: { payment: Payment }) {
    const form = useForm({ outcome: 'paid' });

    const pay = (outcome: 'paid' | 'failed') => {
        form.transform(() => ({ outcome }));
        form.submit(complete(payment.id));
    };

    return (
        <>
            <Head title="Simulasi Pembayaran" />
            <div className="mx-auto flex w-full max-w-md flex-col gap-6 p-6">
                <div className="flex items-center gap-2 rounded-lg border border-pending/40 bg-pending-soft p-3 text-sm">
                    <FlaskConical className="size-4 text-pending" /> Mode
                    pengembangan: tidak ada uang yang ditarik.
                </div>
                <Heading
                    title="Simulasi Pembayaran"
                    description={`${payment.description} · ${formatRupiah(payment.amount)}`}
                />
                <div className="flex gap-2">
                    <Button
                        onClick={() => pay('paid')}
                        disabled={form.processing}
                    >
                        Bayar (berhasil)
                    </Button>
                    <Button
                        variant="outline"
                        onClick={() => pay('failed')}
                        disabled={form.processing}
                    >
                        Gagalkan
                    </Button>
                </div>
            </div>
        </>
    );
}
