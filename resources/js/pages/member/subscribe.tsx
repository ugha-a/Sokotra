import { Head, Link, useForm } from '@inertiajs/react';
import { Crown } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { RiskDisclaimer } from '@/components/domain/risk-disclaimer';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatRupiah } from '@/lib/format';
import { cn } from '@/lib/utils';
import { store } from '@/routes/subscriptions';
import { show as traderProfile } from '@/routes/traders';

type Props = {
    trader: { id: number; name: string; slug: string };
    plan: {
        monthly_price: number;
        description: string | null;
        is_open: boolean;
    } | null;
    durations: number[];
    riskDisclaimer: string;
};

export default function Subscribe({
    trader,
    plan,
    durations,
    riskDisclaimer,
}: Props) {
    const form = useForm({ months: durations[0] ?? 1, accept_risk: false });
    const errors = form.errors as Record<string, string | undefined>;

    return (
        <>
            <Head title={`Langganan ${trader.name}`} />
            <div className="mx-auto flex w-full max-w-xl flex-col gap-6 p-4 md:p-6">
                <Heading
                    title={`Langganan channel ${trader.name}`}
                    description="Sinyal premium terbuka real-time selama masa langganan. Hasil sinyal tetap dinilai otomatis, termasuk yang kalah."
                />
                {!plan || !plan.is_open ? (
                    <p className="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
                        Channel ini sedang tidak menerima subscriber baru.
                    </p>
                ) : (
                    <form
                        className="grid gap-5 rounded-xl border p-4 md:p-6"
                        onSubmit={(event) => {
                            event.preventDefault();
                            form.submit(store(trader.id));
                        }}
                    >
                        {plan.description && (
                            <p className="text-sm whitespace-pre-line">
                                {plan.description}
                            </p>
                        )}
                        <div className="grid gap-2">
                            <span className="text-sm font-medium">Durasi</span>
                            <div className="grid grid-cols-3 gap-2">
                                {durations.map((months) => (
                                    <button
                                        key={months}
                                        type="button"
                                        aria-pressed={
                                            form.data.months === months
                                        }
                                        onClick={() =>
                                            form.setData('months', months)
                                        }
                                        className={cn(
                                            'grid gap-0.5 rounded-lg border p-3 text-left transition-colors',
                                            form.data.months === months
                                                ? 'border-brand bg-brand/10'
                                                : 'hover:bg-accent',
                                        )}
                                    >
                                        <span className="text-sm font-medium">
                                            {months} bulan
                                        </span>
                                        <span className="font-mono text-xs text-muted-foreground tabular-nums">
                                            {formatRupiah(
                                                plan.monthly_price * months,
                                            )}
                                        </span>
                                    </button>
                                ))}
                            </div>
                            <InputError message={errors.months} />
                        </div>
                        <RiskDisclaimer text={riskDisclaimer} />
                        <div className="grid gap-1">
                            <div className="flex items-start gap-2">
                                <Checkbox
                                    id="accept_risk"
                                    checked={form.data.accept_risk}
                                    onCheckedChange={(checked) =>
                                        form.setData(
                                            'accept_risk',
                                            checked === true,
                                        )
                                    }
                                />
                                <Label
                                    htmlFor="accept_risk"
                                    className="leading-snug font-normal"
                                >
                                    Saya memahami bahwa trading berisiko tinggi,
                                    analisa ini tidak menjamin profit, dan saya
                                    bertanggung jawab atas keputusan trading
                                    saya sendiri.
                                </Label>
                            </div>
                            <InputError message={errors.accept_risk} />
                        </div>
                        <InputError
                            message={errors.subscription ?? errors.checkout}
                        />
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <span className="font-mono text-lg font-semibold tabular-nums">
                                {formatRupiah(
                                    plan.monthly_price * form.data.months,
                                )}
                            </span>
                            <Button type="submit" disabled={form.processing}>
                                {form.processing ? <Spinner /> : <Crown />}{' '}
                                Lanjut ke pembayaran
                            </Button>
                        </div>
                    </form>
                )}
                <Link
                    href={traderProfile(trader.slug)}
                    className="text-sm text-muted-foreground hover:underline"
                >
                    ← Kembali ke profil {trader.name}
                </Link>
            </div>
        </>
    );
}
