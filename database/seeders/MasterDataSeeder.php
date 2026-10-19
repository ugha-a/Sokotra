<?php

namespace Database\Seeders;

use App\Enums\BankType;
use App\Enums\InstrumentCategory;
use App\Enums\SettingGroup;
use App\Enums\SettingType;
use App\Models\Bank;
use App\Models\ContentPage;
use App\Models\FollowerBracket;
use App\Models\Instrument;
use App\Models\PayrollRule;
use App\Models\PlatformSetting;
use App\Models\ProductCategory;
use App\Models\Rank;
use Illuminate\Database\Seeder;

/**
 * Default master data from docs/logic/master-data.md.
 *
 * Idempotent: rows are matched on their natural key and never overwritten,
 * so values an admin has changed survive a re-run.
 */
class MasterDataSeeder extends Seeder
{
    public const RISK_DISCLAIMER = 'Trading forex, emas, dan instrumen derivatif lainnya berisiko tinggi dan dapat menyebabkan kehilangan seluruh modal. Kinerja masa lalu tidak menjamin hasil di masa depan. Analisa di Sotra adalah pendapat masing-masing trader, bukan nasihat investasi. Keputusan dan risiko trading sepenuhnya ada pada kamu.';

    public function run(): void
    {
        $this->seedInstruments();
        $this->seedRanksAndPayrollRules();
        $this->seedFollowerBrackets();
        $this->seedProductCategories();
        $this->seedBanks();
        $this->seedContentPages();
        $this->seedPlatformSettings();
    }

    private function seedInstruments(): void
    {
        $instruments = [
            ['EURUSD', 'Euro / US Dollar', InstrumentCategory::Forex, 0.0001, 5, 1.0, 10, 'EUR/USD'],
            ['GBPUSD', 'British Pound / US Dollar', InstrumentCategory::Forex, 0.0001, 5, 1.5, 10, 'GBP/USD'],
            ['USDJPY', 'US Dollar / Japanese Yen', InstrumentCategory::Forex, 0.01, 3, 1.5, 10, 'USD/JPY'],
            ['AUDUSD', 'Australian Dollar / US Dollar', InstrumentCategory::Forex, 0.0001, 5, 1.5, 10, 'AUD/USD'],
            ['USDCHF', 'US Dollar / Swiss Franc', InstrumentCategory::Forex, 0.0001, 5, 1.8, 10, 'USD/CHF'],
            ['USDCAD', 'US Dollar / Canadian Dollar', InstrumentCategory::Forex, 0.0001, 5, 2.0, 10, 'USD/CAD'],
            ['NZDUSD', 'New Zealand Dollar / US Dollar', InstrumentCategory::Forex, 0.0001, 5, 2.0, 10, 'NZD/USD'],
            ['XAUUSD', 'Gold / US Dollar', InstrumentCategory::Metal, 0.1, 2, 3.0, 20, 'XAU/USD'],
            ['XAGUSD', 'Silver / US Dollar', InstrumentCategory::Metal, 0.01, 3, 3.0, 20, 'XAG/USD'],
        ];

        foreach ($instruments as [$symbol, $name, $category, $pipSize, $digits, $spread, $minSl, $feedSymbol]) {
            Instrument::firstOrCreate(['symbol' => $symbol], [
                'name' => $name,
                'category' => $category,
                'pip_size' => $pipSize,
                'digits' => $digits,
                'spread_pips' => $spread,
                'min_sl_pips' => $minSl,
                'feed_symbol' => $feedSymbol,
                'is_active' => true,
            ]);
        }
    }

