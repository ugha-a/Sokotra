# Subscription Channel Premium

Penghasilan kedua platform. Member membayar langganan ke trader dengan rank menengah ke atas untuk mendapat **sinyal premium real-time**.

## Siapa Boleh Membuka Premium

- Rank trader `can_sell_premium = true` (seed: Elite, Master, Legend).
- KYC `verified`.
- Trader mengatur `channel_plans`: harga per bulan dalam rentang `subscription_min_price`–`subscription_max_price` rank-nya.
- Durasi yang tersedia mengikuti `subscription.durations` (default 1, 3, 6 bulan). Harga = `harga_bulanan × durasi` (diskon durasi panjang: fase lanjut).
- Perubahan harga **tidak berlaku** untuk subscriber yang sedang aktif.

## Tabel

`channel_plans`: `trader_id` unik, `monthly_price`, `description`, `is_open` (menerima subscriber baru).

`subscriptions`

| Kolom                    | Keterangan                                                      |
| ------------------------ | --------------------------------------------------------------- |
| subscriber_id, trader_id |                                                                 |
| months                   |                                                                 |
| price_paid               | bigint                                                          |
| platform_fee             | `price_paid × fee_percent` (dibulatkan ke bawah ke rupiah)      |
| trader_income            | `price_paid − platform_fee`                                     |
| status                   | `pending_payment`, `active`, `expired`, `cancelled`, `refunded` |
| starts_at, ends_at       |                                                                 |
| payment_id               | fk `payments`                                                   |

`payments` (dipakai bersama marketplace): `user_id`, `payable_type/id`, `amount`, `gateway`, `gateway_reference` **unik**, `status` (`pending`, `paid`, `failed`, `expired`, `refunded`), `paid_at`, `raw_payload` json.

## Alur

1. Member klik **Langganan** di profil trader → pilih durasi → wajib centang disclaimer risiko.
2. `CreateSubscriptionCheckout` → `subscriptions` (`pending_payment`) + `payments` (`pending`) → redirect ke halaman bayar gateway (Midtrans Snap / Xendit Invoice).
3. Webhook `paid` (idempotent per `gateway_reference`):
    - `payments.status = paid`
    - `subscriptions.status = active`. `starts_at = now`, atau `ends_at` langganan aktif sebelumnya jika ini perpanjangan. `ends_at = starts_at + months`.
    - `PostTransaction(subscription_income, pending, trader_income, available_at = now + hold_period_days)`
4. Notifikasi ke member dan trader.

## Akses

- `Subscription::isActive(member, trader)` = ada subscription `active` dengan `starts_at ≤ now < ends_at`.
- Lihat tabel visibilitas di `signal-lifecycle.md`.
- Job harian: `active` yang lewat `ends_at` → `expired`. Pengingat H-3 dan H-1 sebelum expired.

## Refund

- Hanya oleh admin, dalam hold period (saldo trader masih pending).
- `refund_reversal` sebesar `trader_income`. Subscription menjadi `refunded`, akses dicabut.
- Di luar hold period: tidak ada refund otomatis (kasus khusus → `admin_adjustment`).

## Pembekuan

Jika rank trader turun di bawah syarat premium: `channel_plans.is_open = false` otomatis. Subscriber aktif tetap dilayani sampai `ends_at`. Trader tetap wajib memposting sinyal premium. Jika trader tidak memposting sinyal premium selama 14 hari padahal punya subscriber aktif, admin menerima peringatan.
