# Rekrutmen & Onboarding Trader

Sokotra merekrut trader untuk memposting analisa. Setiap user terdaftar (member) bisa mendaftar menjadi trader.

## Tabel `trader_profiles`

| Kolom | Keterangan |
|---|---|
| user_id | unik |
| display_name, slug | unik, dipakai di URL profil `/traders/{slug}` |
| bio, trading_style | `scalping`, `intraday`, `swing`, `position` |
| experience_years | |
| main_instruments | json instrument_id[] |
| status | `applied`, `approved`, `rejected`, `suspended` |
| rank_id | default rank level 1 (Newbie) saat approved |
| is_featured | dikurasi admin |
| is_flagged | ditandai fraud, dikecualikan dari payroll & leaderboard |
| applied_at, approved_at, reviewed_by, rejection_reason | |

## Alur

1. Member membuka **Jadi Trader** → isi profil + setuju S&K trader (dicatat di `user_consents`).
2. Wajib mengajukan KYC (lihat `compliance.md`) di alur yang sama. Approval trader **tidak bisa** dilakukan sebelum KYC `verified`.
3. Admin review → `approved`: user mendapat role `trader`, rank = Newbie. Atau `rejected` dengan alasan; boleh mengajukan ulang setelah 7 hari.
4. Trader yang `suspended` tidak bisa memposting sinyal. Sinyal terbuka tetap dievaluasi agar track record tetap jujur. Profil disembunyikan dari leaderboard.

## Status → Kemampuan

| Kemampuan | Syarat |
|---|---|
| Posting sinyal free | `approved` |
| Posting sinyal premium & buka channel | `approved` + rank `can_sell_premium` |
| Jual produk | `approved` + rank `can_sell_products` |
| Masuk payroll | `approved` + kelayakan di `payroll-rewards.md` |
