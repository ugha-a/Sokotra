import { Head, usePage } from '@inertiajs/react';
import { LandingAnalystPitch } from '@/components/landing/landing-analyst-pitch';
import { LandingBrokerTrust } from '@/components/landing/landing-broker-trust';
import { LandingEcosystem } from '@/components/landing/landing-ecosystem';
import { LandingFaq } from '@/components/landing/landing-faq';
import { LandingHero } from '@/components/landing/landing-hero';
import { LandingLeaderboard } from '@/components/landing/landing-leaderboard';
import type { LeaderboardRow } from '@/types';

type LandingContent = {
    hero_badge?: string;
    hero_title?: string;
    hero_subtitle?: string;
    hero_cta_primary?: string;
    hero_cta_secondary?: string;
    features_title?: string;
    features_subtitle?: string;
    step1_title?: string;
    step1_desc?: string;
    step2_title?: string;
    step2_desc?: string;
    step3_title?: string;
    step3_desc?: string;
    broker_title?: string;
    broker_desc?: string;
    cta_banner_title?: string;
    cta_banner_desc?: string;
    cta_banner_button?: string;
    faq_q1?: string;
    faq_a1?: string;
    faq_q2?: string;
    faq_a2?: string;
    faq_q3?: string;
    faq_a3?: string;
};

type Props = {
    landing?: LandingContent;
    topTraders: LeaderboardRow[];
    stats: {
        traders: number;
        closed_signals: number;
        losing_signals: number;
        brokers: number;
    };
    riskDisclaimer: string;
};

export default function Home({
    landing = {},
    topTraders,
    stats,
    riskDisclaimer,
}: Props) {
    const { auth } = usePage().props;
    const isAuthenticated = !!auth?.user;

    return (
        <div className="w-full">
            <Head title="Sokotra — Platform Analisa & Sinyal Trading Terverifikasi" />

            {/* 1. Hero Section (Inspirasi: TradingView - Show Don't Tell, Dark Mode, Workstation Mockup, Dual CTAs) */}
            <LandingHero
                badge={landing.hero_badge}
                title={landing.hero_title}
                subtitle={landing.hero_subtitle}
                primaryCta={landing.hero_cta_primary}
                secondaryCta={landing.hero_cta_secondary}
                stats={stats}
            />

            {/* 2. Live Leaderboard / Social Proof (Inspirasi: ZuluTrade - Immediate Proof, Win Rate, ROI, Copy/Subscribe) */}
            <LandingLeaderboard
                topTraders={topTraders}
                isAuthenticated={isAuthenticated}
            />

            {/* 3. Ekosistem & Fitur (Inspirasi: eToro - 4 Kolom: Sinyal, Subscription, Marketplace, Ranking) */}
            <LandingEcosystem />

            {/* 4. The Analyst Pitch / Daya Tarik Kreator (Inspirasi: Patreon & Darwinex - Fokus Trading, Slider Simulasi Penghasilan) */}
            <LandingAnalystPitch />

            {/* Pertanyaan Umum & Final Conversion Banner */}
            <LandingFaq
                faq={{
                    q1: landing.faq_q1,
                    a1: landing.faq_a1,
                    q2: landing.faq_q2,
                    a2: landing.faq_a2,
                    q3: landing.faq_q3,
                    a3: landing.faq_a3,
                }}
                ctaBanner={{
                    title: landing.cta_banner_title,
                    desc: landing.cta_banner_desc,
                    button: landing.cta_banner_button,
                }}
            />

            {/* 5. Footer & Broker Partner (Trust Signals - Monochrome Broker Logos, Bappebti, Segregated Accounts, Risk Disclaimer) */}
            <LandingBrokerTrust riskDisclaimer={riskDisclaimer} />
        </div>
    );
}
