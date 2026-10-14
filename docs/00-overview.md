# Sokotra — Overview

Sokotra adalah platform perantara antara **orang yang baru masuk dunia trading** (FOMO, pemula, dan orang yang ingin serius belajar) dengan **trader analis** yang track record-nya terverifikasi otomatis. Platform juga menjembatani user ke **broker berizin Bappebti**.

Referensi model bisnis: Traders Family (tradersfamily.id).

## Persona

| Persona | Kebutuhan | Yang didapat di Sokotra |
|---|---|---|
| **Member** (FOMO / pemula / serius belajar) | Ingin ikut trading tapi belum bisa analisa | Follow trader, sinyal gratis & premium, marketplace indikator/EA, buka akun broker, edukasi |
| **Trader Analis** | Ingin dibayar dari kemampuan analisa | Channel sinyal, ranking, followers, gaji bulanan, reward streak, jual subscription & indikator/EA |
| **Admin** | Mengelola platform | Master data, approval (trader, KYC, produk, withdraw, payroll, broker), laporan revenue |

Satu user bisa menjadi Member sekaligus Trader setelah lolos approval.

## Sumber Revenue Platform

1. **Fee subscription**: persentase dari setiap langganan channel premium (`platform_settings.subscription_fee_percent`).
2. **Komisi marketplace**: persentase dari setiap penjualan indikator/EA (`platform_settings.marketplace_commission_percent`).
3. **Rebate broker (IB)**: komisi per lot dari broker partner atas akun yang dibuka lewat Sokotra.
4. **Fee withdraw** (`platform_settings.withdraw_fee`).

Biaya platform: gaji bulanan trader dan reward streak (lihat `logic/payroll-rewards.md`).

## Glosarium

| Istilah | Arti |
|---|---|
| **Sinyal / Analisa** | Rencana trading dari trader: instrumen, arah, entry, SL, TP, dan alasan analisa |
| **Pip** | Satuan pergerakan harga terkecil yang lazim. Besarnya per instrumen diatur di master `instruments.pip_size` |
| **SL / TP** | Stop Loss / Take Profit. Keduanya wajib di setiap sinyal |
| **RR (Risk Reward)** | `jarak TP / jarak SL` dari entry |
| **R-multiple** | Satuan skor. Menang = `+RR`, kalah = `-1`. Adil antar instrumen |
| **Streak** | Jumlah kemenangan berurutan (urut `closed_at`) |
| **Rank** | Level trader (diatur di master `ranks`), dihitung ulang mingguan |
| **Follower valid** | Follower dengan email terverifikasi dan aktif dalam N hari terakhir (master) |
| **Premium channel** | Sinyal berbayar dari trader rank menengah ke atas |
| **Ledger** | Catatan mutasi saldo wallet yang tidak bisa diubah (append-only) |
| **IB** | Introducing Broker, skema afiliasi broker yang memberi rebate per lot |

## Prinsip Produk

1. **Track record jujur**: sinyal dikunci saat publish dan dinilai otomatis dari harga pasar. Kekalahan ikut ditampilkan.
2. **Semua angka bisnis bisa diatur dari admin**: tidak ada angka bisnis yang di-hardcode (lihat `logic/master-data.md`).
3. **Tidak ada janji profit**: lihat `logic/compliance.md` dan `ui-ux/copywriting.md`.

## Peta Dokumen

| Dokumen | Isi |
|---|---|
| `architecture.md` | Stack, struktur folder, konvensi kode, testing |
| `logic/master-data.md` | Semua master data dan pemakainya |
| `logic/trader-onboarding.md` | Rekrutmen trader, profil, status |
| `logic/signal-lifecycle.md` | Sinyal: validasi, state machine, engine evaluasi |
| `logic/scoring-ranking.md` | Statistik, skor, rank, leaderboard, follow |
| `logic/payroll-rewards.md` | Gaji bulanan dan reward |
| `logic/wallet-ledger.md` | Wallet, ledger, withdraw |
| `logic/subscription.md` | Channel premium |
| `logic/marketplace.md` | Indikator/EA |
| `logic/broker.md` | Broker partner dan rebate IB |
| `logic/compliance.md` | Kepatuhan, KYC, disclaimer, anti-fraud |
| `ui-ux/design-system.md` | Token warna, tipografi, format angka |
| `ui-ux/components.md` | Katalog komponen reusable |
| `ui-ux/sitemap-flows.md` | Sitemap per role dan user flow |
| `ui-ux/copywriting.md` | Gaya bahasa, teks wajib, kata terlarang |
