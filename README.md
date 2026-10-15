# Sokotra

Platform perantara trader analis ↔ member (sinyal trading, sistem ranking otomatis, gaji trader, subscription channel premium, marketplace indikator & EA, serta integrasi broker partner).

---

## 🚀 Fitur Utama

- **Sinyal Trading Real-Time**: Publikasi sinyal dengan engine evaluasi TP/SL otomatis dan price feed Twelve Data / Mock.
- **Leaderboard & Ranking Trader**: Kalkulasi statistik performa mingguan, win rate, risk-to-reward ratio, dan sistem level trader.
- **Subscription Channel Premium**: Langganan channel VIP trader via transfer bank manual (verifikasi admin) maupun Midtrans Snap.
- **Marketplace Indikator & EA**: Katalog produk digital trading, lisensi otomatis, moderasi produk oleh admin, dan ulasan member.
- **Wallet & Payroll**: Ledger append-only, penarikan saldo, approval admin, dan penggajian otomatis bulanan bagi trader aktif.
- **Broker Partner**: Manajemen akun trading terafiliasi dan impor rebate IB.
- **KYC & Onboarding Trader**: Verifikasi identitas trader sebelum mendapatkan hak publikasi sinyal premium.
- **Admin Hub & Master Data**: Panel manajemen platform setting, audit log, pengguna, dan anti-fraud ringkas.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12, PHP 8.4
- **Frontend**: React 19, Inertia.js v2, TypeScript, Tailwind CSS
- **Database**: SQLite (Development / Testing) / PostgreSQL / MySQL
- **Tooling**: Vite, Wayfinder (`@laravel/vite-plugin-wayfinder`), Pest PHP, Vitest, Laravel Pint

---

## ⚙️ Persyaratan Sistem

- PHP >= 8.2 (Direkomendasikan PHP 8.4 via Laravel Herd)
- Composer >= 2.0
- Node.js >= 20.x & npm
- SQLite / MySQL / PostgreSQL

---

## 📦 Instalasi & Menjalankan Lokal

1. **Clone repositori**:
   ```bash
   git clone https://github.com/ugha-a/Sokotra.git
   cd Sokotra
   ```

2. **Install dependensi**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi & Seeding Database**:
   ```bash
   php artisan migrate --seed
   ```

5. **Build Asset & Jalankan Dev Server**:
   ```bash
   npm run dev
   # dan di terminal terpisah
   php artisan serve
   ```

---

## 🧪 Testing & Code Quality

```bash
# Menjalankan test backend (Pest)
php artisan test

# Menjalankan typecheck TypeScript
npx tsc --noEmit

# Menjalankan linter PHP (Pint)
vendor/bin/pint --test
```

---

## 📄 Lisensi

Hak Cipta © 2026 Sokotra.
