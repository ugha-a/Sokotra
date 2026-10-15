import { Head, useForm } from '@inertiajs/react';
import { Crown, Radio, TriangleAlert } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { ConfirmDialog } from '@/components/domain/confirm-dialog';
import { RiskDisclaimer } from '@/components/domain/risk-disclaimer';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { useLiveQuote } from '@/hooks/use-live-quote';
import { formatPrice } from '@/lib/format';
import { pipsBetween, riskReward, validateLevels } from '@/lib/signal-math';
import { cn } from '@/lib/utils';
import { create, store } from '@/routes/trader/signals';
import type { SignalCreateProps } from '@/types';

type FormData = {
    instrument_id: string;
    direction: string;
    order_type: string;
    entry_price: string;
    stop_loss: string;
    take_profit: string;
    visibility: string;
    analysis: string;
    chart_image: File | null;
};

function Segmented({
    value,
    options,
    onChange,
    tone,
}: {
    value: string;
    options: { value: string | number; label: string }[];
    onChange: (value: string) => void;
    tone?: (value: string) => string;
}) {
    return (
        <div className="grid auto-cols-fr grid-flow-col gap-1 rounded-lg border p-1">
            {options.map((option) => {
                const selected = String(option.value) === value;

                return (
                    <button
                        key={option.value}
                        type="button"
                        aria-pressed={selected}
                        onClick={() => onChange(String(option.value))}
                        className={cn(
                            'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                            selected
                                ? (tone?.(String(option.value)) ??
                                      'bg-brand text-brand-foreground')
                                : 'hover:bg-accent',
                        )}
                    >
                        {option.label}
                    </button>
                );
            })}
        </div>
    );
}

function Metric({
    label,
    value,
    tone,
}: {
    label: string;
    value: string;
    tone?: 'ok' | 'bad';
}) {
    return (
        <div className="grid gap-0.5 rounded-lg border p-3">
            <span className="text-xs text-muted-foreground">{label}</span>
            <span
                className={cn(
                    'font-mono text-lg font-semibold tabular-nums',
                    tone === 'ok' && 'text-profit',
                    tone === 'bad' && 'text-loss',
                )}
            >
                {value}
            </span>
        </div>
    );
}

