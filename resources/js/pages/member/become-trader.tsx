import { Head, useForm } from '@inertiajs/react';
import { BadgeCheck, Clock, ShieldCheck, UserRound } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { RiskDisclaimer } from '@/components/domain/risk-disclaimer';
import { StatusPill } from '@/components/domain/status-pill';
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
import { statusTone } from '@/lib/status';
import { cn } from '@/lib/utils';
import { show, store } from '@/routes/become-trader';
import type { BecomeTraderProps } from '@/types';

type FormData = {
    display_name: string;
    bio: string;
    trading_style: string;
    experience_years: string;
    main_instruments: number[];
    full_name: string;
    nik: string;
    birth_date: string;
    id_card: File | null;
    selfie: File | null;
    accept_terms: boolean;
};

function Section({
    icon: Icon,
    title,
    description,
    children,
}: {
    icon: React.ComponentType<{ className?: string }>;
    title: string;
    description: string;
    children: React.ReactNode;
}) {
    return (
        <section className="grid gap-5 rounded-xl border p-4 md:p-6">
            <header className="flex gap-3">
                <Icon className="mt-0.5 size-5 text-brand" />
                <div>
                    <h3 className="font-medium">{title}</h3>
                    <p className="text-sm text-muted-foreground">
                        {description}
                    </p>
                </div>
            </header>
            {children}
        </section>
    );
}

function Field({
    id,
    label,
    error,
    hint,
    children,
}: {
    id: string;
    label: string;
    error?: string;
    hint?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>{label}</Label>
            {children}
            {hint && <p className="text-xs text-muted-foreground">{hint}</p>}
            <InputError message={error} />
        </div>
    );
}

function StatusCard({
    application,
    kyc,
    blockReason,
}: Pick<BecomeTraderProps, 'application' | 'kyc' | 'blockReason'>) {
    if (!application) {
        return null;
    }

    return (
        <div className="grid gap-3 rounded-xl border p-4 md:p-6">
            <div className="flex flex-wrap items-center gap-2">
                <Clock className="size-4 text-muted-foreground" />
                <span className="text-sm font-medium">Status pengajuan</span>
                <StatusPill
                    label={application.status_label}
                    tone={statusTone(application.status)}
                />
                {kyc && (
                    <>
                        <span className="text-sm text-muted-foreground">
                            KYC
                        </span>
                        <StatusPill
                            label={kyc.status_label}
                            tone={statusTone(kyc.status)}
                        />
                    </>
                )}
            </div>
            {application.rejection_reason && (
                <p className="text-sm">
                    <span className="text-muted-foreground">
                        Alasan ditolak:{' '}
                    </span>
                    {application.rejection_reason}
                </p>
            )}
            {application.suspension_reason && (
                <p className="text-sm">
                    <span className="text-muted-foreground">
                        Alasan ditangguhkan:{' '}
                    </span>
                    {application.suspension_reason}
                </p>
            )}
            {kyc?.rejection_reason && (
                <p className="text-sm">
                    <span className="text-muted-foreground">
                        KYC perlu diperbaiki:{' '}
                    </span>
                    {kyc.rejection_reason}
                </p>
            )}
            {blockReason && (
                <p className="text-sm text-muted-foreground">{blockReason}</p>
            )}
        </div>
    );
}

