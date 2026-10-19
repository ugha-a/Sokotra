import { Head, Link } from '@inertiajs/react';
import { Building2, CheckCircle2, ShieldCheck, Wallet } from 'lucide-react';
import Heading from '@/components/heading';
import { BrokerCard } from '@/components/domain/broker-card';
import type { BrokerCardData } from '@/components/domain/broker-card';
import { Button } from '@/components/ui/button';
import { index as brokersIndex, open } from '@/routes/brokers';
import { index as accountsIndex } from '@/routes/broker-accounts';

export default function Brokers({ brokers }: { brokers: BrokerCardData[] }) {
    return (
        <>
            <Head title="Broker Partner Resmi" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Broker Partner Resmi"
                    description="Sotra hanya bermitra dengan pialang berjangka resmi berizin Bappebti. Sotra tidak pernah mengelola atau menampung dana trading kamu."
                />

                {/* Institutional Security & Compliance Bar */}
                <div className="grid grid-cols-1 gap-3 rounded-xl border border-border bg-card p-4 sm:grid-cols-3">
                    <div className="flex items-start gap-3">
                        <ShieldCheck className="size-5 shrink-0 text-brand" />
                        <div>
                            <p className="text-xs font-semibold text-foreground">
                                100% Berizin Bappebti
                            </p>
                            <p className="text-[11px] leading-relaxed text-muted-foreground">
                                Terdaftar dan diawasi oleh Badan Pengawas
                                Perdagangan Berjangka Komoditi & KBI/ICDX.
                            </p>
                        </div>
                    </div>
                    <div className="flex items-start gap-3 border-t border-border/60 pt-3 sm:border-t-0 sm:border-l sm:pt-0 sm:pl-4">
                        <Wallet className="size-5 shrink-0 text-brand" />
                        <div>
                            <p className="text-xs font-semibold text-foreground">
                                Rekening Terpisah (Segregated)
                            </p>
                            <p className="text-[11px] leading-relaxed text-muted-foreground">
                                Dana trading tersimpan aman di bank kustodian
                                terpisah, bukan di pihak Sotra.
                            </p>
                        </div>
                    </div>
                    <div className="flex items-start gap-3 border-t border-border/60 pt-3 sm:border-t-0 sm:border-l sm:pt-0 sm:pl-4">
                        <CheckCircle2 className="size-5 shrink-0 text-brand" />
                        <div>
                            <p className="text-xs font-semibold text-foreground">
                                Validasi Otomatis
                            </p>
                            <p className="text-[11px] leading-relaxed text-muted-foreground">
                                Hubungkan nomor akun untuk verifikasi status
                                member dan akses sinyal trader.
                            </p>
                        </div>
                    </div>
                </div>

                {/* Broker Cards Grid */}
                {brokers.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed border-border bg-card p-12 text-center">
                        <Building2 className="size-10 text-muted-foreground" />
                        <div className="grid gap-1">
                            <p className="font-semibold text-foreground">
                                Belum ada broker partner aktif
                            </p>
                            <p className="max-w-sm text-xs text-muted-foreground">
                                Saat ini proses due diligence kemitraan broker
                                sedang berlangsung.
                            </p>
                        </div>
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

                {/* Next Steps Guide */}
                <div className="flex flex-col gap-3 rounded-xl border border-border bg-muted/30 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="text-xs font-semibold text-foreground">
                            Sudah punya akun di salah satu broker partner di
                            atas?
                        </p>
                        <p className="text-xs text-muted-foreground">
                            Tautkan nomor akun trading kamu agar tercatat
                            sebagai referral dan mendapatkan benefit channel
                            sinyal.
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        asChild
                        className="shrink-0 text-xs"
                    >
                        <Link href={accountsIndex()}>
                            Kelola Akun Broker Saya
                        </Link>
                    </Button>
                </div>
            </div>
        </>
    );
}

Brokers.layout = {
    breadcrumbs: [{ title: 'Broker Partner', href: brokersIndex() }],
};
