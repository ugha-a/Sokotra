# Sitemap & User Flow

## Sitemap

### Publik (tanpa login) — `PublicLayout`
| URL | Halaman |
|---|---|
| `/` | Landing: value proposition, top trader minggu ini, cara kerja, broker partner, disclaimer |
| `/leaderboard` | Leaderboard (Mingguan/Bulanan/All-time, filter instrumen), tab Most Consistent & Featured |
| `/traders/{slug}` | Profil trader: StatsGrid, EquityCurve, riwayat sinyal (closed lengkap, premium terbuka terkunci), Follow, Langganan, produk |
| `/marketplace` | Daftar produk (filter kategori, platform, harga, rating) |
| `/marketplace/{slug}` | Detail produk |
| `/brokers` | Daftar broker partner |
| `/pages/{slug}` | S&K, kebijakan privasi, disclaimer risiko, FAQ |

### Member (login) — sidebar
| Menu | URL |
|---|---|
| Beranda | `/dashboard` (feed sinyal dari trader yang di-follow/di-subscribe, ringkasan) |
| Sinyal | `/signals` (feed semua sinyal free + premium terkunci, filter) |
| Trader Diikuti | `/following` |
| Langganan Saya | `/subscriptions` |
| Pembelian Saya | `/purchases` (download + lisensi) |
| Akun Broker | `/broker-accounts` |
| Notifikasi | `/notifications` |
| Jadi Trader | `/become-trader` (hanya jika belum trader) |

### Trader (tambahan) — grup "Trader"
| Menu | URL |
|---|---|
| Dashboard Trader | `/trader` (StatsGrid, StreakIndicator, rank & progress ke rank berikutnya, estimasi payroll bulan ini) |
| Posting Sinyal | `/trader/signals/create` |
| Sinyal Saya | `/trader/signals` |
| Channel Premium | `/trader/channel` (harga, subscriber) — hanya jika rank mengizinkan |
| Produk Saya | `/trader/products` — hanya jika rank mengizinkan |
| Gaji & Reward | `/trader/payroll` |
| Wallet | `/wallet` (saldo, ledger, withdraw, rekening) |

### Admin — grup "Admin"
| Menu | URL |
|---|---|
| Ringkasan | `/admin` (KPI: user, trader aktif, revenue per sumber, antrian approval) |
| Approval | `/admin/traders`, `/admin/kyc`, `/admin/products`, `/admin/withdrawals`, `/admin/broker-accounts` |
| Payroll | `/admin/payroll` |
| Pengguna | `/admin/users` |
| Sinyal & Fraud | `/admin/signals`, `/admin/fraud-flags` |
| Rebate IB | `/admin/broker-rebates` |
| Master Data | `/admin/master/{instruments,ranks,payroll-rules,follower-brackets,brokers,product-categories,banks,content-pages}`, `/admin/settings` (Signal Rules, Score, Wallet, Subscription, Marketplace, Konten) |
| Audit Log | `/admin/audit-logs` |

Pengaturan akun: `/settings/*` (bawaan starter kit).

## User Flow

### 1. Onboarding Member
Landing → Daftar (setuju S&K + disclaimer) → Verifikasi email → Pilih instrumen yang diminati (opsional) → Rekomendasi 5 trader teratas untuk di-follow → Dashboard.

### 2. Jadi Trader
`/become-trader` → Profil trader → KYC (data + foto KTP + selfie) → Setuju S&K trader → Status "Menunggu review" → Notifikasi approved/rejected → Dashboard Trader.

### 3. Posting Sinyal
Pilih instrumen (PriceTicker tampil) → arah → tipe order → entry/SL/TP (RR & pips dihitung live, error jika < min) → visibilitas → analisa + gambar chart → Preview → ConfirmDialog "Tidak bisa diubah setelah dipublish" → Publish → muncul di feed dan follower menerima notifikasi.

### 4. Langganan Premium
Profil trader → Langganan → pilih durasi → ringkasan harga → checkbox disclaimer → Bayar (gateway) → kembali ke halaman status → aktif → sinyal premium terbuka.

### 5. Beli Produk
Marketplace → Detail → Beli → checkbox disclaimer → Bayar → Pembelian Saya → Download + license key → Beri review.

### 6. Withdraw
Wallet → Tambah rekening (jika belum ada) → Tarik Saldo → nominal (min, fee, net ditampilkan) → ConfirmDialog → status "Diajukan" → notifikasi Dibayar/Ditolak.

### 7. Buka Akun Broker
Brokers → pilih broker → Buka Akun (tab baru ke broker dengan ref) → kembali → Hubungkan Akun (nomor akun MT) → status "Diverifikasi" → Terhubung.

### 8. Admin Payroll Bulanan
Notifikasi "Draft payroll {bulan} siap" → `/admin/payroll/{bulan}` → review item (layak/tidak + alasan) → hold item tertentu (catatan) → Approve → kredit wallet → selesai.
