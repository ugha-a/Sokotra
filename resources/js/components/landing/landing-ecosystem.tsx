import {
    Activity,
    Check,
    Radio,
    ShieldCheck,
    Sparkles,
    Store,
} from 'lucide-react';

export function LandingEcosystem() {
    const features = [
        {
            icon: Radio,
            badge: 'Real-time Alerts',
            title: 'Sinyal & Copy Trade',
            desc: 'Notifikasi sinyal instan lengkap dengan entry, Stop Loss, dan Take Profit. Salin eksekusi analis favorit Anda dengan kontrol risiko terukur.',
            bullets: ['Eksekusi cepat tanpa delay', 'Kalkulator lot otomatis'],
        },
        {
            icon: Sparkles,
            badge: 'Eksklusif Member',
            title: 'Langganan Premium',
            desc: 'Akses channel analisa mendalam dan pandangan teknikal dari trader pro. Pembayaran aman dan transparan tanpa komitmen jangka panjang.',
            bullets: ['Edukasi & setup harian', 'Diskusi strategi terarah'],
        },
        {
            icon: Store,
            badge: 'Verified Tools',
            title: 'Marketplace Indikator & EA',
            desc: 'Dapatkan indikator kustom, template chart, dan robot trading otomatis (EA) yang telah lolos pengujian teknis dan bebas dari malware berbahaya.',
            bullets: [
                'Download instan berkas asli',
                'Update berkala dari developer',
            ],
        },
        {
            icon: ShieldCheck,
            badge: 'Zero Manipulation',
            title: 'Ranking & Transparansi Data',
            desc: 'Sistem audit otomatis menghitung performa analis dari harga pasar riil. Riwayat loss dan drawdown ditampilkan jujur tanpa sensor.',
            bullets: [
                'Skor kepatuhan risiko riil',
                'Riwayat transaksi permanen',
            ],
        },
    ];

    return (
        <section
            id="ekosistem"
            className="border-b border-[#2a2e39] bg-[#131722] py-16 lg:py-24"
        >
            <div className="mx-auto max-w-7xl px-4 sm:px-6">
                {/* Header */}
                <div className="text-center">
                    <div className="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wider text-[#2962ff] uppercase">
                        <Activity className="h-3.5 w-3.5" />
                        <span>Ekosistem Terintegrasi</span>
                    </div>
                    <h2 className="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Satu Platform untuk Seluruh Kebutuhan Trading Anda
                    </h2>
                    <p className="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-[#b2b5be] sm:text-base">
                        Inspirasi model eToro dengan pendekatan transparansi
                        radikal: menghubungkan trader ritel, analis teruji,
                        pengembang alat, dan pialang berjangka resmi.
                    </p>
                </div>

                {/* 4-Column Feature Grid (Tailwind native) */}
                <div className="mt-12 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    {features.map((item, index) => {
                        const Icon = item.icon;
                        return (
                            <div
                                key={index}
                                className="group relative flex flex-col justify-between rounded-xl border border-[#2a2e39] bg-[#1e222d] p-6 transition-all hover:border-[#2962ff]/60 hover:bg-[#1e222d]/90"
                            >
                                <div>
                                    {/* Icon & Badge Header */}
                                    <div className="flex items-center justify-between">
                                        <div className="flex h-11 w-11 items-center justify-center rounded-lg border border-[#2a2e39] bg-[#131722] text-[#2962ff] transition-colors group-hover:border-[#2962ff]/40 group-hover:bg-[#2962ff]/10">
                                            <Icon className="h-5 w-5" />
                                        </div>
                                        <span className="rounded bg-[#131722] px-2 py-0.5 font-mono text-[10px] font-medium text-[#787b86]">
                                            {item.badge}
                                        </span>
                                    </div>

                                    {/* Title */}
                                    <h3 className="mt-5 text-lg font-bold tracking-tight text-white">
                                        {item.title}
                                    </h3>

                                    {/* Description (2 lines punchy) */}
                                    <p className="mt-2 text-sm leading-relaxed text-[#b2b5be]">
                                        {item.desc}
                                    </p>
                                </div>

                                {/* Feature Bullets */}
                                <div className="mt-6 border-t border-[#2a2e39] pt-4">
                                    <ul className="space-y-1.5 text-xs text-[#787b86]">
                                        {item.bullets.map((b, bIdx) => (
                                            <li
                                                key={bIdx}
                                                className="flex items-center gap-1.5"
                                            >
                                                <Check className="h-3.5 w-3.5 shrink-0 text-[#089981]" />
                                                <span className="text-[#d1d4dc]">
                                                    {b}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
