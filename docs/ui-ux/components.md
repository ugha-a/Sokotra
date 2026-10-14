# Katalog Komponen

- `resources/js/components/ui/` = primitives shadcn. **Jangan** ditambah logic domain.
- `resources/js/components/domain/` = komponen bisnis di bawah ini. Sebelum membuat komponen baru, cek katalog ini. Jika komponen baru dibuat, **tambahkan ke katalog**.
- Props memakai tipe dari `resources/js/types/` (contoh `Signal`, `TraderSummary`, `Rank`).

## Komponen Domain

| Komponen           | Props utama                                    | Dipakai di                      | Catatan                                                                                                                                       |
| ------------------ | ---------------------------------------------- | ------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| `DirectionBadge`   | `direction`                                    | SignalCard, tabel               | ▲ Buy (profit) / ▼ Sell (loss) + teks                                                                                                         |
| `SignalStatusPill` | `status`                                       | SignalCard, tabel               | Warna: active=brand, pending=pending, tp_hit=profit, sl_hit=loss, lainnya=neutral-status                                                      |
| `SignalCard`       | `signal`, `viewerCanSeeDetails`                | Feed, profil trader             | Instrumen, DirectionBadge, entry/SL/TP (atau blok terkunci `PremiumLock`), RR, status, hasil (R & pips), waktu, disclaimer singkat            |
| `PremiumLock`      | `trader`, `cta`                                | SignalCard, halaman premium     | Blur + ikon Lock + tombol "Langganan"                                                                                                         |
| `RankBadge`        | `rank`, `size`                                 | Semua tempat nama trader muncul | Warna dari `rank.color`                                                                                                                       |
| `TraderAvatar`     | `trader`, `size`                               |                                 | Avatar + `BadgeCheck` jika KYC verified                                                                                                       |
| `TraderCard`       | `trader` (summary + stats)                     | Leaderboard, discovery          | Avatar, nama, RankBadge, win rate, total R, sinyal closed, followers, FollowButton                                                            |
| `StatTile`         | `label`, `value`, `hint`, `tone`               | Dashboard, profil               | Angka KPI besar + konteks (periode/jumlah sampel)                                                                                             |
| `StatsGrid`        | `stats`, `period`                              | Profil trader, dashboard trader | Kumpulan StatTile + PeriodTabs                                                                                                                |
| `PeriodTabs`       | `value`, `onChange`                            |                                 | Mingguan / Bulanan / 90 hari / All-time                                                                                                       |
| `EquityCurve`      | `points` (kumulatif R)                         | Profil trader                   | Line chart; garis nol jelas; drawdown diarsir                                                                                                 |
| `StreakIndicator`  | `current`, `best`, `required`                  | Dashboard trader                | Progress menuju syarat streak payroll                                                                                                         |
| `FollowButton`     | `trader`, `isFollowing`                        |                                 | Optimistic update                                                                                                                             |
| `PriceTicker`      | `instrument`, `quote`                          | Form sinyal                     | Bid/ask live                                                                                                                                  |
| `SignalForm`       | `instruments`, `rules`                         | Trader: posting sinyal          | Hitung RR & pips real-time, validasi sama dengan backend, preview sebelum publish, ConfirmDialog "Sinyal tidak bisa diubah setelah dipublish" |
| `WalletBalance`    | `wallet`                                       | Wallet                          | Available / pending / locked + tooltip penjelasan                                                                                             |
| `LedgerTable`      | `transactions`                                 | Wallet, admin                   | Tipe, nominal bertanda, saldo setelah, referensi                                                                                              |
| `MoneyInput`       | `value`, `min`, `max`                          | Withdraw, harga                 | Input Rupiah berformat                                                                                                                        |
| `PriceRangeHint`   | `min`, `max`                                   | Atur harga channel              | Rentang dari rank                                                                                                                             |
| `ProductCard`      | `product`                                      | Marketplace                     | Gambar, platform badge, rating, harga, penjual + RankBadge                                                                                    |
| `BrokerCard`       | `broker`                                       | Halaman broker                  | Logo, nomor izin Bappebti (wajib tampil), tipe akun, tombol Buka Akun                                                                         |
| `RiskDisclaimer`   | `variant` (`inline`, `card`, `checkbox`)       | Footer, SignalCard, checkout    | Teks dari setting `content.risk_disclaimer` (shared Inertia prop)                                                                             |
| `StatusPill`       | `status`, `map`                                | Approval, withdraw, order       | Generik untuk status non-sinyal                                                                                                               |
| `DataTable`        | `columns`, `rows`, `pagination`, `filters`     | Semua tabel admin               | Sort, filter, pagination Laravel; mobile → list card                                                                                          |
| `EmptyState`       | `icon`, `title`, `description`, `action`       |                                 |                                                                                                                                               |
| `ConfirmDialog`    | `title`, `description`, `confirmLabel`, `tone` | Aksi finansial & destruktif     |                                                                                                                                               |
| `ApprovalActions`  | `onApprove`, `onReject`                        | Admin approval                  | Reject wajib alasan                                                                                                                           |
| `MasterForm`       | `fields` schema                                | Admin master data               | Form generik dari schema agar semua master konsisten                                                                                          |

## Layout

| Layout                      | Untuk                                                                                                      |
| --------------------------- | ---------------------------------------------------------------------------------------------------------- |
| `PublicLayout`              | Landing, leaderboard, profil trader, marketplace, broker, halaman konten. Header + footer (RiskDisclaimer) |
| `AppSidebarLayout` (bawaan) | Area login. Item sidebar disusun dari role (lihat `sitemap-flows.md`)                                      |
| `AuthLayout` (bawaan)       | Login/registrasi                                                                                           |
| `SettingsLayout` (bawaan)   | Pengaturan akun                                                                                            |

## Pola Form

- Memakai `<Form>` Inertia + Wayfinder action (pola bawaan starter kit).
- Error per field memakai `InputError`. Error umum memakai toast `sonner`.
- Tombol submit menampilkan state processing dan dinonaktifkan saat processing.
- Validasi utama ada di FormRequest backend. Validasi frontend hanya untuk UX.