export default function BecomeTrader({
    application,
    kyc,
    canSubmit,
    blockReason,
    instruments,
    tradingStyles,
    maxFileMb,
    maxInstruments,
    riskDisclaimer,
}: BecomeTraderProps) {
    const form = useForm<FormData>({
        display_name: application?.display_name ?? '',
        bio: application?.bio ?? '',
        trading_style: application?.trading_style ?? '',
        experience_years: application
            ? String(application.experience_years)
            : '',
        main_instruments: application?.main_instruments ?? [],
        full_name: kyc?.full_name ?? '',
        nik: '',
        birth_date: kyc?.birth_date ?? '',
        id_card: null,
        selfie: null,
        accept_terms: false,
    });
    const errors = form.errors as Record<string, string | undefined>;
    const instrumentError = Object.entries(errors).find(([key]) =>
        key.startsWith('main_instruments'),
    )?.[1];

    const toggleInstrument = (id: number) => {
        const selected = form.data.main_instruments;
        form.setData(
            'main_instruments',
            selected.includes(id)
                ? selected.filter((item) => item !== id)
                : selected.length < maxInstruments
                  ? [...selected, id]
                  : selected,
        );
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.submit(store(), { forceFormData: true, preserveScroll: true });
    };

    return (
        <>
            <Head title="Jadi Trader" />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Jadi Trader di Sotra"
                    description="Posting analisa, bangun track record yang dinilai otomatis dari harga pasar, dan dapatkan penghasilan dari kinerja dan followers kamu."
                />

                <StatusCard
                    application={application}
                    kyc={kyc}
                    blockReason={blockReason}
                />

                {canSubmit && (
                    <form onSubmit={submit} className="grid gap-6">
                        <InputError message={errors.application} />

                        <Section
                            icon={UserRound}
                            title="Profil trader"
                            description="Tampil di profil publik dan leaderboard."
                        >
                            <Field
                                id="display_name"
                                label="Nama tampilan"
                                error={errors.display_name}
                            >
                                <Input
                                    id="display_name"
                                    value={form.data.display_name}
                                    onChange={(e) =>
                                        form.setData(
                                            'display_name',
                                            e.target.value,
                                        )
                                    }
                                />
                            </Field>
                            <Field
                                id="bio"
                                label="Bio"
                                error={errors.bio}
                                hint="Jelaskan pendekatan analisa kamu. Klaim seperti “pasti profit” tidak diizinkan."
                            >
                                <Textarea
                                    id="bio"
                                    rows={4}
                                    value={form.data.bio}
                                    onChange={(e) =>
                                        form.setData('bio', e.target.value)
                                    }
                                />
                            </Field>
                            <div className="grid gap-5 sm:grid-cols-2">
                                <Field
                                    id="trading_style"
                                    label="Gaya trading"
                                    error={errors.trading_style}
                                >
                                    <Select
                                        value={form.data.trading_style}
                                        onValueChange={(value) =>
                                            form.setData('trading_style', value)
                                        }
                                    >
                                        <SelectTrigger id="trading_style">
                                            <SelectValue placeholder="Pilih…" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {tradingStyles.map((style) => (
                                                <SelectItem
                                                    key={style.value}
                                                    value={String(style.value)}
                                                >
                                                    {style.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>
                                <Field
                                    id="experience_years"
                                    label="Pengalaman trading (tahun)"
                                    error={errors.experience_years}
                                >
                                    <Input
                                        id="experience_years"
                                        type="number"
                                        min={0}
                                        className="font-mono tabular-nums"
                                        value={form.data.experience_years}
                                        onChange={(e) =>
                                            form.setData(
                                                'experience_years',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </Field>
                            </div>
                            <div className="grid gap-2">
                                <span className="text-sm font-medium">
                                    Instrumen utama{' '}
                                    <span className="font-normal text-muted-foreground">
                                        (maksimal {maxInstruments})
                                    </span>
                                </span>
                                <div className="flex flex-wrap gap-2">
                                    {instruments.map((instrument) => {
                                        const id = Number(instrument.value);
                                        const selected =
                                            form.data.main_instruments.includes(
                                                id,
                                            );

                                        return (
                                            <button
                                                key={id}
                                                type="button"
                                                aria-pressed={selected}
                                                onClick={() =>
                                                    toggleInstrument(id)
                                                }
                                                className={cn(
                                                    'rounded-full border px-3 py-1 font-mono text-sm transition-colors',
                                                    selected
                                                        ? 'border-brand bg-brand text-brand-foreground'
                                                        : 'hover:bg-accent',
                                                )}
                                            >
                                                {instrument.label}
                                            </button>
                                        );
                                    })}
                                </div>
                                <InputError message={instrumentError} />
                            </div>
                        </Section>

                        <Section
                            icon={ShieldCheck}
                            title="Verifikasi identitas (KYC)"
                            description="Wajib untuk trader. Data disimpan terenkripsi dan hanya bisa dilihat admin peninjau."
                        >
                            <div className="grid gap-5 sm:grid-cols-2">
                                <Field
                                    id="full_name"
                                    label="Nama sesuai KTP"
                                    error={errors.full_name}
                                >
                                    <Input
                                        id="full_name"
                                        value={form.data.full_name}
                                        onChange={(e) =>
                                            form.setData(
                                                'full_name',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </Field>
                                <Field
                                    id="birth_date"
                                    label="Tanggal lahir"
                                    error={errors.birth_date}
                                >
                                    <Input
                                        id="birth_date"
                                        type="date"
                                        value={form.data.birth_date}
                                        onChange={(e) =>
                                            form.setData(
                                                'birth_date',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </Field>
                            </div>
                            <Field
                                id="nik"
                                label="NIK"
                                error={errors.nik}
                                hint="16 digit sesuai KTP."
                            >
                                <Input
                                    id="nik"
                                    inputMode="numeric"
                                    autoComplete="off"
                                    className="font-mono tracking-wider"
                                    value={form.data.nik}
                                    onChange={(e) =>
                                        form.setData('nik', e.target.value)
                                    }
                                />
                            </Field>
                            <div className="grid gap-5 sm:grid-cols-2">
                                <Field
                                    id="id_card"
                                    label="Foto KTP"
                                    error={errors.id_card}
                                    hint={`JPG, PNG, atau WEBP. Maksimal ${maxFileMb} MB.`}
                                >
                                    <Input
                                        id="id_card"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        onChange={(e) =>
                                            form.setData(
                                                'id_card',
                                                e.target.files?.[0] ?? null,
                                            )
                                        }
                                    />
                                </Field>
                                <Field
                                    id="selfie"
                                    label="Selfie sambil memegang KTP"
                                    error={errors.selfie}
                                    hint="Wajah dan KTP harus terlihat jelas."
                                >
                                    <Input
                                        id="selfie"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        onChange={(e) =>
                                            form.setData(
                                                'selfie',
                                                e.target.files?.[0] ?? null,
                                            )
                                        }
                                    />
                                </Field>
                            </div>
                        </Section>

                        <Section
                            icon={BadgeCheck}
                            title="Persetujuan"
                            description="Sebagai trader, analisa kamu akan diikuti orang lain."
                        >
                            <RiskDisclaimer text={riskDisclaimer} />
                            <div className="grid gap-1">
                                <div className="flex items-start gap-2">
                                    <Checkbox
                                        id="accept_terms"
                                        checked={form.data.accept_terms}
                                        onCheckedChange={(checked) =>
                                            form.setData(
                                                'accept_terms',
                                                checked === true,
                                            )
                                        }
                                    />
                                    <Label
                                        htmlFor="accept_terms"
                                        className="leading-snug font-normal"
                                    >
                                        Saya menyetujui syarat & ketentuan
                                        trader dan disclaimer risiko, serta
                                        tidak akan menjanjikan profit kepada
                                        siapa pun.
                                    </Label>
                                </div>
                                <InputError message={errors.accept_terms} />
                            </div>
                        </Section>

                        <Button
                            type="submit"
                            size="lg"
                            className="justify-self-start"
                            disabled={form.processing}
                        >
                            {form.processing && <Spinner />}
                            Kirim pengajuan
                        </Button>
                    </form>
                )}
            </div>
        </>
    );
}

BecomeTrader.layout = {
    breadcrumbs: [{ title: 'Jadi Trader', href: show() }],
};
