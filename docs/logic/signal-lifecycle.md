# Sinyal: Validasi, State Machine & Engine Evaluasi

Sinyal adalah inti kejujuran platform. Ranking, gaji, dan harga subscription semuanya dihitung dari sinyal, jadi **tidak boleh ada celah manipulasi**.

## Tabel `signals`

| Kolom                                             | Tipe                   | Keterangan                                   |
| ------------------------------------------------- | ---------------------- | -------------------------------------------- |
| trader_id                                         | fk users               |                                              |
| instrument_id                                     | fk                     |                                              |
| pip_size_snapshot, spread_pips_snapshot           | decimal                | Disalin dari master saat publish             |
| direction                                         | enum                   | `buy`, `sell`                                |
| order_type                                        | enum                   | `market`, `limit`, `stop`                    |
| entry_price, stop_loss, take_profit               | decimal(18,5)          | Terkunci setelah publish                     |
| rr                                                | decimal(8,2)           | Dihitung saat publish                        |
| visibility                                        | enum                   | `free`, `premium`                            |
| analysis                                          | text                   | Alasan analisa (wajib, min 30 karakter)      |
| chart_image_path                                  | string nullable        |                                              |
| status                                            | enum                   | Lihat state machine                          |
| published_at, triggered_at, closed_at, expires_at | timestamp              |                                              |
| close_price                                       | decimal nullable       |                                              |
| result_pips                                       | decimal(10,1) nullable |                                              |
| result_r                                          | decimal(8,2) nullable  | `+rr`, `-1`, atau proporsional untuk timeout |
| publish_ip, publish_device_hash                   | string                 | Untuk deteksi fraud                          |

Tabel `signal_events` (append-only): `signal_id`, `type`, `price`, `payload` json, `occurred_at`.

## Validasi Publish (`App\Actions\Signal\PublishSignal`)

1. User punya role `trader` dan trader-profile `approved`.
2. Instrumen aktif.
3. Arah konsisten:
    - `buy`: `stop_loss < entry < take_profit`
    - `sell`: `take_profit < entry < stop_loss`
4. `sl_pips = |entry - stop_loss| / pip_size ≥ instrument.min_sl_pips`.
5. `rr = |take_profit - entry| / |entry - stop_loss| ≥ signal.min_rr`.
6. Jumlah sinyal `pending` + `active` milik trader `< signal.max_active_per_trader`.
7. `visibility = premium` hanya jika rank trader `can_sell_premium`.
8. **Order market**: `entry_price` diganti dengan harga feed saat publish (ask untuk buy, bid untuk sell). SL/TP divalidasi ulang terhadap entry baru. Jika validasi gagal setelah harga diganti, publish ditolak dan trader diminta menyesuaikan.
9. Order limit/stop: entry harus di sisi yang benar terhadap harga saat ini:
    - buy limit < harga < buy stop
    - sell limit > harga > sell stop

## State Machine

```
              publish (market)
   [draft] ───────────────────────────► [active]
      │ publish (limit/stop)                ▲   │
      ▼                                     │   ├── harga sentuh TP ──► [tp_hit]
  [pending] ── harga sentuh entry ──────────┘   ├── harga sentuh SL ──► [sl_hit]
      │                                         └── lewat max_duration ► [closed_timeout]
      ├── lewat pending_expiry_hours ──► [expired]
      └── dibatalkan trader ───────────► [cancelled]
```

- `draft` hanya ada di form, tidak disimpan (opsional nanti).
- Setelah `published_at`, kolom `entry_price`, `stop_loss`, `take_profit`, `direction`, `instrument_id` **immutable**. Model melempar exception jika diubah. Tidak ada soft delete untuk trader.
- `cancelled` hanya dari `pending` dan dibatasi `signal.max_cancel_per_week`.
- Status final: `tp_hit`, `sl_hit`, `closed_timeout`, `expired`, `cancelled`.

## Engine Evaluasi (`App\Actions\Signal\EvaluateOpenSignals`)

Dijalankan oleh scheduler **setiap menit**.

1. Ambil instrumen yang punya sinyal `pending` atau `active`.
2. Untuk setiap instrumen, ambil candle 1m sejak evaluasi terakhir dari `PriceFeed::candles($symbol, $from, $to)`.
3. Proses candle **berurutan**. Untuk setiap sinyal:
    - **Pending**: jika candle menyentuh entry → `active` (`triggered_at` = waktu candle). Lanjutkan cek TP/SL pada candle yang sama. Buy masuk di ask (`low + spread ≤ entry` untuk limit, `high + spread ≥ entry` untuk stop). Sell masuk di bid (`high ≥ entry` untuk limit, `low ≤ entry` untuk stop).
    - **Active**: lihat tabel di bawah. Harga sisi **bid** dipakai dari feed. Untuk buy, exit di bid. Untuk sell, exit di ask = bid + spread.

| Arah | TP tersentuh jika            | SL tersentuh jika           |
| ---- | ---------------------------- | --------------------------- |
| buy  | `high ≥ take_profit`         | `low ≤ stop_loss`           |
| sell | `low + spread ≤ take_profit` | `high + spread ≥ stop_loss` |

