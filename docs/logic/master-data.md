# Master Data

Semua angka dan pilihan bisnis disimpan di master dan dikelola dari **Admin → Master Data**. Kode **dilarang** meng-hardcode nilai-nilai ini. Nilai default di bawah diisi oleh seeder dan boleh diubah admin.

Aturan umum:

- Setiap master punya kolom `is_active` (Halaman Konten memakai `is_published`, Platform Settings tidak punya). Data yang sudah dipakai transaksi **tidak dihapus**, cukup dinonaktifkan. Admin UI tidak menyediakan tombol hapus.
- Nilai default dibuat oleh migration `seed_master_defaults` (memanggil `MasterDataSeeder`, idempotent: tidak menimpa nilai yang sudah diubah admin). Default baru di masa depan = migration baru.
- Implementasi admin: setiap master didefinisikan sebagai class di `app/MasterData/Resources/` (lihat `architecture.md`).
- Perubahan master **tidak mengubah data historis**. Contoh: sinyal yang sudah closed tetap memakai `pip_size` saat ia dibuat (disalin ke sinyal).
- Setiap perubahan master dicatat di `admin_audit_logs` (siapa, kapan, nilai lama/baru).

---

## 1. Instruments — `instruments`

| Kolom       | Tipe          | Keterangan                                         |
| ----------- | ------------- | -------------------------------------------------- |
| symbol      | string unik   | `EURUSD`, `XAUUSD`                                 |
| name        | string        | `Euro / US Dollar`                                 |
| category    | enum          | `forex`, `metal`, `index`, `crypto`, `commodity`   |
| pip_size    | decimal(18,5) | `0.0001` (EURUSD), `0.01` (USDJPY), `0.1` (XAUUSD) |
| digits      | tinyint       | Jumlah desimal harga (5, 3, 2)                     |
| spread_pips | decimal(8,2)  | Spread default untuk evaluasi                      |
| min_sl_pips | decimal(8,2)  | Jarak SL minimum                                   |
| feed_symbol | string        | Kode simbol di provider price feed                 |
| is_active   | bool          |                                                    |

Seed: EURUSD, GBPUSD, USDJPY, AUDUSD, USDCHF, USDCAD, NZDUSD, XAUUSD, XAGUSD.

## 2. Signal Rules — `platform_settings` group `signal`

| Key                            | Default    | Keterangan                                            |
| ------------------------------ | ---------- | ----------------------------------------------------- |
| signal.min_rr                  | 1.00       | RR minimum                                            |
| signal.max_active_per_trader   | 5          | Sinyal pending + active maksimum                      |
| signal.pending_expiry_hours    | 24         | Pending yang tidak tersentuh → expired                |
| signal.max_duration_days       | 7          | Active melewati batas → closed_timeout di harga pasar |
| signal.max_cancel_per_week     | 3          | Batas pembatalan pending                              |
| signal.ambiguous_candle_rule   | `sl_first` | Jika satu candle menyentuh TP & SL                    |
| signal.count_expired_in_streak | false      | Expired/cancelled tidak memutus streak                |
| signal.min_analysis_chars      | 30         | Panjang minimal teks analisa (karakter)               |
| signal.chart_max_mb            | 5          | Ukuran gambar chart maksimal (MB)                     |

## 3. Ranks — `ranks`

| Kolom                  | Tipe                  | Keterangan                              |
| ---------------------- | --------------------- | --------------------------------------- |
| name, slug             | string                |                                         |
| level                  | tinyint unik          | Urutan (1 = terendah)                   |
| min_closed_signals     | int                   | Minimal sinyal closed (window 90 hari)  |
| min_win_rate           | decimal(5,2)          | %                                       |
| min_total_r            | decimal(8,2)          | Total R-multiple (window 90 hari)       |
| min_valid_followers    | int                   |                                         |
| max_drawdown_r         | decimal(8,2) nullable | Batas drawdown (window 90 hari)         |
| can_sell_premium       | bool                  | Boleh membuka channel premium           |
| subscription_min_price | bigint nullable       | Rp / bulan                              |
| subscription_max_price | bigint nullable       | Rp / bulan                              |
| can_sell_products      | bool                  | Boleh jual di marketplace               |
| color                  | string                | Token warna badge (lihat design-system) |