export default function CreateSignal({
    instruments,
    directions,
    orderTypes,
    rules,
    canPublishPremium,
    blockReason,
    riskDisclaimer,
}: SignalCreateProps) {
    const form = useForm<FormData>({
        instrument_id: instruments[0] ? String(instruments[0].id) : '',
        direction: 'buy',
        order_type: 'market',
        entry_price: '',
        stop_loss: '',
        take_profit: '',
        visibility: 'free',
        analysis: '',
        chart_image: null,
    });
    const errors = form.errors as Record<string, string | undefined>;
    const instrument =
        instruments.find(
            (item) => String(item.id) === form.data.instrument_id,
        ) ?? null;
    const { quote, error: quoteError } = useLiveQuote(instrument?.id ?? null);

    const isMarket = form.data.order_type === 'market';
    const isBuy = form.data.direction === 'buy';
    const liveEntry = quote ? (isBuy ? quote.ask : quote.bid) : '';
    const entry = isMarket ? liveEntry : form.data.entry_price;
    const hasLevels =
        instrument && entry && form.data.stop_loss && form.data.take_profit;
    const sideErrors = hasLevels
        ? validateLevels(
              form.data.direction,
              entry,
              form.data.stop_loss,
              form.data.take_profit,
          )
        : [];
    const slPips =
        instrument && entry && form.data.stop_loss
            ? pipsBetween(instrument, entry, form.data.stop_loss)
            : null;
    const tpPips =
        instrument && entry && form.data.take_profit
            ? pipsBetween(instrument, entry, form.data.take_profit)
            : null;
    const rr =
        hasLevels && sideErrors.length === 0
            ? riskReward(
                  instrument,
                  entry,
                  form.data.stop_loss,
                  form.data.take_profit,
              )
            : null;
    const slTooClose =
        instrument &&
        slPips !== null &&
        slPips < Number(instrument.min_sl_pips);
    const rrTooLow = rr !== null && rr < rules.min_rr;
    const atCapacity = rules.open_count >= rules.max_active;

    const submit = () => {
        // Market orders always fill at the live price, so no entry is sent.
        form.transform((data) => ({
            ...data,
            entry_price: isMarket ? '' : data.entry_price,
        }));
        form.submit(store(), { forceFormData: true, preserveScroll: true });
    };

    if (blockReason) {
        return (
            <div className="p-4 md:p-6">
                <Head title="Posting Sinyal" />
                <div className="flex gap-3 rounded-xl border border-loss/40 bg-loss-soft p-4 text-sm">
                    <TriangleAlert className="size-4 shrink-0 text-loss" />
                    <p>{blockReason}</p>
                </div>
            </div>
        );
    }

    return (
        <>
            <Head title="Posting Sinyal" />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Posting Sinyal"
                    description={`Sinyal dinilai otomatis dari harga pasar. Entry, Stop Loss, dan Take Profit tidak bisa diubah setelah dipublish. Sinyal terbuka: ${rules.open_count} dari ${rules.max_active}.`}
                />

                <InputError message={errors.signal} />

                <form
                    onSubmit={(event) => event.preventDefault()}
                    className="grid gap-6 rounded-xl border p-4 md:p-6"
                >
                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="instrument_id">Instrumen</Label>
                            <Select
                                value={form.data.instrument_id}
                                onValueChange={(value) =>
                                    form.setData('instrument_id', value)
                                }
                            >
                                <SelectTrigger
                                    id="instrument_id"
                                    className="font-mono"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {instruments.map((item) => (
                                        <SelectItem
                                            key={item.id}
                                            value={String(item.id)}
                                            className="font-mono"
                                        >
                                            {item.symbol}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.instrument_id} />
                        </div>
                        <div className="grid gap-2">
                            <span className="text-sm font-medium">
                                Harga live
                            </span>
                            <div className="flex h-9 items-center gap-3 rounded-md border px-3 font-mono text-sm tabular-nums">
                                <Radio
                                    className={cn(
                                        'size-3.5',
                                        quote
                                            ? 'text-profit'
                                            : 'text-muted-foreground',
                                    )}
                                />
                                {quote && instrument ? (
                                    <>
                                        <span>
                                            <span className="text-xs text-muted-foreground">
                                                Bid{' '}
                                            </span>
                                            {formatPrice(
                                                quote.bid,
                                                instrument.digits,
                                            )}
                                        </span>
                                        <span>
                                            <span className="text-xs text-muted-foreground">
                                                Ask{' '}
                                            </span>
                                            {formatPrice(
                                                quote.ask,
                                                instrument.digits,
                                            )}
                                        </span>
                                    </>
                                ) : (
                                    <span className="text-muted-foreground">
                                        {quoteError ?? 'Memuat…'}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <span className="text-sm font-medium">Arah</span>
                            <Segmented
                                value={form.data.direction}
                                options={directions}
                                onChange={(value) =>
                                    form.setData('direction', value)
                                }
                                tone={(value) =>
                                    value === 'buy'
                                        ? 'bg-profit text-profit-foreground'
                                        : 'bg-loss text-loss-foreground'
                                }
                            />
                        </div>
                        <div className="grid gap-2">
                            <span className="text-sm font-medium">
                                Tipe order
                            </span>
                            <Segmented
                                value={form.data.order_type}
                                options={orderTypes}
                                onChange={(value) =>
                                    form.setData('order_type', value)
                                }
                            />
                        </div>
                    </div>

                    <div className="grid gap-5 sm:grid-cols-3">
                        <div className="grid gap-2">
                            <Label htmlFor="entry_price">Entry</Label>
                            <Input
                                id="entry_price"
                                inputMode="decimal"
                                className="font-mono tabular-nums"
                                disabled={isMarket}
                                value={
                                    isMarket
                                        ? liveEntry && instrument
                                            ? formatPrice(
                                                  liveEntry,
                                                  instrument.digits,
                                              )
                                            : ''
                                        : form.data.entry_price
                                }
                                placeholder={
                                    isMarket
                                        ? 'Harga live saat publish'
                                        : undefined
                                }
                                onChange={(e) =>
                                    form.setData('entry_price', e.target.value)
                                }
                            />
                            <InputError message={errors.entry_price} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="stop_loss">Stop Loss</Label>
                            <Input
                                id="stop_loss"
                                inputMode="decimal"
                                className="font-mono tabular-nums"
                                value={form.data.stop_loss}
                                aria-invalid={
                                    sideErrors.includes('stop_loss') ||
                                    !!errors.stop_loss
                                }
                                onChange={(e) =>
                                    form.setData('stop_loss', e.target.value)
                                }
                            />
                            <InputError
                                message={
                                    errors.stop_loss ??
                                    (sideErrors.includes('stop_loss')
                                        ? `Stop Loss ${isBuy ? 'buy harus di bawah' : 'sell harus di atas'} entry.`
                                        : slTooClose && instrument
                                          ? `Jarak SL minimal ${Number(instrument.min_sl_pips)} pips.`
                                          : undefined)
                                }
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="take_profit">Take Profit</Label>
                            <Input
                                id="take_profit"
                                inputMode="decimal"
                                className="font-mono tabular-nums"
                                value={form.data.take_profit}
                                aria-invalid={
                                    sideErrors.includes('take_profit') ||
                                    !!errors.take_profit
                                }
                                onChange={(e) =>
                                    form.setData('take_profit', e.target.value)
                                }
                            />
                            <InputError
                                message={
                                    errors.take_profit ??
                                    (sideErrors.includes('take_profit')
                                        ? `Take Profit ${isBuy ? 'buy harus di atas' : 'sell harus di bawah'} entry.`
                                        : rrTooLow
                                          ? `Risk Reward minimal 1:${rules.min_rr.toLocaleString('id-ID', { minimumFractionDigits: 2 })}. Jauhkan TP atau dekatkan SL.`
                                          : undefined)
                                }
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                        <Metric
                            label="Risiko"
                            value={
                                slPips === null
                                    ? '—'
                                    : `${slPips.toLocaleString('id-ID')} pips`
                            }
                            tone={slTooClose ? 'bad' : undefined}
                        />
                        <Metric
                            label="Target"
                            value={
                                tpPips === null
                                    ? '—'
                                    : `${tpPips.toLocaleString('id-ID')} pips`
                            }
                        />
                        <Metric
                            label="Risk Reward"
                            value={
                                rr === null
                                    ? '—'
                                    : `1:${rr.toLocaleString('id-ID', { minimumFractionDigits: 2 })}`
                            }
                            tone={
                                rr === null
                                    ? undefined
                                    : rrTooLow
                                      ? 'bad'
                                      : 'ok'
                            }
                        />
                    </div>
                    {!isMarket && (
                        <p className="-mt-3 text-xs text-muted-foreground">
                            Order pending kedaluwarsa jika entry tidak tersentuh
                            dalam {rules.pending_expiry_hours} jam.
                        </p>
                    )}

                    <div className="grid gap-2">
                        <Label htmlFor="analysis">Analisa</Label>
                        <Textarea
                            id="analysis"
                            rows={5}
                            value={form.data.analysis}
                            onChange={(e) =>
                                form.setData('analysis', e.target.value)
                            }
                            placeholder="Kenapa kamu mengambil posisi ini? Struktur pasar, level kunci, konfirmasi…"
                        />
                        <p className="text-xs text-muted-foreground">
                            Minimal {rules.min_analysis_chars} karakter (
                            {form.data.analysis.length} saat ini). Klaim seperti
                            “pasti profit” tidak diizinkan.
                        </p>
                        <InputError message={errors.analysis} />
                    </div>

                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="chart_image">
                                Gambar chart (opsional)
                            </Label>
                            <Input
                                id="chart_image"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                onChange={(e) =>
                                    form.setData(
                                        'chart_image',
                                        e.target.files?.[0] ?? null,
                                    )
                                }
                            />
                            <p className="text-xs text-muted-foreground">
                                Maksimal {rules.chart_max_mb} MB.
                            </p>
                            <InputError message={errors.chart_image} />
                        </div>
                        <div className="grid content-start gap-2">
                            <span className="text-sm font-medium">
                                Visibilitas
                            </span>
                            <div className="flex items-start gap-2">
                                <Checkbox
                                    id="premium"
                                    disabled={!canPublishPremium}
                                    checked={form.data.visibility === 'premium'}
                                    onCheckedChange={(checked) =>
                                        form.setData(
                                            'visibility',
                                            checked === true
                                                ? 'premium'
                                                : 'free',
                                        )
                                    }
                                />
                                <Label
                                    htmlFor="premium"
                                    className="leading-snug font-normal"
                                >
                                    <Crown className="inline size-3.5 text-premium" />{' '}
                                    Sinyal premium
                                    <span className="block text-xs text-muted-foreground">
                                        {canPublishPremium
                                            ? 'Detail hanya untuk subscriber sampai sinyal selesai.'
                                            : 'Tersedia mulai rank yang boleh membuka channel premium.'}
                                    </span>
                                </Label>
                            </div>
                            <InputError message={errors.visibility} />
                        </div>
                    </div>

                    <RiskDisclaimer text={riskDisclaimer} variant="inline" />

                    <ConfirmDialog
                        trigger={
                            <Button
                                type="button"
                                size="lg"
                                className="justify-self-start"
                                disabled={
                                    form.processing || atCapacity || !quote
                                }
                            >
                                {form.processing && <Spinner />}
                                Publish sinyal
                            </Button>
                        }
                        title="Publish sinyal ini?"
                        description="Setelah dipublish, entry, Stop Loss, dan Take Profit tidak bisa diubah atau dihapus. Hasilnya dinilai otomatis dari harga pasar dan masuk ke statistik kamu."
                        confirmLabel="Publish"
                        onConfirm={submit}
                    />
                    {atCapacity && (
                        <p className="text-sm text-muted-foreground">
                            Kamu sudah punya {rules.max_active} sinyal terbuka.
                            Tunggu salah satunya selesai.
                        </p>
                    )}
                </form>
            </div>
        </>
    );
}

CreateSignal.layout = {
    breadcrumbs: [{ title: 'Posting Sinyal', href: create() }],
};