    private function seedRanksAndPayrollRules(): void
    {
        $ranks = [
            // level, name, closed, win rate, total R, followers, premium, min price, max price, products
            [1, 'Newbie', 0, 0, 0, 0, false, null, null, false],
            [2, 'Rookie', 20, 45, 0, 10, false, null, null, false],
            [3, 'Pro', 50, 50, 10, 50, false, null, null, true],
            [4, 'Elite', 100, 55, 25, 200, true, 100000, 300000, true],
            [5, 'Master', 200, 58, 50, 500, true, 150000, 500000, true],
            [6, 'Legend', 300, 60, 100, 1000, true, 200000, 1000000, true],
        ];

        foreach ($ranks as [$level, $name, $closed, $winRate, $totalR, $followers, $premium, $minPrice, $maxPrice, $products]) {
            $rank = Rank::firstOrCreate(['level' => $level], [
                'name' => $name,
                'slug' => str($name)->slug()->toString(),
                'min_closed_signals' => $closed,
                'min_win_rate' => $winRate,
                'min_total_r' => $totalR,
                'min_valid_followers' => $followers,
                'max_drawdown_r' => null,
                'can_sell_premium' => $premium,
                'subscription_min_price' => $minPrice,
                'subscription_max_price' => $maxPrice,
                'can_sell_products' => $products,
                'color' => "rank-{$level}",
                'is_active' => true,
            ]);

            // Salary amounts start at zero; the admin sets them per docs/logic/payroll-rewards.md.
            PayrollRule::firstOrCreate(['rank_id' => $rank->id], [
                'base_salary' => 0,
                'per_win_amount' => 0,
                'required_streak' => 10,
                'min_closed_signals_month' => $level >= 3 ? 20 : 0,
                'max_drawdown_r_month' => null,
                'streak_bonus_amount' => 0,
                'max_weekly_wins_counted' => 10,
                'is_active' => true,
            ]);
        }
    }

    private function seedFollowerBrackets(): void
    {
        $brackets = [[0, 99, 0], [100, 499, 250000], [500, 999, 750000], [1000, null, 1500000]];

        foreach ($brackets as [$min, $max, $bonus]) {
            FollowerBracket::firstOrCreate(['min_followers' => $min], [
                'max_followers' => $max,
                'bonus_amount' => $bonus,
                'is_active' => true,
            ]);
        }
    }

