import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { ArrowRight, ChevronDown, HelpCircle, Sparkles } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';

type LandingFaqProps = {
    faq?: {
        q1?: string;
        a1?: string;
        q2?: string;
        a2?: string;
        q3?: string;
        a3?: string;
    };
    ctaBanner?: {
        title?: string;
        desc?: string;
        button?: string;
    };
};

export function LandingFaq({ faq = {}, ctaBanner = {} }: LandingFaqProps) {
    const [openIndex, setOpenIndex] = useState<number | null>(0);

    const questions = [
        {
            q: faq.q1 || 'Apakah SooTa menjamin keuntungan dalam trading?',
            a:
                faq.a1 ||
                'Tidak ada jaminan profit dalam trading. SooTa berkomitmen memberikan transparansi penuh di mana setiap sinyal dan riwayat loss dicatat apa adanya agar Anda dapat membuat keputusan berbasis data obyektif.',
        },
        {
            q:
                faq.q2 ||
                'Apakah SooTa menghimpun dana atau memegang uang trading saya?',
            a:
                faq.a2 ||
                'Tidak. SooTa bukan manajer investasi dan tidak mengelola dana nasabah. Anda bertransaksi langsung pada pialang berjangka resmi rekanan yang diawasi oleh Bappebti melalui segregated account.',
        },
        {
            q:
                faq.q3 ||
                'Bagaimana cara mendaftar sebagai analis penyedia sinyal?',
            a:
                faq.a3 ||
                'Daftar akun gratis, lengkapi verifikasi identitas (KYC), dan ajukan permohonan sebagai trader. Setelah disetujui, Anda dapat mulai mempublikasikan sinyal dan memonetisasi strategi Anda.',
        },
    ];

    const ctaTitle =
        ctaBanner.title || 'Mulai Trading dengan Transparansi Penuh Hari Ini';
    const ctaDesc =
        ctaBanner.desc ||
        'Daftar akun gratis tanpa biaya langganan awal. Jelajahi puluhan analis teruji dan evaluasi sinyal berdasarkan data rekam jejak riil.';
    const ctaBtn = ctaBanner.button || 'Daftar Akun Gratis Sekarang';

    return (
        <section
            id="faq"
            className="border-b border-[#2a2e39] bg-[#0e1118] py-16 lg:py-24"
        >
            <div className="mx-auto max-w-5xl px-4 sm:px-6">
                {/* FAQ Header */}
                <div className="text-center">
                    <div className="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wider text-[#2962ff] uppercase">
                        <HelpCircle className="h-3.5 w-3.5" />
                        <span>Pertanyaan Umum</span>
                    </div>
                    <h2 className="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Ketahui Lebih Banyak tentang Standar SooTa
                    </h2>
                    <p className="mx-auto mt-2 max-w-xl text-sm text-[#b2b5be]">
                        Semua informasi penting terkait sistem kerja platform,
                        keterbukaan risiko, dan kemitraan broker.
                    </p>
                </div>

                {/* FAQ Accordion List */}
                <div className="mt-10 space-y-3">
                    {questions.map((item, idx) => {
                        const isOpen = openIndex === idx;
                        return (
                            <div
                                key={idx}
                                className="overflow-hidden rounded-xl border border-[#2a2e39] bg-[#1e222d] transition-colors"
                            >
                                <button
                                    onClick={() =>
                                        setOpenIndex(isOpen ? null : idx)
                                    }
                                    className="flex w-full items-center justify-between px-5 py-4 text-left text-sm font-semibold text-white sm:text-base"
                                >
                                    <span>{item.q}</span>
                                    <ChevronDown
                                        className={`h-4 w-4 shrink-0 text-[#787b86] transition-transform duration-200 ${
                                            isOpen
                                                ? 'rotate-180 text-[#2962ff]'
                                                : ''
                                        }`}
                                    />
                                </button>
                                {isOpen && (
                                    <div className="border-t border-[#2a2e39] bg-[#131722]/50 px-5 py-4 text-xs leading-relaxed text-[#b2b5be] sm:text-sm">
                                        {item.a}
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>

                {/* Final High-Conversion Banner */}
                <div className="mt-16 rounded-2xl border border-[#2962ff]/40 bg-[#1e222d] p-8 text-center sm:p-12">
                    <div className="inline-flex items-center gap-1.5 rounded-full border border-[#2962ff]/40 bg-[#2962ff]/10 px-3 py-1 text-xs font-medium text-[#2962ff]">
                        <Sparkles className="h-3.5 w-3.5" />
                        <span>Ekosistem Terbuka & Bebas Manipulasi</span>
                    </div>
                    <h3 className="mt-4 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        {ctaTitle}
                    </h3>
                    <p className="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-[#b2b5be] sm:text-base">
                        {ctaDesc}
                    </p>
                    <div className="mt-8 flex justify-center">
                        <Button
                            size="lg"
                            asChild
                            className="h-12 bg-[#2962ff] px-8 text-base font-semibold text-white shadow-lg hover:bg-[#1e53e5]"
                        >
                            <Link
                                href={register()}
                                className="inline-flex items-center gap-2"
                            >
                                <span>{ctaBtn}</span>
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    );
}
