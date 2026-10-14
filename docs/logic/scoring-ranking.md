# Statistik, Skor, Rank, Leaderboard & Follow

## Sinyal yang Dihitung

Hanya sinyal berstatus final **`tp_hit`, `sl_hit`, `closed_timeout`**. Status `expired` dan `cancelled` tidak dihitung.

- **Menang** = `tp_hit`, atau `closed_timeout` dengan `result_r > 0`.
- **Kalah** = `sl_hit`, atau `closed_timeout` dengan `result_r ≤ 0`.

## Statistik per Trader — `trader_stats`

Disimpan per `period` (`week`, `month`, `rolling_90d`, `all_time`) + `period_start`. Diperbarui saat event `SignalClosed` (inkremental) dan direkonsiliasi oleh job harian.

| Metrik                        | Rumus                                                     |
| ----------------------------- | --------------------------------------------------------- |
| closed_signals                | jumlah sinyal closed                                      |
| wins / losses                 |                                                           |
| win_rate                      | `wins / closed_signals × 100`                             |
| total_r                       | `Σ result_r`                                              |
| avg_rr                        | rata-rata `rr` dari sinyal closed                         |
| total_pips                    | `Σ result_pips` (informasi saja, tidak untuk ranking)     |
| max_consecutive_wins / losses | urut `closed_at`                                          |
| current_streak                | kemenangan berurutan terakhir (0 jika terakhir kalah)     |
| max_drawdown_r                | penurunan terbesar dari puncak kurva kumulatif `result_r` |
| profit_factor                 | `Σ R menang / abs(Σ R kalah)`                             |

`Support\TraderStatsCalculator::fromSignals(Collection $signals): TraderStats` adalah fungsi murni dan sumber kebenaran tunggal rumus di atas.

### Streak

- Kemenangan berurutan menurut `closed_at`, satu trader, semua instrumen.
- Kekalahan me-reset streak ke 0.
- `expired`/`cancelled` diabaikan (tidak me-reset), kecuali `signal.count_expired_in_streak = true`.
- `best_streak_in_month` dipakai payroll (lihat `payroll-rewards.md`).

## Rank (dihitung ulang mingguan, Senin 00:05 WIB)

Job `RecalculateRanks`:

1. Ambil statistik `rolling_90d` dan jumlah **follower valid** setiap trader.
2. Rank = level **tertinggi** yang **semua** threshold-nya terpenuhi:
    - `closed_signals ≥ min_closed_signals`
    - `win_rate ≥ min_win_rate`
    - `total_r ≥ min_total_r`
    - `valid_followers ≥ min_valid_followers`
    - `max_drawdown_r ≤ max_drawdown_r` (jika diisi)
3. Perubahan rank dicatat di `trader_rank_histories` dan dinotifikasi ke trader.
4. Jika rank turun di bawah `can_sell_premium`: channel premium **dibekukan untuk penjualan baru**. Subscriber yang ada tetap dilayani sampai masa langganan habis.

## Skor Leaderboard

Setiap komponen dinormalisasi 0–1 terhadap seluruh trader aktif (min-max) di window `score.window_days`:

```
score = w_win_rate    × norm(win_rate)
      + w_total_r     × norm(total_r)
      + w_consistency × norm(consistency)
      + w_followers   × norm(log(1 + valid_followers))
consistency = 1 / (1 + max_drawdown_r)
```

- Trader dengan `closed_signals < 20` tidak masuk leaderboard (menghindari win rate 100% dari 2 sinyal).
- Leaderboard publik: **Mingguan, Bulanan, All-time**, plus filter instrumen.
- **"Most Consistent"**: urut `consistency` lalu `win_rate`, minimal 50 sinyal.
- **"Featured"**: dikurasi admin (flag `is_featured`) dan tetap wajib memenuhi minimal sinyal.

## Follow

- Tabel `follows` (`follower_id`, `trader_id`, unik). Gratis, tanpa batas.
- Trader tidak bisa follow dirinya sendiri.
- **Follower valid** = `email_verified_at` tidak null **dan** `last_active_at` dalam `score.follower_active_days` hari **dan** bukan akun yang ditandai fraud. Hanya follower valid yang dihitung untuk rank dan gaji. Jumlah total follower tetap ditampilkan.
- `users.last_active_at` diperbarui oleh middleware (maksimal sekali per jam).

## Anti-Fraud Ranking

| Pola               | Deteksi                                                                                   | Tindakan                                                                 |
| ------------------ | ----------------------------------------------------------------------------------------- | ------------------------------------------------------------------------ |
| Hedging antar akun | Sinyal berlawanan, instrumen sama, publish dalam ±10 menit, dari IP/device hash yang sama | Tandai kedua trader untuk review admin; sinyal dikecualikan dari payroll |
| Multi-akun trader  | KYC (NIK) sama                                                                            | Ditolak saat approval                                                    |
| Followers palsu    | Banyak akun baru dari IP sama mem-follow trader yang sama                                 | Follower ditandai tidak valid                                            |
| Spam TP kecil      | Dicegah oleh `min_rr` dan `min_sl_pips`                                                   | —                                                                        |
| Cancel berlebihan  | `max_cancel_per_week`                                                                     | —                                                                        |
