import { Head, router, usePage } from '@inertiajs/react';
import { ExternalLink, ShieldCheck, UserRound } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { ConfirmDialog } from '@/components/domain/confirm-dialog';
import { RankBadge } from '@/components/domain/rank-badge';
import { ReasonDialog } from '@/components/domain/reason-dialog';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/format';
import { statusTone } from '@/lib/status';
import {
    approve,
    index,
    reinstate,
    reject,
    suspend,
} from '@/routes/admin/traders';
import {
    reject as rejectKyc,
    verify as verifyKyc,
} from '@/routes/admin/traders/kyc';
import type { AdminKyc, AdminTraderProfile } from '@/types';

type Props = {
    profile: AdminTraderProfile;
    kyc: AdminKyc | null;
    canApprove: boolean;
};

function Detail({
    label,
    children,
}: {
    label: string;
    children: React.ReactNode;
}) {
    return (
        <div className="grid gap-0.5">
            <dt className="text-xs text-muted-foreground">{label}</dt>
            <dd className="text-sm">{children}</dd>
        </div>
    );
}

function Document({ label, url }: { label: string; url: string }) {
    return (
        <figure className="grid gap-2">
            <a
                href={url}
                target="_blank"
                rel="noreferrer"
                className="group relative block overflow-hidden rounded-lg border bg-muted"
            >
                <img
                    src={url}
                    alt={label}
                    className="aspect-[4/3] w-full object-contain"
                />
                <span className="absolute right-2 bottom-2 flex items-center gap-1 rounded-md bg-background/90 px-2 py-1 text-xs opacity-0 transition-opacity group-hover:opacity-100">
                    <ExternalLink className="size-3" /> Buka
                </span>
            </a>
            <figcaption className="text-xs text-muted-foreground">
                {label}
            </figcaption>
        </figure>
    );
}

