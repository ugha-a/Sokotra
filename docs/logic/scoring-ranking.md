# Statistik, Skor, Rank, Leaderboard & Follow

## Sinyal yang Dihitung

Hanya sinyal berstatus final **`tp_hit`, `sl_hit`, `closed_timeout`**. Status `expired` dan `cancelled` tidak dihitung.

- **Menang** = `tp_hit`, atau `closed_timeout` dengan `result_r > 0`.
- **Kalah** = `sl_hit`, atau `closed_timeout` dengan `result_r ≤ 0`.

## Statistik per Trader — `trader_stats`

Disimpan per `period` (`week`, `month`, `rolling` = `score.window_days` hari terakhir, `all_time`) + `period_start`. Dihitung ulang penuh oleh `App\Actions\Ranking\RefreshTraderStats` setiap kali sinyal trader ditutup (listener `RefreshStatsWhenSignalCloses`) dan untuk semua trader oleh command `stats:refresh` setiap hari 00:15. Batas minggu (Senin) dan bulan memakai kalender WIB (`config('app.business_timezone')`), timestamp tetap UTC.

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

Command `ranks:recalculate` (Senin 00:05 WIB; menjalankan `stats:refresh` dulu) → `App\Actions\Ranking\RecalculateRanks`:

1. Ambil statistik `rolling` dan jumlah **follower valid** setiap trader `approved` (trader `suspended` tidak diubah). Hanya rank `is_active` yang dipertimbangkan.
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

- Trader dengan `closed_signals < score.leaderboard_min_signals` (default 20) tidak masuk leaderboard (menghindari win rate 100% dari 2 sinyal). Trader `suspended` atau `is_flagged` tidak ditampilkan.
- Leaderboard publik `/leaderboard`: **Mingguan, Bulanan, All-time**. Normalisasi min-max dihitung di antara trader yang lolos syarat pada periode itu (`App\Actions\Ranking\BuildLeaderboard`). Filter instrumen belum ada (butuh statistik per instrumen).
- **"Most Consistent"**: urut `consistency` lalu `win_rate`, minimal `score.consistent_min_signals` (default 50) sinyal.
- **"Featured"**: dikurasi admin (flag `is_featured`) dan tetap wajib memenuhi minimal sinyal.

## Follow

- Tabel `follows` (`follower_id`, `trader_id`, unik). Gratis, tanpa batas.
- Trader tidak bisa follow dirinya sendiri.
- **Follower valid** = `email_verified_at` tidak null **dan** `last_active_at` dalam `score.follower_active_days` hari **dan** bukan akun yang ditandai fraud. Hanya follower valid yang dihitung untuk rank dan gaji. Jumlah total follower tetap ditampilkan.
- `users.last_active_at` diperbarui oleh middleware `TrackLastActivity` (maksimal sekali per jam). Flag akun fraud: `users.is_fraud_flagged`.
- Profil publik `/traders/{slug}` (hanya trader `approved`): statistik per periode, kurva R kumulatif, riwayat sinyal dengan aturan visibilitas, tombol Ikuti.

## Notifikasi

- Database notification: `SignalPublishedNotification` dan `SignalClosedNotification` ke semua follower (listener `NotifyFollowersOfSignal`, kecuali pembatalan), `RankChanged` (email + database) ke trader.
- Notifikasi diantrikan (`ShouldQueue`). Ikon lonceng di header memperbarui jumlah belum dibaca dengan polling 60 detik (shared prop `notifications.unread`). Broadcast realtime (Reverb) bisa ditambahkan nanti tanpa mengubah notifikasi.

## Anti-Fraud Ranking

| Pola               | Deteksi                                                                                   | Tindakan                                                                 |
| ------------------ | ----------------------------------------------------------------------------------------- | ------------------------------------------------------------------------ |
| Hedging antar akun | Sinyal berlawanan, instrumen sama, publish dalam ±10 menit, dari IP/device hash yang sama | Tandai kedua trader untuk review admin; sinyal dikecualikan dari payroll |
| Multi-akun trader  | KYC (NIK) sama                                                                            | Ditolak saat pengajuan (validasi `nik_hash`)                             |
| Followers palsu    | Banyak akun baru dari IP sama mem-follow trader yang sama                                 | Follower ditandai tidak valid                                            |
| Spam TP kecil      | Dicegah oleh `min_rr` dan `min_sl_pips`                                                   | —                                                                        |
| Cancel berlebihan  | `max_cancel_per_week`                                                                     | —                                                                        |
