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

`payments` (dipakai bersama marketplace): `user_id`, `payable_type/id`, `amount`, `gateway`, `gateway_reference` **unik**, `checkout_url`, `status` (`pending`, `paid`, `failed`, `expired`, `refunded`), `paid_at`, `expires_at`, `raw_payload` json.

### Payment gateway

- Interface `App\Services\Payments\PaymentGateway`: `name()`, `createCheckout(Payment): Checkout` (reference + URL), `parseWebhook(Request): PaymentEvent` (melempar `InvalidWebhookSignature`).
- Driver dari env `PAYMENT_DRIVER`: `simulated` (dev; halaman `/payments/{id}/simulate` untuk memilih berhasil/gagal, **ditolak di production**), `FakePaymentGateway` (test, helper `fakePaymentGateway()`), `midtrans` (production, Snap).
- Driver `midtrans` (`App\Services\Payments\MidtransPaymentGateway`): env `MIDTRANS_SERVER_KEY`, `MIDTRANS_PRODUCTION` (false = sandbox). `order_id` = `SKT-{payment_id}-XXXXXX` (disimpan sebagai `gateway_reference`), masa bayar = `services.payments.checkout_expiry_hours`. Webhook diverifikasi dengan `signature_key = sha512(order_id + status_code + gross_amount + server_key)`. Pemetaan status: `settlement` → paid; `capture` → paid jika `fraud_status` accept/kosong, selain itu tetap pending; `expire` → expired; `deny`/`cancel`/`failure` → failed; lainnya pending. URL notifikasi di dashboard Midtrans: `https://<domain>/webhooks/payments/midtrans`.
- Jika gateway gagal membuat checkout, `StartCheckout` melaporkan error, transaksi dibatalkan (payment dan subscription/order tidak tersimpan), dan pengguna melihat pesan "Pembayaran sedang tidak bisa diproses" pada field `checkout`.
- Webhook: `POST /webhooks/payments/{gateway}` (tanpa CSRF). `App\Actions\Payments\HandlePaymentEvent` mengunci baris payment, hanya memproses payment `pending` (idempotent), lalu memanggil handler sesuai jenis payable (`HandlePaymentEvent::HANDLERS`).
- Checkout `pending` yang lewat `expires_at` (24 jam) menjadi `expired` oleh `subscriptions:lifecycle`.

## Alur

1. Member klik **Langganan** di profil trader → pilih durasi → wajib centang disclaimer risiko.
2. `CreateSubscriptionCheckout` → `subscriptions` (`pending_payment`) + `payments` (`pending`) + persetujuan disclaimer di `user_consents` → redirect ke halaman bayar gateway.
3. Webhook `paid` (idempotent per `gateway_reference`):
    - `payments.status = paid`
    - `subscriptions.status = active`. `starts_at = now`, atau `ends_at` langganan aktif sebelumnya jika ini perpanjangan. `ends_at = starts_at + months`.
    - `PostTransaction(subscription_income, pending, trader_income, available_at = now + hold_period_days)`
4. Notifikasi ke member dan trader.

## Akses

- `Subscription::isActive(member, trader)` = ada subscription `active` dengan `starts_at ≤ now < ends_at`.
- Lihat tabel visibilitas di `signal-lifecycle.md`.
- Command harian `subscriptions:lifecycle` (06:00): `active` yang lewat `ends_at` → `expired`; pengingat sesuai `subscription.reminder_days` (default H-3, H-1), dilewati jika sudah diperpanjang; peringatan ke admin untuk channel yang punya subscriber aktif ≥ `subscription.inactive_alert_days` hari tanpa sinyal premium baru.
- Kartu sinyal premium yang terkunci menampilkan tombol **Langganan** jika channel trader terbuka; profil trader menampilkan tombol langganan/perpanjang.

## Refund

- Hanya oleh admin, dalam hold period (saldo trader masih pending).
- `refund_reversal` sebesar `trader_income` dengan reference `reverse:{id transaksi pendapatan}` (sehingga `wallets:release` tidak merilisnya). Subscription menjadi `refunded`, `ends_at = now`, payment `refunded`, akses dicabut. Pengembalian uang ke pembeli dilakukan di dashboard gateway.
- Di luar hold period: tidak ada refund otomatis (kasus khusus → `admin_adjustment`).

## Pembekuan

Jika rank trader turun di bawah syarat premium: `channel_plans.is_open = false` otomatis (di `RecalculateRanks`). Subscriber aktif tetap dilayani sampai `ends_at`, dan trader **tetap bisa memposting sinyal premium** selama masih punya subscriber aktif. Peringatan inaktivitas: lihat lifecycle di atas.