export default function TraderApplicationShow({
    profile,
    kyc,
    canApprove,
}: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;

    return (
        <>
            <Head title={`Aplikasi — ${profile.display_name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        title={profile.display_name}
                        description={`${profile.user.name} · ${profile.user.email}`}
                    />
                    <div className="flex items-center gap-2">
                        {profile.rank && (
                            <RankBadge
                                name={profile.rank.name}
                                color={profile.rank.color}
                            />
                        )}
                        <StatusPill
                            label={profile.status_label}
                            tone={statusTone(profile.status)}
                        />
                    </div>
                </div>

                <InputError message={errors.application ?? errors.kyc} />

                <div className="grid gap-6 lg:grid-cols-2">
                    <section className="grid content-start gap-4 rounded-xl border p-4 md:p-6">
                        <header className="flex items-center gap-2">
                            <UserRound className="size-5 text-brand" />
                            <h3 className="font-medium">Aplikasi trader</h3>
                        </header>
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <Detail label="Gaya trading">
                                {profile.trading_style}
                            </Detail>
                            <Detail label="Pengalaman">
                                {profile.experience_years} tahun
                            </Detail>
                            <Detail label="Instrumen utama">
                                <span className="font-mono">
                                    {profile.main_instruments.join(', ')}
                                </span>
                            </Detail>
                            <Detail label="Diajukan">
                                {profile.applied_at
                                    ? formatDate(profile.applied_at)
                                    : '—'}
                            </Detail>
                            {profile.reviewed_at && (
                                <Detail label="Direview">
                                    {formatDate(profile.reviewed_at)}
                                    {profile.reviewer &&
                                        ` oleh ${profile.reviewer}`}
                                </Detail>
                            )}
                        </dl>
                        <Detail label="Bio">
                            <p className="whitespace-pre-line">{profile.bio}</p>
                        </Detail>
                        {profile.rejection_reason && (
                            <Detail label="Alasan ditolak">
                                {profile.rejection_reason}
                            </Detail>
                        )}
                        {profile.suspension_reason && (
                            <Detail label="Alasan ditangguhkan">
                                {profile.suspension_reason}
                            </Detail>
                        )}

                        <div className="flex flex-wrap gap-2 pt-2">
                            {profile.status === 'applied' && (
                                <>
                                    <ConfirmDialog
                                        trigger={
                                            <Button disabled={!canApprove}>
                                                Setujui jadi trader
                                            </Button>
                                        }
                                        title="Setujui aplikasi trader?"
                                        description="User akan mendapat role trader dengan rank terendah dan bisa mulai memposting sinyal."
                                        confirmLabel="Setujui"
                                        onConfirm={() =>
                                            router.visit(approve(profile.id), {
                                                preserveScroll: true,
                                            })
                                        }
                                    />
                                    <ReasonDialog
                                        trigger={
                                            <Button variant="outline">
                                                Tolak aplikasi
                                            </Button>
                                        }
                                        title="Tolak aplikasi trader"
                                        description="Alasan dikirim ke pemohon. Pemohon bisa mengajukan ulang setelah masa jeda."
                                        confirmLabel="Tolak aplikasi"
                                        action={reject(profile.id)}
                                    />
                                </>
                            )}
                            {profile.status === 'approved' && (
                                <ReasonDialog
                                    trigger={
                                        <Button variant="outline">
                                            Tangguhkan trader
                                        </Button>
                                    }
                                    title="Tangguhkan trader"
                                    description="Trader tidak bisa memposting sinyal dan disembunyikan dari leaderboard. Sinyal terbuka tetap dinilai."
                                    confirmLabel="Tangguhkan"
                                    action={suspend(profile.id)}
                                />
                            )}
                            {profile.status === 'suspended' && (
                                <ConfirmDialog
                                    trigger={<Button>Aktifkan kembali</Button>}
                                    title="Aktifkan kembali trader?"
                                    description="Trader bisa memposting sinyal lagi dan kembali tampil di leaderboard."
                                    confirmLabel="Aktifkan"
                                    onConfirm={() =>
                                        router.visit(reinstate(profile.id), {
                                            preserveScroll: true,
                                        })
                                    }
                                />
                            )}
                        </div>
                        {profile.status === 'applied' && !canApprove && (
                            <p className="text-xs text-muted-foreground">
                                Aplikasi bisa disetujui setelah KYC
                                terverifikasi.
                            </p>
                        )}
                    </section>

                    <section className="grid content-start gap-4 rounded-xl border p-4 md:p-6">
                        <header className="flex flex-wrap items-center gap-2">
                            <ShieldCheck className="size-5 text-brand" />
                            <h3 className="font-medium">
                                Verifikasi identitas (KYC)
                            </h3>
                            {kyc && (
                                <StatusPill
                                    label={kyc.status_label}
                                    tone={statusTone(kyc.status)}
                                />
                            )}
                        </header>

                        {!kyc ? (
                            <p className="text-sm text-muted-foreground">
                                Pengguna ini belum mengirim KYC.
                            </p>
                        ) : (
                            <>
                                <dl className="grid gap-4 sm:grid-cols-2">
                                    <Detail label="Nama sesuai KTP">
                                        {kyc.full_name}
                                    </Detail>
                                    <Detail label="NIK">
                                        <span className="font-mono tracking-wider">
                                            {kyc.nik_masked}
                                        </span>
                                    </Detail>
                                    <Detail label="Tanggal lahir">
                                        {kyc.birth_date}
                                    </Detail>
                                    <Detail label="Dikirim">
                                        {kyc.submitted_at
                                            ? formatDate(kyc.submitted_at)
                                            : '—'}
                                    </Detail>
                                    {kyc.reviewed_at && (
                                        <Detail label="Direview">
                                            {formatDate(kyc.reviewed_at)}
                                            {kyc.reviewer &&
                                                ` oleh ${kyc.reviewer}`}
                                        </Detail>
                                    )}
                                </dl>
                                {kyc.rejection_reason && (
                                    <Detail label="Alasan ditolak">
                                        {kyc.rejection_reason}
                                    </Detail>
                                )}

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <Document
                                        label="Foto KTP"
                                        url={kyc.documents.id_card}
                                    />
                                    <Document
                                        label="Selfie dengan KTP"
                                        url={kyc.documents.selfie}
                                    />
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Cocokkan nama, tanggal lahir, dan NIK di
                                    KTP. Link dokumen berlaku{' '}
                                    {kyc.documentLinkMinutes} menit dan setiap
                                    akses tercatat.
                                </p>

                                {kyc.status === 'submitted' && (
                                    <div className="flex flex-wrap gap-2">
                                        <ConfirmDialog
                                            trigger={
                                                <Button>Verifikasi KYC</Button>
                                            }
                                            title="Verifikasi KYC?"
                                            description="Pastikan data sesuai dengan foto KTP dan selfie."
                                            confirmLabel="Verifikasi"
                                            onConfirm={() =>
                                                router.visit(
                                                    verifyKyc(profile.id),
                                                    { preserveScroll: true },
                                                )
                                            }
                                        />
                                        <ReasonDialog
                                            trigger={
                                                <Button variant="outline">
                                                    Tolak KYC
                                                </Button>
                                            }
                                            title="Tolak KYC"
                                            description="Alasan dikirim ke pengguna agar bisa mengirim ulang dokumen."
                                            confirmLabel="Tolak KYC"
                                            action={rejectKyc(profile.id)}
                                        />
                                    </div>
                                )}
                            </>
                        )}
                    </section>
                </div>
            </div>
        </>
    );
}

TraderApplicationShow.layout = {
    breadcrumbs: [{ title: 'Aplikasi Trader', href: index() }],
};