    private function seedProductCategories(): void
    {
        foreach (['indikator' => 'Indikator', 'expert-advisor' => 'Expert Advisor (EA)', 'template-script' => 'Template/Script'] as $slug => $name) {
            ProductCategory::firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true]);
        }
    }

    private function seedBanks(): void
    {
        $banks = [
            'BCA' => ['Bank Central Asia (BCA)', BankType::Bank],
            'BRI' => ['Bank Rakyat Indonesia (BRI)', BankType::Bank],
            'BNI' => ['Bank Negara Indonesia (BNI)', BankType::Bank],
            'MANDIRI' => ['Bank Mandiri', BankType::Bank],
            'BSI' => ['Bank Syariah Indonesia (BSI)', BankType::Bank],
            'CIMB' => ['CIMB Niaga', BankType::Bank],
            'PERMATA' => ['Bank Permata', BankType::Bank],
            'DANA' => ['DANA', BankType::Ewallet],
            'OVO' => ['OVO', BankType::Ewallet],
            'GOPAY' => ['GoPay', BankType::Ewallet],
        ];

        foreach ($banks as $code => [$name, $type]) {
            Bank::firstOrCreate(['code' => $code], ['name' => $name, 'type' => $type, 'is_active' => true]);
        }
    }

    private function seedContentPages(): void
    {
        $draft = 'Draft — lengkapi bersama konsultan hukum sebelum dipublikasikan.';

        $pages = [
            'syarat-ketentuan' => ['Syarat & Ketentuan', $draft, false],
            'kebijakan-privasi' => ['Kebijakan Privasi', $draft, false],
            'disclaimer-risiko' => ['Disclaimer Risiko', self::RISK_DISCLAIMER, true],
            'faq' => ['FAQ', $draft, false],
        ];

        foreach ($pages as $slug => [$title, $body, $published]) {
            ContentPage::firstOrCreate(['slug' => $slug], [
                'title' => $title,
                'body' => $body,
                'is_published' => $published,
            ]);
        }
    }

    private function seedPlatformSettings(): void
    {
        $settings = [
            [SettingGroup::Signal, 'signal.min_rr', SettingType::Decimal, 1.0, 'Risk Reward minimum', null],
            [SettingGroup::Signal, 'signal.max_active_per_trader', SettingType::Integer, 5, 'Maksimal sinyal pending + aktif per trader', null],
            [SettingGroup::Signal, 'signal.pending_expiry_hours', SettingType::Integer, 24, 'Pending kedaluwarsa setelah (jam)', null],
            [SettingGroup::Signal, 'signal.max_duration_days', SettingType::Integer, 7, 'Durasi maksimal sinyal aktif (hari)', null],
            [SettingGroup::Signal, 'signal.max_cancel_per_week', SettingType::Integer, 3, 'Maksimal pembatalan pending per minggu', null],
            [SettingGroup::Signal, 'signal.ambiguous_candle_rule', SettingType::Choice, 'sl_first', 'Jika TP & SL tersentuh di candle yang sama', ['sl_first' => 'SL lebih dulu (konservatif)', 'tp_first' => 'TP lebih dulu']],
            [SettingGroup::Signal, 'signal.count_expired_in_streak', SettingType::Boolean, false, 'Sinyal expired/cancelled memutus streak', null],
            [SettingGroup::Signal, 'signal.min_analysis_chars', SettingType::Integer, 30, 'Panjang minimal teks analisa (karakter)', null],
            [SettingGroup::Signal, 'signal.chart_max_mb', SettingType::Integer, 5, 'Ukuran gambar chart maksimal (MB)', null],

            [SettingGroup::Score, 'score.weight_win_rate', SettingType::Decimal, 0.30, 'Bobot win rate', null],
            [SettingGroup::Score, 'score.weight_total_r', SettingType::Decimal, 0.35, 'Bobot total R', null],
            [SettingGroup::Score, 'score.weight_consistency', SettingType::Decimal, 0.20, 'Bobot konsistensi', null],
            [SettingGroup::Score, 'score.weight_followers', SettingType::Decimal, 0.15, 'Bobot followers', null],
            [SettingGroup::Score, 'score.window_days', SettingType::Integer, 90, 'Jendela statistik rank & skor (hari)', null],
            [SettingGroup::Score, 'score.follower_active_days', SettingType::Integer, 30, 'Follower valid jika aktif dalam (hari)', null],
            [SettingGroup::Score, 'score.leaderboard_min_signals', SettingType::Integer, 20, 'Minimal sinyal selesai untuk masuk leaderboard', null],
            [SettingGroup::Score, 'score.consistent_min_signals', SettingType::Integer, 50, 'Minimal sinyal selesai untuk "Paling Konsisten"', null],

            [SettingGroup::Wallet, 'wallet.min_withdraw', SettingType::Money, 100000, 'Minimum penarikan', null],
            [SettingGroup::Wallet, 'wallet.withdraw_fee', SettingType::Money, 6500, 'Biaya penarikan', null],
            [SettingGroup::Wallet, 'wallet.hold_period_days', SettingType::Integer, 7, 'Saldo pending dirilis setelah (hari)', null],
            [SettingGroup::Wallet, 'wallet.max_bank_accounts', SettingType::Integer, 3, 'Maksimal rekening per pengguna', null],

            [SettingGroup::Subscription, 'subscription.fee_percent', SettingType::Percent, 20.0, 'Fee platform', null],
            [SettingGroup::Subscription, 'subscription.durations', SettingType::IntegerList, [1, 3, 6], 'Pilihan durasi (bulan)', null],
            [SettingGroup::Subscription, 'subscription.reminder_days', SettingType::IntegerList, [3, 1], 'Kirim pengingat sebelum berakhir (hari)', null],
            [SettingGroup::Subscription, 'subscription.inactive_alert_days', SettingType::Integer, 14, 'Peringatkan admin jika channel tanpa sinyal premium (hari)', null],

            [SettingGroup::Marketplace, 'marketplace.commission_percent', SettingType::Percent, 15.0, 'Komisi platform', null],
            [SettingGroup::Marketplace, 'marketplace.max_file_mb', SettingType::Integer, 20, 'Ukuran file produk maksimal (MB)', null],

            [SettingGroup::Content, 'content.risk_disclaimer', SettingType::Text, self::RISK_DISCLAIMER, 'Disclaimer risiko', null],
            [SettingGroup::Content, 'content.prohibited_phrases', SettingType::StringList, [
                'pasti profit', 'dijamin untung', 'anti loss', 'tanpa risiko',
                'cepat kaya', 'passive income pasti', 'balik modal', 'win rate 100%',
            ], 'Klaim terlarang', null],

            [SettingGroup::Trader, 'trader.reapply_cooldown_days', SettingType::Integer, 7, 'Jeda pengajuan ulang setelah aplikasi ditolak (hari)', null],
            [SettingGroup::Trader, 'trader.max_main_instruments', SettingType::Integer, 5, 'Maksimal instrumen utama per trader', null],
            [SettingGroup::Trader, 'kyc.max_file_mb', SettingType::Integer, 5, 'Ukuran foto KTP / selfie maksimal (MB)', null],
            [SettingGroup::Trader, 'kyc.min_age', SettingType::Integer, 18, 'Usia minimum (tahun)', null],
            [SettingGroup::Trader, 'kyc.document_link_minutes', SettingType::Integer, 10, 'Masa berlaku link dokumen KYC untuk admin (menit)', null],

            [SettingGroup::Fraud, 'fraud.hedge_window_minutes', SettingType::Integer, 10, 'Jendela waktu sinyal berlawanan dari jaringan yang sama (menit)', null],
            [SettingGroup::Fraud, 'fraud.follower_ip_threshold', SettingType::Integer, 5, 'Jumlah follower baru dari IP yang sama untuk ditandai', null],
            [SettingGroup::Fraud, 'fraud.follower_window_days', SettingType::Integer, 7, 'Jendela waktu follower baru (hari)', null],

            [SettingGroup::Payment, 'payment.bank_name', SettingType::Text, 'Bank Central Asia (BCA)', 'Nama Bank Tujuan Transfer', null],
            [SettingGroup::Payment, 'payment.account_number', SettingType::Text, '1234567890', 'Nomor Rekening Tujuan', null],
            [SettingGroup::Payment, 'payment.account_holder', SettingType::Text, 'PT Sotra Teknologi Indonesia', 'Nama Pemilik Rekening', null],
            [SettingGroup::Payment, 'payment.instructions', SettingType::Text, 'Silakan transfer tepat sesuai nominal yang tertera. Cantumkan kode pembayaran pada berita transfer dan unggah bukti transfer setelah selesai.', 'Instruksi Pembayaran', null],

            [SettingGroup::Landing, 'landing.hero_badge', SettingType::Text, 'Platform Edukasi & Analisa Trading Independen', 'Hero - Lencana Tagline', null],
            [SettingGroup::Landing, 'landing.hero_title', SettingType::Text, 'Platform Analisa & Kinerja Trader Berbasis Data Pasar Nyata', 'Hero - Judul Utama', null],
            [SettingGroup::Landing, 'landing.hero_subtitle', SettingType::Text, 'Transparansi penuh tanpa manipulasi: pantau track record trader analis terverifikasi, pelajari manajemen risiko disiplin, dan trading aman di broker berizin resmi Bappebti.', 'Hero - Subjudul / Deskripsi', null],
            [SettingGroup::Landing, 'landing.hero_cta_primary', SettingType::Text, 'Daftar Sekarang — Gratis', 'Hero - Teks Tombol CTA Utama', null],
            [SettingGroup::Landing, 'landing.hero_cta_secondary', SettingType::Text, 'Pelajari Cara Kerja', 'Hero - Teks Tombol CTA Kedua', null],
            [SettingGroup::Landing, 'landing.features_title', SettingType::Text, 'Standar Transparansi & Manajemen Risiko', 'Keunggulan - Judul Bagian', null],
            [SettingGroup::Landing, 'landing.features_subtitle', SettingType::Text, 'Prinsip utama yang membedakan Sotra: kejujuran data, kepatuhan regulasi, dan tanpa janji palsu.', 'Keunggulan - Subjudul', null],
            [SettingGroup::Landing, 'landing.step1_title', SettingType::Text, '1. Buat Akun Gratis', 'Cara Kerja - Langkah 1 (Judul)', null],
            [SettingGroup::Landing, 'landing.step1_desc', SettingType::Text, 'Daftar akun member dalam hitungan menit untuk mulai menjelajahi ekosistem edukasi dan analisa Sotra.', 'Cara Kerja - Langkah 1 (Deskripsi)', null],
            [SettingGroup::Landing, 'landing.step2_title', SettingType::Text, '2. Akses Analisa & Leaderboard', 'Cara Kerja - Langkah 2 (Judul)', null],
            [SettingGroup::Landing, 'landing.step2_desc', SettingType::Text, 'Login untuk melihat sinyal berargumen teknikal, performa trader teratas dengan statistik jujur, dan tools marketplace.', 'Cara Kerja - Langkah 2 (Deskripsi)', null],
            [SettingGroup::Landing, 'landing.step3_title', SettingType::Text, '3. Trading Mandiri & Disiplin', 'Cara Kerja - Langkah 3 (Judul)', null],
            [SettingGroup::Landing, 'landing.step3_desc', SettingType::Text, 'Terapkan analisa di broker mitra berizin resmi Bappebti dengan kalkulasi lot terukur dan rasio Risk/Reward minimal 1:1.', 'Cara Kerja - Langkah 3 (Deskripsi)', null],
            [SettingGroup::Landing, 'landing.broker_title', SettingType::Text, 'Keamanan Dana & Kemitraan Broker Berizin Resmi Bappebti', 'Broker - Judul Bagian', null],
            [SettingGroup::Landing, 'landing.broker_desc', SettingType::Text, 'Sotra tidak pernah menghimpun, mengelola, atau menerima deposit dana trading kamu. Seluruh eksekusi dan penempatan dana dilakukan secara langsung pada broker partner berizin resmi Bappebti.', 'Broker - Deskripsi', null],
            [SettingGroup::Landing, 'landing.cta_banner_title', SettingType::Text, 'Siap Tingkatkan Kualitas & Kedisiplinan Trading Kamu?', 'CTA Banner - Judul', null],
            [SettingGroup::Landing, 'landing.cta_banner_desc', SettingType::Text, 'Daftar sekarang untuk membuka akses penuh ke leaderboard, sinyal real-time, dan produk marketplace.', 'CTA Banner - Deskripsi', null],
            [SettingGroup::Landing, 'landing.cta_banner_button', SettingType::Text, 'Daftar Akun Member Sekarang', 'CTA Banner - Teks Tombol', null],
            [SettingGroup::Landing, 'landing.faq_q1', SettingType::Text, 'Apakah Sotra menjanjikan keuntungan atau profit pasti?', 'FAQ 1 - Pertanyaan', null],
            [SettingGroup::Landing, 'landing.faq_a1', SettingType::Text, 'Tidak. Sotra menentang keras janji profit palsu. Trading instrumen derivatif berisiko tinggi. Kami menyediakan track record transparan agar kamu belajar dari data objektif.', 'FAQ 1 - Jawaban', null],
            [SettingGroup::Landing, 'landing.faq_q2', SettingType::Text, 'Apakah Sotra mengelola atau menerima dana deposit pengguna?', 'FAQ 2 - Pertanyaan', null],
            [SettingGroup::Landing, 'landing.faq_a2', SettingType::Text, 'Tidak sama sekali. Sotra bukan broker dan tidak menghimpun dana pengguna. Dana trading kamu tetap berada di akun terpisah broker resmi berizin Bappebti.', 'FAQ 2 - Jawaban', null],
            [SettingGroup::Landing, 'landing.faq_q3', SettingType::Text, 'Bagaimana cara melihat sinyal dan performa lengkap trader?', 'FAQ 3 - Pertanyaan', null],
            [SettingGroup::Landing, 'landing.faq_a3', SettingType::Text, 'Seluruh data internal platform seperti Sinyal, Leaderboard, dan Profil Analis dapat diakses lengkap setelah kamu mendaftar dan login ke dalam platform.', 'FAQ 3 - Jawaban', null],
        ];

        foreach ($settings as $order => [$group, $key, $type, $value, $label, $options]) {
            PlatformSetting::firstOrCreate(['key' => $key], [
                'group' => $group,
                'type' => $type,
                'label' => $label,
                'options' => $options,
                'value' => $value,
                'sort_order' => $order,
            ]);
        }
    }
}
