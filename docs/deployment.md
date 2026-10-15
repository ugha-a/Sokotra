# Deployment & Operasional

Checklist untuk menjalankan Sokotra di server production. Semua angka bisnis tetap diatur di Master Data; dokumen ini hanya mengatur infrastruktur.

## Kebutuhan Server

- PHP 8.4 (ekstensi: `bcmath`, `intl`, `mbstring`, `openssl`, `pdo_*`, `fileinfo`), Composer, Node 22+ untuk build aset.
- Database: MySQL 8 / PostgreSQL 15 (SQLite hanya untuk dev). Redis disarankan untuk cache, session, dan queue.
- HTTPS wajib (cookie session, webhook Midtrans).

## Environment

| Variabel                                      | Nilai production                | Catatan                                                                                          |
| --------------------------------------------- | ------------------------------- | ------------------------------------------------------------------------------------------------ |
| `APP_ENV` / `APP_DEBUG`                       | `production` / `false`          |                                                                                                  |
| `APP_KEY`                                     | dari `php artisan key:generate` | **Jangan dirotasi** tanpa migrasi data, lihat bagian APP_KEY                                     |
| `APP_URL`                                     | `https://<domain>`              | Dipakai link email, signed URL, dan callback Midtrans                                            |
| `APP_LOCALE`                                  | `id`                            |                                                                                                  |
| `QUEUE_CONNECTION`                            | `redis` atau `database`         | Jangan `sync`: notifikasi sinyal dikirim ke banyak follower                                      |
| `CACHE_STORE` / `SESSION_DRIVER`              | `redis`                         | Cache dipakai `Settings::get`                                                                    |
| `MAIL_*`                                      | SMTP transaksional              | Verifikasi email, reset password, notifikasi payroll/withdraw                                    |
| `PRICE_FEED_DRIVER`                           | `twelvedata`                    | `simulated` **ditolak** saat `APP_ENV=production` (aplikasi melempar exception saat boot driver) |
| `TWELVE_DATA_API_KEY`                         | key paket berbayar              | Pastikan kuota kredit cukup: ±1 request/menit per instrumen yang punya sinyal terbuka            |
| `PAYMENT_DRIVER`                              | `bank_transfer` atau `midtrans` | `bank_transfer` default sementara; `simulated` **ditolak** di production                         |
| `MIDTRANS_SERVER_KEY` / `MIDTRANS_PRODUCTION` | server key / `true`             | Uji dulu dengan key sandbox dan `MIDTRANS_PRODUCTION=false`                                      |

## Langkah Deploy

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force          # juga mengisi role & master default (idempotent)
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
```

Jangan jalankan `db:seed` di production: `DatabaseSeeder` membuat akun demo dengan password `password`. Buat akun admin pertama lewat `php artisan tinker` lalu beri role `admin`.

## Scheduler

Satu cron untuk semua job (jadwal di `routes/console.php`, zona waktu `app.business_timezone` = Asia/Jakarta):

```
* * * * * cd /path/to/sokotra && php artisan schedule:run >> /dev/null 2>&1
```

| Command                   | Jadwal (WIB)     | Fungsi                                                 |
| ------------------------- | ---------------- | ------------------------------------------------------ |
| `signals:evaluate`        | setiap menit     | Evaluasi sinyal terbuka dari price feed                |
| `wallets:release`         | setiap jam       | Pendapatan pending → available setelah masa tahan      |
| `stats:refresh`           | harian 07:15     | Hitung ulang statistik trader                          |
| `wallets:reconcile`       | harian 07:30     | Cek saldo wallet = jumlah ledger                       |
| `fraud:scan`              | harian 07:45     | Pindai follower palsu                                  |
| `subscriptions:lifecycle` | harian 13:00     | Pengingat, expire langganan, alert channel tidak aktif |
| `ranks:recalculate`       | Senin 00:05      | Rank mingguan                                          |
| `payroll:generate`        | tanggal 1, 01:00 | Draft payroll bulan sebelumnya                         |

Cek jadwal aktual dengan `php artisan schedule:list` (ditampilkan dalam zona waktu server). Jika `signals:evaluate` berhenti, sinyal tidak akan tertutup; pasang monitoring (mis. heartbeat) pada cron ini.

## Queue Worker

Notifikasi (`SignalPublishedNotification`, `SignalClosedNotification`, dll.) memakai `ShouldQueue`. Jalankan worker lewat Supervisor/systemd:

```
php artisan queue:work --tries=3 --backoff=10 --max-time=3600
```

Restart worker setiap deploy (`php artisan queue:restart`).

## Storage

- Disk `local` (private, `storage/app/private`): dokumen KYC, file produk marketplace, gambar chart sinyal. Hanya diakses lewat controller dengan otorisasi atau signed URL. **Jangan** dipindah ke disk public.
- Disk `public` (+ `storage:link`): aset yang boleh publik.
- Backup `storage/app/private` bersama database. Data KYC tunduk pada UU PDP (lihat `logic/compliance.md`).

## Webhook Pembayaran

- URL notifikasi di dashboard Midtrans: `https://<domain>/webhooks/payments/midtrans` (tanpa CSRF, diverifikasi dengan signature sha512).
- Finish redirect diisi otomatis per transaksi ke `/payments/{id}`.
- Webhook idempotent: aman jika Midtrans mengirim ulang.

## APP_KEY

`APP_KEY` dipakai untuk enkripsi kolom (`nik`, nomor rekening) **dan** `nik_hash` (HMAC NIK untuk mencegah satu NIK dipakai banyak akun). Mengganti key tanpa re-enkripsi membuat data KYC dan rekening tidak terbaca serta deteksi NIK duplikat tidak cocok lagi. Jika rotasi diperlukan, pakai `APP_PREVIOUS_KEYS` dan tulis migrasi yang mengenkripsi ulang kolom serta menghitung ulang `nik_hash`.

## Sebelum Launch

- Isi nominal di Master Data → Aturan Gaji (default Rp0, payroll tidak membayar apa pun sampai diisi).
- Ganti broker contoh dengan broker berizin Bappebti yang sudah diverifikasi.
- Finalisasi S&K, Kebijakan Privasi, dan Disclaimer Risiko (Master Data → Halaman Konten) bersama konsultan hukum.
- Tentukan rumus PPh bersama konsultan pajak (`payroll_items.tax_amount` masih 0).
- Uji end-to-end di sandbox Midtrans: subscribe, beli produk, refund.
