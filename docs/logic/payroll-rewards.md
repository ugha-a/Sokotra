# Gaji Bulanan & Reward Trader

Trader dibayar bulanan berdasarkan **jumlah analisa benar (per minggu)**, **jumlah follower valid**, dan **rank**. Syaratnya, trader pernah mencapai **streak N kemenangan berturut-turut** (default 10) dalam bulan tersebut. Semua angka berasal dari master `payroll_rules` dan `follower_brackets`.

## Kelayakan (semua harus terpenuhi)

1. Trader profile `approved` dan **KYC `verified`**.
2. Rank trader (rank saat payroll dijalankan) punya baris `payroll_rules` dengan `base_salary > 0` atau `per_win_amount > 0`.
3. `best_streak_in_month ≥ required_streak`. Streak dihitung hanya dari sinyal yang closed di bulan itu, jadi streak dari bulan sebelumnya tidak terbawa.
4. `closed_signals_in_month ≥ min_closed_signals_month`.
5. `max_drawdown_r_in_month ≤ max_drawdown_r_month`.
6. Trader tidak sedang ditandai fraud (`trader_profiles.is_flagged = false`).

Jika tidak layak, item payroll tetap dibuat dengan `amount = 0` dan alasan (`ineligible_reasons` json) agar transparan.

## Rumus

```
weekly_paid_wins = Σ per minggu ISO dalam bulan: min(wins_minggu_itu, max_weekly_wins_counted)

gaji = base_salary[rank]
     + weekly_paid_wins × per_win_amount[rank]
     + follower_bracket_bonus(valid_followers saat payroll dijalankan)
     + streak_bonus_amount[rank]
```

- Sinyal masuk ke bulan sesuai `closed_at` (WIB).
- Minggu ISO yang terpotong di awal/akhir bulan hanya dihitung bagian hari di bulan itu.
- Sinyal yang ditandai fraud (`signals.excluded_from_payroll = true`) tidak dihitung.
- `Support\PayrollCalculator::calculate(PayrollInput): PayrollResult` adalah fungsi murni dan sumber kebenaran rumus.

## Proses

| Langkah | Waktu | Aktor |
|---|---|---|
| 1. `GeneratePayrollDraft` untuk bulan sebelumnya | Tanggal 1, 01:00 WIB | Scheduler |
| 2. Review item, bisa men-*hold* trader tertentu (wajib isi catatan) | Tanggal 1–5 | Admin |
| 3. `ApprovePayroll` → setiap item `amount > 0` dikreditkan ke wallet trader (`payroll_credit`, langsung **available**) | | Admin |
| 4. Notifikasi ke trader + slip rincian | | Sistem |

Tabel:
- `payroll_periods` (`month` unik `YYYY-MM`, `status` draft/approved, `approved_by`, `approved_at`, `total_amount`)
- `payroll_items` (`period_id`, `trader_id`, `rank_id`, `eligible`, `ineligible_reasons`, `base_amount`, `wins_counted`, `wins_amount`, `followers_counted`, `followers_amount`, `streak_amount`, `total_amount`, `status` pending/held/paid)

Aturan:
- Satu period hanya bisa di-approve sekali (idempotent). Kredit wallet memakai `reference = payroll_item:{id}` yang unik.
- Draft bisa di-*regenerate* selama belum di-approve.
- Pajak (PPh) akan dihitung di fase berikutnya; kolom `tax_amount` disiapkan (default 0).

## Contoh

Trader rank **Elite**: base 2.000.000, per win 25.000, max 10 win/minggu, streak 10, bonus streak 500.000.
Bulan ini: 4 minggu dengan win 12, 8, 10, 5 (dibayar 10 + 8 + 10 + 5 = 33). Follower valid 620 (bracket 500–999 = 750.000). Best streak 11.

`2.000.000 + 33 × 25.000 + 750.000 + 500.000 = 4.075.000`