- Jika **TP dan SL tersentuh di candle yang sama** → diputuskan oleh `signal.ambiguous_candle_rule` (default `sl_first` = kalah).

4. Setelah semua candle: pending lewat `expires_at` → `expired`; active lewat `max_duration_days` → `closed_timeout` di harga close terakhir.
5. Setiap transisi menulis `signal_events` dan memicu event `SignalClosed` / `SignalTriggered` (untuk statistik & notifikasi).

### Hasil

| Status              | result_pips                       | result_r                |
| ------------------- | --------------------------------- | ----------------------- |
| tp_hit              | jarak entry→TP dalam pips         | `+rr`                   |
| sl_hit              | −jarak entry→SL                   | `-1.00`                 |
| closed_timeout      | (close − entry) × arah / pip_size | `result_pips / sl_pips` |
| expired / cancelled | null                              | null (tidak dihitung)   |

## Price Feed

- Interface `App\Services\PriceFeed\PriceFeed`:
    - `quote(Instrument $instrument): Quote` (bid, ask, time)
    - `candles(Instrument $instrument, CarbonInterface $from, CarbonInterface $to): list<Candle>`: candle 1m harga **bid** yang sudah selesai, waktu mulai di `[from, to)`, urut dari yang terlama
    - Kegagalan apa pun dilempar sebagai `PriceFeedUnavailable`.
- Driver dipilih dari env `PRICE_FEED_DRIVER` (`config/services.php` → `price_feed.driver`):

| Driver          | Untuk      | Catatan                                                                                                       |
| --------------- | ---------- | ------------------------------------------------------------------------------------------------------------- |
| `simulated`     | Dev lokal  | Harga sintetis deterministik dari `price_feed.simulated.base_prices`. **Ditolak di production.**              |
| `FakePriceFeed` | Test       | Quote & candle disuntikkan (`setQuote`, `addCandles`, `failFor`, `recover`), lewat helper `fakePriceFeed()`   |
| `twelvedata`    | Production | Twelve Data REST (`/price`, `/time_series` 1min) memakai `instruments.feed_symbol`; env `TWELVE_DATA_API_KEY` |

- Driver `twelvedata` (`App\Services\PriceFeed\TwelveDataPriceFeed`): harga dari Twelve Data diperlakukan sebagai **bid**; ask = bid + `spread_default` instrumen. Respons error "no data" pada `time_series` (akhir pekan/libur) berarti pasar tutup → daftar candle kosong, bukan kegagalan. Error lain, HTTP gagal, atau koneksi putus → `PriceFeedUnavailable`. Cek batas kredit paket Twelve Data sebelum live (satu request per instrumen terbuka per menit).
- Jika feed gagal atau ada gap data, evaluasi **tidak menebak**. Cursor (`instrument_feed_cursors.last_candle_at`) tidak maju, dan run berikutnya mengulang dari candle terakhir yang sukses.

## Implementasi Engine

- Command `signals:evaluate` dijadwalkan **setiap menit** (`routes/console.php`, `withoutOverlapping`). Lokal: `php artisan schedule:work`.
- Semua perbandingan harga memakai `App\Support\Signal\PriceMath` dalam satuan **poin** bilangan bulat (`harga × 10^digits`), memakai snapshot `digits`/`pip_size`/`spread` dari sinyal, bukan master saat ini.
- Hanya candle yang **mulai pada atau setelah** `published_at` yang dihitung. Sinyal yang dipublish 10:00:30 mulai dinilai dari candle 10:01.
- Rentang candle per run: dari `max(cursor + 1 menit, menit publish sinyal terbuka tertua)` sampai menit berjalan (hanya candle yang sudah selesai).
- `closed_timeout` hanya ditutup jika ada harga close yang diketahui dari candle sebelum batas waktu. Kalau tidak ada, engine mencoba lagi di run berikutnya.
- Transisi memicu event `App\Events\SignalTriggered` / `App\Events\SignalClosed` (dipakai modul statistik & notifikasi).

## Visibilitas

| Viewer                 | Sinyal free | Sinyal premium (terbuka)                | Sinyal premium (closed) |
| ---------------------- | ----------- | --------------------------------------- | ----------------------- |
| Publik / member        | Lengkap     | Hanya instrumen + arah, detail terkunci | Lengkap                 |
| Subscriber aktif       | Lengkap     | Lengkap, real-time                      | Lengkap                 |
| Trader pemilik / admin | Lengkap     | Lengkap                                 | Lengkap                 |

## Notifikasi

Saat publish: follower (sinyal free) dan subscriber (premium) menerima notifikasi database + broadcast Reverb. Saat closed: notifikasi hasil ke orang yang sama. **Diimplementasikan di modul Follow** (listener `SignalTriggered`/`SignalClosed`), karena relasi follower baru ada di sana.

Aturan visibilitas diterapkan di `App\Policies\SignalPolicy::viewDetails` dan `App\Http\Resources\SignalResource` (field detail dikirim `null` saat terkunci). Gambar chart disimpan di disk privat dan hanya dilayani lewat `GET /signals/{signal}/chart` dengan policy yang sama.
