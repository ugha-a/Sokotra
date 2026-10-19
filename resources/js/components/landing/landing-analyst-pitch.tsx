import { useState } from 'react';
import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    Coins,
    ShieldCheck,
    Sparkles,
    Users,
    Wallet,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/format';
import { register } from '@/routes';

export function LandingAnalystPitch() {
    // Patreon & Darwinex inspired interactive slider states
    const [subscribers, setSubscribers] = useState(100);
    const [monthlyFee, setMonthlyFee] = useState(500000); // Rp 500.000 (~$35-50 USD)

    // Calculations
    const grossSubscription = subscribers * monthlyFee;
    const netTraderEarnings = Math.round(grossSubscription * 0.85); // 85% payout to analyst
    const estimatedBrokerRebate = Math.round(subscribers * 150000); // Estimasi rebate volume trading follower
    const totalPotentialMonthly = netTraderEarnings + estimatedBrokerRebate;

    return (
        <section
            id="analyst-pitch"
            className="border-b border-[#2a2e39] bg-[#0e1118] py-16 lg:py-24"
        >
            <div className="mx-auto max-w-7xl px-4 sm:px-6">
                <div className="grid items-center gap-12 lg:grid-cols-12 lg:gap-10">
                    {/* Left Column: Creator Value Proposition */}
                    <div className="lg:col-span-6">
                        <div className="inline-flex items-center gap-2 rounded-full border border-[#2a2e39] bg-[#1e222d] px-3.5 py-1 text-xs font-semibold text-[#f7a600]">
                            <Sparkles className="h-3.5 w-3.5" />
                            <span>Untuk Trader Berpengalaman & Komunitas</span>
                        </div>

                        <h2 className="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                            Fokus trading, biarkan kami yang mengurus
                            monetisasinya.
                        </h2>

                        <p className="mt-4 text-base leading-relaxed text-[#b2b5be] sm:text-lg">
                            Hentikan kerepotan mengelola grup VIP manual, bukti
                            transfer palsu, dan kebocoran sinyal. Di SooTa,
                            reputasi Anda diaudit secara profesional layaknya
                            Darwinex, dan sistem kami menangani langganan
                            bulanan Anda layaknya Patreon.
                        </p>

                        <div className="mt-8 space-y-4">
                            <div className="flex items-start gap-3.5">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#2a2e39] bg-[#1e222d] text-[#089981]">
                                    <ShieldCheck className="h-5 w-5" />
                                </div>
                                <div>
                                    <h4 className="text-sm font-semibold text-white">
                                        Proteksi Hak Cipta & Sinyal Terenkripsi
                                    </h4>
                                    <p className="mt-0.5 text-xs leading-relaxed text-[#787b86]">
                                        Sinyal hanya dapat diakses oleh
                                        pelanggan aktif melalui saluran resmi
                                        tanpa risiko re-share liar.
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-start gap-3.5">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#2a2e39] bg-[#1e222d] text-[#2962ff]">
                                    <Wallet className="h-5 w-5" />
                                </div>
                                <div>
                                    <h4 className="text-sm font-semibold text-white">
                                        Payout Otomatis ke Bank Lokal
                                    </h4>
                                    <p className="mt-0.5 text-xs leading-relaxed text-[#787b86]">
                                        Tarik saldo pendapatan langganan Anda
                                        setiap bulan langsung ke rekening bank
                                        Anda dengan aman dan tepat waktu.
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-start gap-3.5">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#2a2e39] bg-[#1e222d] text-[#f7a600]">
                                    <Coins className="h-5 w-5" />
                                </div>
                                <div>
                                    <h4 className="text-sm font-semibold text-white">
                                        Bagi Hasil & Rebate Broker Partner
                                    </h4>
                                    <p className="mt-0.5 text-xs leading-relaxed text-[#787b86]">
                                        Dapatkan insentif tambahan dari volume
                                        trading member Anda melalui kemitraan
                                        pialang berjangka resmi.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="mt-8">
                            <Button
                                size="lg"
                                asChild
                                className="h-12 bg-[#2962ff] px-6 text-base font-semibold text-white shadow-sm hover:bg-[#1e53e5]"
                            >
                                <Link
                                    href={register()}
                                    className="inline-flex items-center gap-2"
                                >
                                    <span>Daftar Sebagai Trader Analis</span>
                                    <ArrowRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <span className="ml-4 text-xs text-[#787b86]">
                                Verifikasi profil dan KYC tanpa biaya
                                pendaftaran.
                            </span>
                        </div>
                    </div>

                    {/* Right Column: Patreon-Style Interactive Earnings Calculator */}
                    <div className="lg:col-span-6">
                        <div className="rounded-xl border border-[#2a2e39] bg-[#1e222d] p-6 shadow-xl sm:p-8">
                            <div className="flex items-center justify-between border-b border-[#2a2e39] pb-4">
                                <div>
                                    <h3 className="text-lg font-bold text-white">
                                        Simulasi Penghasilan Tambahan
                                    </h3>
                                    <p className="text-xs text-[#787b86]">
                                        Geser slider untuk melihat potensi
                                        pendapatan bulanan Anda
                                    </p>
                                </div>
                                <span className="rounded bg-[#089981]/15 px-2.5 py-1 font-mono text-xs font-semibold text-[#089981]">
                                    ESTIMASI ANALIS
                                </span>
                            </div>

                            {/* Slider 1: Jumlah Subscriber */}
                            <div className="mt-6">
                                <div className="flex items-center justify-between text-sm font-medium">
                                    <label className="flex items-center gap-2 text-white">
                                        <Users className="h-4 w-4 text-[#2962ff]" />
                                        <span>
                                            Target Pelanggan (Subscribers):
                                        </span>
                                    </label>
                                    <span className="font-mono text-lg font-bold text-[#2962ff]">
                                        {subscribers} Member
                                    </span>
                                </div>
                                <input
                                    type="range"
                                    min="10"
                                    max="500"
                                    step="10"
                                    value={subscribers}
                                    onChange={(e) =>
                                        setSubscribers(parseInt(e.target.value))
                                    }
                                    className="mt-3 h-2 w-full cursor-pointer appearance-none rounded-lg bg-[#131722] accent-[#2962ff]"
                                />
                                <div className="mt-1 flex justify-between font-mono text-[11px] text-[#787b86]">
                                    <span>10 Member</span>
                                    <span>250 Member</span>
                                    <span>500 Member</span>
                                </div>
                            </div>

                            {/* Slider 2: Biaya Langganan Bulanan */}
                            <div className="mt-6 border-t border-[#2a2e39] pt-6">
                                <div className="flex items-center justify-between text-sm font-medium">
                                    <label className="flex items-center gap-2 text-white">
                                        <Wallet className="h-4 w-4 text-[#089981]" />
                                        <span>Tarif Langganan per Bulan:</span>
                                    </label>
                                    <span className="font-mono text-lg font-bold text-[#089981]">
                                        {formatRupiah(monthlyFee)}
                                    </span>
                                </div>
                                <input
                                    type="range"
                                    min="150000"
                                    max="1500000"
                                    step="50000"
                                    value={monthlyFee}
                                    onChange={(e) =>
                                        setMonthlyFee(parseInt(e.target.value))
                                    }
                                    className="mt-3 h-2 w-full cursor-pointer appearance-none rounded-lg bg-[#131722] accent-[#089981]"
                                />
                                <div className="mt-1 flex justify-between font-mono text-[11px] text-[#787b86]">
                                    <span>Rp 150.000</span>
                                    <span>Rp 750.000</span>
                                    <span>Rp 1.500.000</span>
                                </div>
                            </div>

                            {/* Calculation Breakdown Cards */}
                            <div className="mt-6 space-y-2.5 rounded-lg border border-[#2a2e39] bg-[#131722] p-4 text-xs">
                                <div className="flex items-center justify-between text-[#b2b5be]">
                                    <span>
                                        Pendapatan Langganan Bersih (85%):
                                    </span>
                                    <span className="font-mono font-bold text-white">
                                        {formatRupiah(netTraderEarnings)}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between text-[#b2b5be]">
                                    <span>Estimasi Rebate Volume Broker:</span>
                                    <span className="font-mono font-bold text-[#089981]">
                                        +{formatRupiah(estimatedBrokerRebate)}
                                    </span>
                                </div>
                                <div className="border-t border-[#2a2e39] pt-2 text-[#787b86]">
                                    *Platform fee 15% mencakup gateway
                                    pembayaran, server notifikasi sinyal, dan
                                    audit transaksi.
                                </div>
                            </div>

                            {/* Big Revenue Result Banner */}
                            <div className="mt-6 rounded-xl border border-[#2962ff]/40 bg-[#2962ff]/10 p-5 text-center">
                                <span className="text-xs font-semibold tracking-wider text-[#2962ff] uppercase">
                                    Potensi Total Pendapatan Tambahan Anda
                                </span>
                                <div className="mt-1 font-mono text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                                    {formatRupiah(totalPotentialMonthly)}
                                    <span className="text-sm font-normal text-[#b2b5be]">
                                        {' '}
                                        / bulan
                                    </span>
                                </div>
                                <p className="mt-1.5 text-xs text-[#b2b5be]">
                                    Berdasarkan {subscribers} member aktif
                                    dengan setup trading konsisten.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
