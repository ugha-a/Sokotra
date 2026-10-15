# Panduan UI/UX Interaktif & Tema Trading Platform Sokotra

Dokumen ini mendokumentasikan pembaruan antarmuka (UI) dan pengalaman pengguna (UX) Sokotra agar memberikan atmosfer **terminal trading profesional dan tempat belajar yang nyaman** bagi para trader pemula hingga analis berpengalaman.

---

## 1. Filosofi & Pendekatan Desain

- **Terminal Trading Otentik, Bukan Kasino**:
  Menghadirkan kesan _trading workstation_ modern (seperti TradingView dan Bybit/Binance pro) dengan warna slate obsidian berkelas, angka mono tabular yang tajam, dan kontras tinggi untuk data finansial.
- **Transparansi & Edukasi Nyata**:
  Bukan sekadar memamerkan angka kemenangan, melainkan membiasakan trader dengan manajemen risiko disiplin (kalkulator lot 1-2%, rasio Risk/Reward minimal 1:1, dan alasan analisa teknikal).
- **Interaktivitas Tinggi & Efisiensi Aksi**:
  Fitur 1-klik salin setup trading, ticker harga live yang responsif, visualisasi rasio RR langsung pada kartu sinyal, dan kalkulator lot yang selalu dapat diakses.

---

## 2. Token Warna & Palet Tema Trading Pro

Didefinisikan dalam `resources/css/app.css` dengan format warna modern Oklch:

### Mode Gelap (Pro Slate Workstation)

- **Latar Belakang (`--background`)**: `oklch(0.135 0.012 255)` (deep navy-slate obsidian, nyaman di mata untuk sesi trading berjam-jam).
- **Kartu & Surface (`--card`)**: `oklch(0.165 0.012 255)` dengan border halus `oklch(0.25 0.012 255)`.
- **Profit / Buy / TP (`--profit`)**: `oklch(0.72 0.16 155)` (emerald tajam dengan kontras WCAG AA di atas latar gelap).
- **Loss / Sell / SL (`--loss`)**: `oklch(0.68 0.19 25)` (crimson ruby jelas dan tegas).
- **Brand / Accent (`--brand`)**: `oklch(0.68 0.14 250)` (sapphire/cobalt profesional).

### Animasi Mikro Finansial

- `.flash-up`: Animasi kilat hijau halus saat harga instrumen bergerak naik.
- `.flash-down`: Animasi kilat merah halus saat harga instrumen bergerak turun.
- `.live-pulse`: Radar beacon berkedip untuk sinyal yang sedang aktif dimonitor oleh engine evaluasi pasar.

---

## 3. Komponen Interaktif Baru

### A. MarketTickerTape (`components/domain/market-ticker-tape.tsx`)

- Terpasang di bagian teratas `PublicLayout`.
- Menampilkan streaming instrumen utama: `XAU/USD`, `EUR/USD`, `GBP/USD`, `USD/JPY`, dan `BTC/USD`.
- Dilengkapi indikator persentase 24 jam, status pasar live, dan animasi mikro pergerakan harga.

### B. TradingCalculator (`components/domain/trading-calculator.tsx`)

- Modal interaktif yang dapat diakses langsung dari header publik, landing page, maupun sidebar dashboard.
- **Kalkulator Ukuran Lot**: Menghitung lot ideal berdasarkan modal akun, toleransi risiko (1-2%), jarak Stop Loss (pips), dan nilai pip.
- **Simulasi Rasio Risk/Reward (RR)**: Visualisasi rasio risiko 1R terhadap target TP dengan indikator kepatuhan minimal 1:1.

### C. TradingChartPreview (`components/domain/trading-chart-preview.tsx`)

- Komponen candlestick chart interaktif berbasis SVG vector.
- Dilengkapi pembacaan nilai OHLC (Open, High, Low, Close) saat kursor diarahkan ke candlestick.
- Pilihan timeframe interaktif (15M, 1H, 4H, 1D).
- Menghiasi landing hero page untuk memberikan kesan trading terminal seketika kepada pengunjung.

### D. Fitur Interaktif pada SignalCard (`components/domain/signal-card.tsx`)

- **1-Click Copy Setup ("Salin Setup")**:
  Menyalin format eksekusi sinyal ke clipboard dengan 1 kali klik (cocok untuk langsung di-paste ke MetaTrader 4/5 atau cTrader).
- **Visual Risk/Reward Gauge**:
  Bar proporsional yang memvisualisasikan jarak risiko (merah) vs target reward (hijau).
- **Status Live Radar Beacon**:
  Titik berkedip real-time untuk sinyal berstatus `active` (Berjalan).

---

## 4. Kepatuhan Aksesibilitas (WCAG AA) & Bahasa

- Seluruh teks angka menggunakan `font-mono tabular-nums` (`JetBrains Mono`).
- Seluruh tombol memiliki label jelas dan target klik minimal 44px di perangkat mobile.
- Tetap mematuhi aturan bahasa di `docs/ui-ux/copywriting.md`:
    - Sapaan santai dan ramah **"kamu"**.
    - Tanpa klaim profit instan atau janji kaya cepat.
    - Disclaimer risiko resmi selalu tertera di setiap tampilan sinyal dan checkout.
