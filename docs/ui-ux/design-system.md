# Design System

Basis: shadcn/ui + Tailwind v4 dari starter kit. Token didefinisikan sebagai CSS variable di `resources/css/app.css` (`:root` dan `.dark`) lalu dipetakan ke `@theme`. **Jangan memakai warna Tailwind mentah** (`text-green-500`, `bg-red-600`) untuk makna bisnis. Selalu pakai token semantik di bawah.

## Karakter Visual

- **Tenang dan terpercaya**, bukan "kasino". Hindari animasi berlebihan, confetti, dan warna neon untuk profit.
- Angka adalah konten utama: tabular, rata kanan di tabel, dan selalu diberi konteks (periode, jumlah sinyal).
- Light & dark mode didukung penuh (appearance toggle bawaan starter kit).

## Token Warna Semantik

Tambahan di atas token shadcn bawaan:

| Token                            | Makna                                          | Light (oklch)                 | Dark (oklch)                  |
| -------------------------------- | ---------------------------------------------- | ----------------------------- | ----------------------------- |
| `--brand` / `--brand-foreground` | Aksen utama Sokotra (tombol utama, link aktif) | `0.55 0.15 250` / `0.985 0 0` | `0.68 0.14 250` / `0.145 0 0` |
| `--profit`                       | Menang, TP, R positif, arah **buy**            | `0.60 0.15 155`               | `0.72 0.16 155`               |
| `--loss`                         | Kalah, SL, R negatif, arah **sell**            | `0.58 0.20 25`                | `0.68 0.19 25`                |
| `--pending`                      | Sinyal pending / menunggu review               | `0.75 0.15 80`                | `0.80 0.14 80`                |
| `--neutral-status`               | Expired, cancelled, closed timeout             | `0.556 0 0`                   | `0.708 0 0`                   |
| `--premium`                      | Badge & kunci konten premium                   | `0.70 0.14 70`                | `0.78 0.13 70`                |

Setiap token punya pasangan `-foreground` dan varian latar lembut `-soft` (alpha 12%) untuk pill/badge, contoh `bg-profit-soft text-profit`.

**Aturan:** buy/sell memakai warna profit/loss **disertai label teks dan ikon** (▲ Buy / ▼ Sell). Jangan mengandalkan warna saja (aksesibilitas buta warna).

### Warna Rank

Diambil dari kolom `ranks.color` (nama token):

| Rank   | Token             |
| ------ | ----------------- |
| Newbie | `rank-1` (abu)    |
| Rookie | `rank-2` (hijau)  |
| Pro    | `rank-3` (biru)   |
| Elite  | `rank-4` (ungu)   |
| Master | `rank-5` (oranye) |
| Legend | `rank-6` (emas)   |

## Tipografi

- Font: `Instrument Sans` (bawaan). Angka harga, pips, dan R memakai `font-mono tabular-nums` (`JetBrains Mono`, fallback `ui-monospace`).
- Skala: `text-xs` (meta), `text-sm` (body tabel/form), `text-base` (body), `text-lg` (judul card), `text-2xl` (judul halaman), `text-3xl font-semibold` (angka KPI).
- Judul halaman memakai komponen `Heading` bawaan starter kit.

## Spacing & Layout

- Grid 4px (skala Tailwind). Padding halaman `p-4 md:p-6`. Jarak antar section `gap-6`.
- Card: `rounded-xl border bg-card p-4 md:p-6`.
- Maksimal lebar konten publik `max-w-6xl mx-auto`. Area app memakai sidebar layout bawaan.
- Mobile-first: semua halaman harus berfungsi di lebar 360px. Tabel lebar di mobile berubah menjadi list card.

## Format Angka (wajib lewat `resources/js/lib/format.ts`)

| Fungsi                         | Contoh output            | Aturan                                        |
| ------------------------------ | ------------------------ | --------------------------------------------- |
| `formatRupiah(150000)`         | `Rp150.000`              | Tanpa desimal, pemisah titik                  |
| `formatPrice(1.08345, digits)` | `1.08345`                | Jumlah desimal dari `instrument.digits`       |
| `formatPips(-12.5)`            | `−12,5 pips`             | Tanda eksplisit (+/−), 1 desimal              |
| `formatR(1.8)`                 | `+1,80R`                 | Tanda eksplisit, 2 desimal                    |
| `formatPercent(55.234)`        | `55,2%`                  | 1 desimal                                     |
| `formatDate(iso)`              | `15 Okt 2026, 14.30 WIB` | Zona `Asia/Jakarta`                           |
| `formatRelative(iso)`          | `5 menit yang lalu`      | Selalu angka (bukan "kemarin dulu")           |
| `formatCompact(1250)`          | `1,2 rb`                 | Followers; dipotong, tidak dibulatkan ke atas |

Locale `id-ID`. Backend mengirim angka mentah (integer rupiah, decimal string), tidak mengirim string yang sudah diformat.

## Ikon

`lucide-react` (bawaan). Ikon tetap untuk konsep tetap:

| Konsep       | Ikon                                |
| ------------ | ----------------------------------- |
| Buy / Sell   | `TrendingUp` / `TrendingDown`       |
| Premium      | `Lock` (terkunci) / `Crown` (badge) |
| Followers    | `Users`                             |
| Streak       | `Flame`                             |
| Wallet       | `Wallet`                            |
| Verified KYC | `BadgeCheck`                        |
| Broker       | `Building2`                         |
| Marketplace  | `Store`                             |

## State Wajib di Setiap Halaman Data

Loading (skeleton), kosong (`EmptyState` + ajakan bertindak), error (`AlertError` bawaan), dan konten. Aksi destruktif atau finansial selalu lewat `ConfirmDialog`.
