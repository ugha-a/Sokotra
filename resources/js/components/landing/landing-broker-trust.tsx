import {
    AlertTriangle,
    Building2,
    Lock,
    Scale,
    ShieldAlert,
    ShieldCheck,
} from 'lucide-react';

type LandingBrokerTrustProps = {
    riskDisclaimer: string;
};

export function LandingBrokerTrust({
    riskDisclaimer,
}: LandingBrokerTrustProps) {
    // Monochrome partner representations (clean, muted, professional)
    const brokerPartners = [
        {
            name: 'Pialang Berjangka Resmi Bappebti',
            license: 'No. 001/BAPPEBTI/SI/05/2018',
            clearing: 'Kliring Berjangka Indonesia (KBI)',
            tag: 'Segregated Account 100%',
        },
        {
            name: 'Mitra Kliring Komoditi & Derivatif',
            license: 'No. 042/BAPPEBTI/SP-L/09/2020',
            clearing: 'Indonesia Clearing House (ICH)',
            tag: 'Regulated Custodian',
        },
        {
            name: 'Pialang Derivatif Keuangan Global',
            license: 'No. 128/BAPPEBTI/SI/12/2021',
            clearing: 'Bursa Berjangka Jakarta (JFX)',
            tag: 'Tier-1 Liquidity',
        },
    ];

    return (
        <section
            id="broker-partner"
            className="border-b border-[#2a2e39] bg-[#131722] py-16 lg:py-20"
        >
            <div className="mx-auto max-w-7xl px-4 sm:px-6">
                {/* Section Header */}
                <div className="text-center">
                    <div className="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wider text-[#787b86] uppercase">
                        <Scale className="h-3.5 w-3.5" />
                        <span>Kemitraan & Integritas Legalitas</span>
                    </div>
                    <h2 className="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Standar Broker Teregulasi & Perlindungan Nasabah
                    </h2>
                    <p className="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-[#787b86] sm:text-base">
                        Sokotra tidak pernah menyimpan dana trading Anda. Anda
                        membuka akun langsung pada pialang berjangka resmi yang
                        mematuhi hukum Republik Indonesia.
                    </p>
                </div>

                {/* Monochrome Broker Partner Cards (Abu-abu / redup agar tidak mengganggu hierarki) */}
                <div className="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    {brokerPartners.map((broker, idx) => (
                        <div
                            key={idx}
                            className="flex flex-col justify-between rounded-xl border border-[#2a2e39]/80 bg-[#1e222d]/40 p-5 opacity-75 transition-all hover:border-[#363a45] hover:bg-[#1e222d] hover:opacity-100"
                        >
                            <div>
                                <div className="flex items-center justify-between text-xs text-[#787b86]">
                                    <span className="font-mono">
                                        {broker.clearing}
                                    </span>
                                    <span className="rounded bg-[#131722] px-2 py-0.5 text-[10px] text-[#787b86]">
                                        {broker.tag}
                                    </span>
                                </div>
                                <h4 className="mt-3 text-base font-semibold text-[#d1d4dc]">
                                    {broker.name}
                                </h4>
                                <p className="mt-1 font-mono text-xs text-[#787b86]">
                                    Izin: {broker.license}
                                </p>
                            </div>
                            <div className="mt-4 flex items-center gap-1.5 border-t border-[#2a2e39]/60 pt-3 text-[11px] text-[#787b86]">
                                <ShieldCheck className="h-3.5 w-3.5 text-[#089981]" />
                                <span>Diawasi Resmi Bappebti & Kemendag</span>
                            </div>
                        </div>
                    ))}
                </div>

                {/* 3 Core Trust Pillars */}
                <div className="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div className="flex items-start gap-3.5 rounded-lg border border-[#2a2e39] bg-[#1e222d] p-4">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#2a2e39] bg-[#131722] text-[#787b86]">
                            <Building2 className="h-4 w-4" />
                        </div>
                        <div>
                            <h5 className="text-sm font-semibold text-white">
                                Rekening Terpisah (Segregated Account)
                            </h5>
                            <p className="mt-1 text-xs leading-relaxed text-[#787b86]">
                                Dana margin Anda disimpan terpisah di bank
                                kustodian resmi dan diawasi oleh lembaga kliring
                                berjangka pemerintah.
                            </p>
                        </div>
                    </div>

                    <div className="flex items-start gap-3.5 rounded-lg border border-[#2a2e39] bg-[#1e222d] p-4">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#2a2e39] bg-[#131722] text-[#787b86]">
                            <Lock className="h-4 w-4" />
                        </div>
                        <div>
                            <h5 className="text-sm font-semibold text-white">
                                Non-Custodial Technology
                            </h5>
                            <p className="mt-1 text-xs leading-relaxed text-[#787b86]">
                                Sokotra adalah penyedia infrastruktur informasi
                                dan perangkat lunak sinyal, bukan penghimpun
                                dana investasi masyarakat.
                            </p>
                        </div>
                    </div>

                    <div className="flex items-start gap-3.5 rounded-lg border border-[#2a2e39] bg-[#1e222d] p-4">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#2a2e39] bg-[#131722] text-[#787b86]">
                            <ShieldAlert className="h-4 w-4" />
                        </div>
                        <div>
                            <h5 className="text-sm font-semibold text-white">
                                Batas Risiko & Manajemen Drawdown
                            </h5>
                            <p className="mt-1 text-xs leading-relaxed text-[#787b86]">
                                Semua sinyal wajib mencantumkan Stop Loss
                                eksplisit. Sistem membatasi rasio Risk-Reward
                                minimum 1:1.
                            </p>
                        </div>
                    </div>
                </div>

                {/* Regulatory Risk Notice Box */}
                <div className="mt-10 rounded-xl border border-[#2a2e39] bg-[#131722] p-5 text-xs leading-relaxed text-[#787b86]">
                    <div className="flex items-center gap-2 font-semibold text-[#f23645]">
                        <AlertTriangle className="h-4 w-4 shrink-0" />
                        <span className="tracking-wider uppercase">
                            Pemberitahuan Risiko Regulasi Perdagangan Berjangka
                        </span>
                    </div>
                    <p className="mt-2 text-[#787b86]">{riskDisclaimer}</p>
                </div>
            </div>
        </section>
    );
}
