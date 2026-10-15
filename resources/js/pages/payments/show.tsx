import { Head, Link } from '@inertiajs/react';
import { CircleCheck, Clock, CircleX } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/format';
import { index as subscriptions } from '@/routes/subscriptions';

type Payment = {
    id: number;
    amount: number;
    status: string;
    status_label: string;
    description: string;
    checkout_url: string | null;
};

export default function PaymentStatus({ payment }: { payment: Payment }) {
    const Icon =
        payment.status === 'paid'
            ? CircleCheck
            : payment.status === 'pending'
              ? Clock
              : CircleX;
    const tone =
        payment.status === 'paid'
            ? 'text-profit'
            : payment.status === 'pending'
              ? 'text-pending'
              : 'text-loss';

    return (
        <>
            <Head title="Status Pembayaran" />
            <div className="mx-auto flex w-full max-w-md flex-col items-center gap-4 p-6 text-center">
                <Icon className={`size-12 ${tone}`} />
                <Heading
                    title={payment.status_label}
                    description={`${payment.description} · ${formatRupiah(payment.amount)}`}
                />
                {payment.status === 'pending' && payment.checkout_url && (
                    <Button asChild>
                        <a href={payment.checkout_url}>Lanjutkan pembayaran</a>
                    </Button>
                )}
                <Button variant="outline" asChild>
                    <Link href={subscriptions()}>Lihat langganan</Link>
                </Button>
            </div>
        </>
    );
}