Seed default:

| level | name   | closed | win rate | total R | followers | premium | harga Rp/bln      | produk |
| ----- | ------ | ------ | -------- | ------- | --------- | ------- | ----------------- | ------ |
| 1     | Newbie | 0      | 0        | 0       | 0         | ✗       | –                 | ✗      |
| 2     | Rookie | 20     | 45       | 0       | 10        | ✗       | –                 | ✗      |
| 3     | Pro    | 50     | 50       | 10      | 50        | ✗       | –                 | ✓      |
| 4     | Elite  | 100    | 55       | 25      | 200       | ✓       | 100.000–300.000   | ✓      |
| 5     | Master | 200    | 58       | 50      | 500       | ✓       | 150.000–500.000   | ✓      |
| 6     | Legend | 300    | 60       | 100     | 1.000     | ✓       | 200.000–1.000.000 | ✓      |

## 4. Score Weights — `platform_settings` group `score`

| Key                           | Default |                                                   |
| ----------------------------- | ------- | ------------------------------------------------- |
| score.weight_win_rate         | 0.30    |                                                   |
| score.weight_total_r          | 0.35    |                                                   |
| score.weight_consistency      | 0.20    | Kebalikan dari drawdown & variansi mingguan       |
| score.weight_followers        | 0.15    |                                                   |
| score.window_days             | 90      | Jendela statistik untuk rank & skor               |
| score.follower_active_days    | 30      | Definisi follower valid                           |
| score.leaderboard_min_signals | 20      | Minimal sinyal selesai untuk masuk leaderboard    |
| score.consistent_min_signals  | 50      | Minimal sinyal selesai untuk tab Paling Konsisten |

## 5. Payroll Rules — `payroll_rules` (satu baris per rank)

| Kolom                    | Tipe         | Keterangan                                       |
| ------------------------ | ------------ | ------------------------------------------------ |
| rank_id                  | fk unik      |                                                  |
| base_salary              | bigint       | Gaji pokok Rp/bulan                              |
| per_win_amount           | bigint       | Rp per analisa benar (TP hit)                    |
| required_streak          | int          | Default 10                                       |
| min_closed_signals_month | int          | Minimal sinyal closed di bulan itu               |
| max_drawdown_r_month     | decimal(8,2) |                                                  |
| streak_bonus_amount      | bigint       | Bonus sekali per bulan jika streak tercapai      |
| max_weekly_wins_counted  | int          | Batas TP hit yang dibayar per minggu (anti-spam) |

Seed: Newbie & Rookie tidak digaji (`base_salary = 0`, `per_win_amount = 0`). Nilai Pro ke atas diisi admin.

## 6. Follower Brackets — `follower_brackets`

| Kolom         | Tipe                            |
| ------------- | ------------------------------- |
| min_followers | int                             |
| max_followers | int nullable (tanpa batas atas) |
| bonus_amount  | bigint                          |

Seed: 0–99 → 0, 100–499 → 250.000, 500–999 → 750.000, 1.000+ → 1.500.000.

## 7. Platform Settings — `platform_settings`

Key-value (`key` unik, `value` json, `group`, `type`, `label`, `description`, `options`, `sort_order`). Diakses lewat `App\Support\Settings::get('key')`: nilai sudah bertipe sesuai `type`, di-cache, cache dibersihkan saat update, dan key yang tidak dikenal melempar exception.

Tipe (`App\Enums\SettingType`): `integer`, `decimal`, `money`, `percent` (0–100), `boolean`, `text`, `choice` (`options` = map nilai → label), `integer_list`, `string_list`. Validasi tambahan: total `score.weight_*` harus 1,00.

Update lewat `PUT /admin/settings` dengan payload bersarang per grup: `settings[wallet][min_withdraw]=150000`.

