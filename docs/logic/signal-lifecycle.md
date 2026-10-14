# Sinyal: Validasi, State Machine & Engine Evaluasi

Sinyal adalah inti kejujuran platform. Ranking, gaji, dan harga subscription semuanya dihitung dari sinyal, jadi **tidak boleh ada celah manipulasi**.

## Tabel `signals`

| Kolom | Tipe | Keterangan |
|---|---|---|
| trader_id | fk users | |
| instrument_id | fk | |
| pip_size_snapshot, spread_pips_snapshot | decimal | Disalin dari master saat publish |
| direction | enum | `buy`, `sell` |
| order_type | enum | `market`, `limit`, `stop` |
| entry_price, stop_loss, take_profit | decimal(18,5) | Terkunci setelah publish |
| rr | decimal(8,2) | Dihitung saat publish |
| visibility | enum | `free`, `premium` |
| analysis | text | Alasan analisa (wajib, min 30 karakter) |
| chart_image_path | string nullable | |
| status | enum | Lihat state machine |
| published_at, triggered_at, closed_at, expires_at | timestamp | |
| close_price | decimal nullable | |
| result_pips | decimal(10,1) nullable | |
| result_r | decimal(8,2) nullable | `+rr`, `-1`, atau proporsional untuk timeout |
| publish_ip, publish_device_hash | string | Untuk deteksi fraud |

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

| Arah | TP tersentuh jika | SL tersentuh jika |
|---|---|---|
| buy | `high ≥ take_profit` | `low ≤ stop_loss` |
| sell | `low + spread ≤ take_profit` | `high + spread ≥ stop_loss` |

   - Jika **TP dan SL tersentuh di candle yang sama** → diputuskan oleh `signal.ambiguous_candle_rule` (default `sl_first` = kalah).
4. Setelah semua candle: pending lewat `expires_at` → `expired`; active lewat `max_duration_days` → `closed_timeout` di harga close terakhir.
5. Setiap transisi menulis `signal_events` dan memicu event `SignalClosed` / `SignalTriggered` (untuk statistik & notifikasi).

### Hasil

| Status | result_pips | result_r |
|---|---|---|
| tp_hit | jarak entry→TP dalam pips | `+rr` |
| sl_hit | −jarak entry→SL | `-1.00` |
| closed_timeout | (close − entry) × arah / pip_size | `result_pips / sl_pips` |
| expired / cancelled | null | null (tidak dihitung) |

## Price Feed

- Interface `App\Services\PriceFeed\PriceFeed`:
  - `quote(string $symbol): Quote` (bid, ask, time)
  - `candles(string $symbol, CarbonInterface $from, CarbonInterface $to): Collection<Candle>` (time, open, high, low, close)
- Driver dipilih dari `config/services.php` → `price_feed.driver`. Kandidat: Twelve Data, Finnhub, Polygon, OANDA. Pilih setelah membandingkan harga dan cakupan XAUUSD.
- `FakePriceFeed` untuk test: candle disuntikkan manual.
- Jika feed gagal atau ada gap data, evaluasi **tidak menebak**. Engine mencoba lagi di run berikutnya dari timestamp terakhir yang sukses (`instrument_feed_cursors`).

## Visibilitas

| Viewer | Sinyal free | Sinyal premium (terbuka) | Sinyal premium (closed) |
|---|---|---|---|
| Publik / member | Lengkap | Hanya instrumen + arah, detail terkunci | Lengkap |
| Subscriber aktif | Lengkap | Lengkap, real-time | Lengkap |
| Trader pemilik / admin | Lengkap | Lengkap | Lengkap |

## Notifikasi

Saat publish: follower (sinyal free) dan subscriber (premium) menerima notifikasi database + broadcast Reverb. Saat closed: notifikasi hasil ke orang yang sama.
