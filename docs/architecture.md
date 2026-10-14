# Arsitektur & Konvensi Kode

## Stack

| Layer             | Teknologi                                                                                                  |
| ----------------- | ---------------------------------------------------------------------------------------------------------- |
| Backend           | Laravel 13, PHP 8.4                                                                                        |
| Frontend          | Inertia 3 + React 19 (TypeScript) + Tailwind v4 + shadcn/ui (Radix)                                        |
| Routing FE        | Laravel Wayfinder (route & action typed, `resources/js/routes`, `resources/js/actions`)                    |
| Auth              | Laravel Fortify (sudah termasuk 2FA & passkey dari starter kit)                                            |
| Role              | `spatie/laravel-permission` (role dari enum `App\Enums\Role`, dibuat oleh migration `seed_platform_roles`) |
| Test              | Pest 5                                                                                                     |
| Queue / Scheduler | Database queue (dev) → Redis + Horizon (production)                                                        |
| Realtime          | Laravel Reverb (dipasang saat modul Sinyal)                                                                |
| Storage           | `local` (dev) / S3 private (production) untuk KYC dan file produk                                          |

## Struktur Folder Backend

```
app/
  Actions/<Domain>/      # Satu class = satu use-case. Method publik: handle(...)
  Enums/                 # Status & tipe (backed enum string)
  Http/Controllers/<Area>/  # Area: Public, Member, Trader, Admin. Tipis: validasi → Action → Inertia
  Http/Requests/<Area>/  # FormRequest per aksi
  Models/
  Services/<Integrasi>/  # PriceFeed, PaymentGateway, Disbursement (interface + driver)
  MasterData/            # MasterResource per master (Resources/), Field (schema form), MasterRegistry
  Support/               # Helper murni (Settings, Money, Pips, RMultiple)
```

Domain: `Signal`, `Ranking`, `Payroll`, `Wallet`, `Subscription`, `Marketplace`, `Broker`, `Kyc`, `MasterData`.

### Aturan

- **Controller tidak berisi logic bisnis.** Logic ada di Action agar bisa dites tanpa HTTP.
- **Semua angka bisnis dibaca dari master data** (`logic/master-data.md`), tidak di-hardcode.
- **Uang**: integer rupiah (`bigInteger`/`unsignedBigInteger`), tanpa desimal. Nama kolom berakhiran `_amount` atau `price`.
- **Harga instrumen**: `decimal(18,5)`. Di PHP diperlakukan sebagai string/`float` hanya di Support helper.
- **Persentase**: `decimal(5,2)` (contoh `20.00` = 20%).
- **Status**: PHP backed enum di `app/Enums`, kolom `string`.
- **Data finansial & audit bersifat append-only**: `wallet_transactions`, `signal_events`. Tidak ada update/delete.
- **Operasi uang** dibungkus `DB::transaction()` dengan `lockForUpdate()` pada wallet.
- **Webhook pembayaran idempotent** (unik per `gateway_reference`).

## Struktur Folder Frontend

```
resources/js/
  pages/
    public/    # landing, leaderboard, profil trader, marketplace, broker
    member/    # feed, langganan, pembelian, wallet
    trader/    # posting sinyal, statistik, channel, produk, payroll
    admin/     # master data, approval, laporan
  components/
    ui/        # shadcn primitives — jangan diubah untuk kebutuhan domain
    domain/    # SignalCard, TraderCard, RankBadge, dst (lihat ui-ux/components.md)
  layouts/     # app (sidebar), auth, settings, public
  lib/         # format.ts (Rupiah, pips, persen, tanggal), utils.ts
```

Navigasi sidebar disusun per role (lihat `ui-ux/sitemap-flows.md`).

### Master Data Generik

Semua master di admin memakai satu jalur: `MasterRegistry` → `MasterResource` → `Admin\MasterDataController` → halaman `admin/master/index`.

- Menambah master baru = membuat class baru di `app/MasterData/Resources/` dan mendaftarkannya di `MasterRegistry::RESOURCES`. Tidak perlu controller atau halaman baru.
- `MasterResource` mendeskripsikan `fields()` (schema form via `Field::*`), `columns()`, `rules()`, serta hook `prepareForValidation()`, `after()` (validasi lintas field), `beforeSave()`, dan `appends()` (accessor model untuk tabel).
- Validasi master dijalankan di `App\Actions\MasterData\SaveMasterRecord`, bukan FormRequest, karena aturannya berasal dari resource. Semua create/update/toggle dicatat ke `admin_audit_logs` lewat `RecordAdminAudit`.

### Lokalisasi

Locale default `id`. Pesan validasi ada di `lang/id/validation.php`; label field diambil dari schema (`attributeLabels()`). Rule yang belum diterjemahkan jatuh ke bahasa Inggris.

## Testing (TDD wajib)

Mengikuti CLAUDE.md root: setiap modul **test ditulis dulu (Red)**, lalu kode minimum (Green), lalu refactor.

| Jenis    | Lokasi                      | Isi                                                           |
| -------- | --------------------------- | ------------------------------------------------------------- |
| Unit     | `tests/Unit/<Domain>/`      | Rumus murni: R-multiple, pips, streak, rank, gaji, ledger     |
| Frontend | `resources/js/**/*.test.ts` | Fungsi murni FE (format, navigasi) dengan Vitest bawaan Vite+ |
| Feature  | `tests/Feature/<Domain>/`   | Action + HTTP + otorisasi role + Inertia props                |

- Database test: SQLite in-memory (`phpunit.xml`).
- Integrasi eksternal **selalu di-fake**: `FakePriceFeed`, `FakePaymentGateway`.
- Jalankan: `php artisan test`, `npx vp test run`, `npm run types:check`, `npm run check`.
- Test PHP berjalan tanpa Vite (`withoutVite()` di `tests/TestCase.php`), jadi tidak perlu build sebelum test.

## Git

- Branch `main` stabil. Setiap modul di branch `feature/<modul>`.
- Commit message: `<type>(<domain>): <ringkasan>` — contoh `feat(signal): evaluasi otomatis TP/SL dari candle 1m`.