| Key                              | Default      | Keterangan                                                               |
| -------------------------------- | ------------ | ------------------------------------------------------------------------ |
| wallet.min_withdraw              | 100000       | Rp                                                                       |
| wallet.withdraw_fee              | 6500         | Rp per withdraw                                                          |
| wallet.hold_period_days          | 7            | Pendapatan pending → available                                           |
| wallet.max_bank_accounts         | 3            | Maksimal rekening per pengguna                                           |
| subscription.fee_percent         | 20.00        | Fee platform                                                             |
| subscription.durations           | [1, 3, 6]    | Bulan                                                                    |
| subscription.reminder_days       | [3, 1]       | Kirim pengingat sebelum langganan berakhir (hari)                        |
| subscription.inactive_alert_days | 14           | Peringatkan admin jika channel bersubscriber tanpa sinyal premium (hari) |
| marketplace.commission_percent   | 15.00        |                                                                          |
| marketplace.max_file_mb          | 20           |                                                                          |
| content.risk_disclaimer          | teks         | Lihat `ui-ux/copywriting.md`                                             |
| content.prohibited_phrases       | daftar frasa | Klaim terlarang (lihat `ui-ux/copywriting.md`)                           |
| trader.reapply_cooldown_days     | 7            | Jeda pengajuan ulang setelah aplikasi trader ditolak (hari)              |
| trader.max_main_instruments      | 5            | Maksimal instrumen utama per trader                                      |
| kyc.max_file_mb                  | 5            | Ukuran foto KTP / selfie maksimal (MB)                                   |
| kyc.min_age                      | 18           | Usia minimum pendaftar KYC (tahun)                                       |
| kyc.document_link_minutes        | 10           | Masa berlaku link dokumen KYC untuk admin (menit)                        |

## 8. Brokers — `brokers`

| Kolom                 | Keterangan                                             |
| --------------------- | ------------------------------------------------------ |
| name, slug, logo_url  |                                                        |
| regulator_license_no  | Nomor izin Bappebti (wajib)                            |
| website_url           |                                                        |
| affiliate_url         | Link IB, mendukung placeholder `{ref}`                 |
| account_types         | json: `[{name, min_deposit, spread_from, commission}]` |
| description           |                                                        |
| rebate_per_lot_usd    | decimal(8,2)                                           | Informasi internal |
| is_active, sort_order |                                                        |

## 9. Product Categories — `product_categories`

`name`, `slug`, `is_active`. Seed: Indikator, Expert Advisor (EA), Template/Script.
Platform produk (enum di kode, bukan master): `mt4`, `mt5`, `tradingview`.

## 10. Banks — `banks`

`code`, `name`, `is_active`. Seed: BCA, BRI, BNI, Mandiri, BSI, CIMB Niaga, Permata, + e-wallet (DANA, OVO, GoPay) bila didukung disbursement.

## 11. Content Pages — `content_pages`

`slug` (`syarat-ketentuan`, `kebijakan-privasi`, `disclaimer-risiko`, `faq`), `title`, `body` (markdown), `is_published`.

---

## Siapa Memakai Apa

| Modul        | Master yang dibaca                                                                                    |
| ------------ | ----------------------------------------------------------------------------------------------------- |
| Sinyal       | Instruments, Signal Rules                                                                             |
| Ranking      | Ranks, Score Weights                                                                                  |
| Payroll      | Payroll Rules, Follower Brackets, Ranks                                                               |
| Wallet       | Platform Settings (wallet.*), Banks                                                                   |
| Subscription | Ranks (harga & izin), Platform Settings (subscription.*)                                              |
| Marketplace  | Ranks (izin), Product Categories, Platform Settings (marketplace.*)                                   |
| Broker       | Brokers                                                                                               |
| Publik       | Content Pages, `content.risk_disclaimer`                                                              |
| Trader & KYC | Instruments, Ranks (rank awal), Platform Settings (`trader.*`, `kyc.*`, `content.prohibited_phrases`) |
