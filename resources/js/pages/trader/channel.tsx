import { Head, useForm } from '@inertiajs/react';
import { Crown, TriangleAlert } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { MoneyInput } from '@/components/domain/money-input';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { formatDate, formatRupiah } from '@/lib/format';
import { statusTone } from '@/lib/status';
import { channel } from '@/routes/trader';
import { update } from '@/routes/trader/channel';

type Props = {
    canSell: boolean;
    blockReason: string | null;
    range: { min: number | null; max: number | null };
    plan: {
        monthly_price: number;
        description: string | null;
        is_open: boolean;
    } | null;
    feePercent: number;
    durations: number[];
    activeSubscribers: number;
    subscribers: {
        id: number;
        name: string;
        months: number;
        trader_income: number;
        status: string;
        status_label: string;
        starts_at: string | null;
        ends_at: string | null;
    }[];
};

export default function Channel({
    canSell,
    blockReason,
    range,
    plan,
    feePercent,
    durations,
    activeSubscribers,
    subscribers,
}: Props) {
    const form = useForm({
        monthly_price: plan
            ? String(plan.monthly_price)
            : range.min
              ? String(range.min)
              : '',
        description: plan?.description ?? '',
        is_open: plan?.is_open ?? true,
    });
    const errors = form.errors as Record<string, string | undefined>;
    const price = Number(form.data.monthly_price) || 0;

    return (
        <>
            <Head title="Channel Premium" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Channel Premium"
                    description={`${activeSubscribers} subscriber aktif. Sinyal premium kamu terbuka real-time untuk mereka.`}
                />
                {!canSell && (
                    <div className="flex gap-3 rounded-xl border border-pending/40 bg-pending-soft p-4 text-sm">
                        <TriangleAlert className="size-4 shrink-0 text-pending" />
                        <p>{blockReason}</p>
                    </div>
                )}
                {canSell && (
                    <form
                        className="grid max-w-2xl gap-5 rounded-xl border p-4 md:p-6"
                        onSubmit={(event) => {
                            event.preventDefault();
                            form.submit(update(), { preserveScroll: true });
                        }}
                    >
                        <div className="grid gap-2">
                            <Label htmlFor="monthly_price">
                                Harga per bulan
                            </Label>
                            <MoneyInput
                                id="monthly_price"
                                value={form.data.monthly_price}
                                onChange={(e) =>
                                    form.setData(
                                        'monthly_price',
                                        e.target.value,
                                    )
                                }
                            />
                            <p className="text-xs text-muted-foreground">
                                Rentang rank kamu {formatRupiah(range.min ?? 0)}{' '}
                                – {formatRupiah(range.max ?? 0)}. Fee platform{' '}
                                {feePercent}%: kamu menerima{' '}
                                {formatRupiah(
                                    Math.floor(price * (1 - feePercent / 100)),
                                )}{' '}
                                per bulan per subscriber. Durasi:{' '}
                                {durations.join(', ')} bulan.
                            </p>
                            <InputError
                                message={errors.monthly_price ?? errors.channel}
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="description">
                                Deskripsi channel
                            </Label>
                            <Textarea
                                id="description"
                                rows={4}
                                value={form.data.description}
                                onChange={(e) =>
                                    form.setData('description', e.target.value)
                                }
                                placeholder="Gaya trading, instrumen, frekuensi sinyal…"
                            />
                        </div>
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="is_open"
                                checked={form.data.is_open}
                                onCheckedChange={(checked) =>
                                    form.setData('is_open', checked === true)
                                }
                            />
                            <Label htmlFor="is_open">
                                Terima subscriber baru
                            </Label>
                        </div>
                        <Button
                            type="submit"
                            className="justify-self-start"
                            disabled={form.processing}
                        >
                            <Crown /> Simpan channel
                        </Button>
                    </form>
                )}
                <section className="grid gap-3">
                    <h2 className="font-medium">Subscriber</h2>
                    {subscribers.length === 0 ? (
                        <p className="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
                            Belum ada subscriber.
                        </p>
                    ) : (
                        <ul className="divide-y rounded-xl border">
                            {subscribers.map((item) => (
                                <li
                                    key={item.id}
                                    className="flex flex-wrap items-center justify-between gap-3 p-4 text-sm"
                                >
                                    <span>
                                        <span className="block font-medium">
                                            {item.name}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {item.months} bulan · sampai{' '}
                                            {item.ends_at
                                                ? formatDate(item.ends_at)
                                                : '—'}
                                        </span>
                                    </span>
                                    <span className="flex items-center gap-3">
                                        <span className="font-mono tabular-nums">
                                            {formatRupiah(item.trader_income)}
                                        </span>
                                        <StatusPill
                                            label={item.status_label}
                                            tone={statusTone(item.status)}
                                        />
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}

Channel.layout = {
    breadcrumbs: [{ title: 'Channel Premium', href: channel() }],
};
