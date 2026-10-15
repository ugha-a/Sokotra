# Rekrutmen & Onboarding Trader

Sokotra merekrut trader untuk memposting analisa. Setiap user terdaftar (member) bisa mendaftar menjadi trader.

## Tabel `trader_profiles`

| Kolom                                                               | Keterangan                                              |
| ------------------------------------------------------------------- | ------------------------------------------------------- |
| user_id                                                             | unik                                                    |
| display_name, slug                                                  | unik, dipakai di URL profil `/traders/{slug}`           |
| bio, trading_style                                                  | `scalping`, `intraday`, `swing`, `position`             |
| experience_years                                                    |                                                         |
| main_instruments                                                    | json instrument_id[]                                    |
| status                                                              | `applied`, `approved`, `rejected`, `suspended`          |
| rank_id                                                             | default rank level 1 (Newbie) saat approved             |
| is_featured                                                         | dikurasi admin                                          |
| is_flagged                                                          | ditandai fraud, dikecualikan dari payroll & leaderboard |
| applied_at, approved_at, reviewed_at, reviewed_by, rejection_reason |                                                         |
| suspended_at, suspension_reason                                     | diisi saat ditangguhkan                                 |

## Alur

1. Member membuka **Jadi Trader** (`/become-trader`) → satu form: profil trader + KYC + persetujuan S&K dan disclaimer risiko (keduanya dicatat di `user_consents`, versi = `updated_at` halaman konten terkait).
2. Validasi: bio dicek terhadap `content.prohibited_phrases`; instrumen utama harus aktif, maksimal `trader.max_main_instruments`; aturan KYC di `compliance.md`.
3. Admin review di `/admin/traders/{id}` dalam dua langkah terpisah: **verifikasi/tolak KYC**, lalu **setujui/tolak aplikasi**. Approval trader **tidak bisa** dilakukan sebelum KYC `verified`.
4. `approved`: user mendapat role `trader`, rank = rank aktif dengan level terendah (seed: Newbie). `rejected`: wajib alasan; boleh mengajukan ulang setelah `trader.reapply_cooldown_days` hari sejak direview.
5. KYC ditolak → pemohon bisa langsung mengirim ulang form (status aplikasi tetap `applied`); data KYC lama diganti dan file lamanya dihapus.
6. Trader yang `suspended` (wajib alasan) tidak bisa memposting sinyal. Sinyal terbuka tetap dievaluasi agar track record tetap jujur. Profil disembunyikan dari leaderboard. Admin bisa mengaktifkan kembali.
7. Setiap keputusan admin dicatat di `admin_audit_logs` (`kyc.verified`, `kyc.rejected`, `trader.approved`, `trader.rejected`, `trader.suspended`, `trader.reinstated`) dan pemohon menerima email (`KycReviewed`, `TraderApplicationReviewed`).

Slug profil dibuat dari nama tampilan saat pertama mengajukan (ditambah `-2`, `-3`, … jika bentrok) dan tidak berubah setelahnya, agar URL profil stabil.

## Status → Kemampuan

| Kemampuan                             | Syarat                                         |
| ------------------------------------- | ---------------------------------------------- |
| Posting sinyal free                   | `approved`                                     |
| Posting sinyal premium & buka channel | `approved` + rank `can_sell_premium`           |
| Jual produk                           | `approved` + rank `can_sell_products`          |
| Masuk payroll                         | `approved` + kelayakan di `payroll-rewards.md` |
