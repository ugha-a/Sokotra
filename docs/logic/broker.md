# Broker Partner & Rebate IB

Sokotra menjembatani member ke **broker berizin Bappebti**. Revenue berasal dari rebate Introducing Broker (IB) per lot yang ditransaksikan akun yang dibuka lewat Sokotra.

## Aturan Broker

- Hanya broker dengan `regulator_license_no` terisi dan sudah diverifikasi admin di situs Bappebti yang boleh `is_active`.
- Halaman broker menampilkan: logo, nomor izin, tipe akun (min deposit, spread, komisi), dan tombol **Buka Akun**.

## Alur Member

1. Member klik **Buka Akun** di `/brokers` (wajib login) → `GET /brokers/{id}/open` membuat `broker_referrals` (`user_id`, `broker_id`, `ref_code` unik 10 karakter, `clicked_at`) → redirect ke `affiliate_url` dengan `{ref}` diganti `ref_code`. Halaman publik tidak menampilkan `affiliate_url` maupun `rebate_per_lot_usd`.
2. Setelah membuka akun di broker, member mengisi **Akun Broker** (`/broker-accounts`): nomor akun MT (login) dan tipe akun → `broker_accounts` (`status = submitted`). Satu login hanya sekali per broker.
3. Admin (`/admin/broker-accounts`) mencocokkan dengan laporan IB broker → `linked` atau `rejected` (alasan). Audit log + notifikasi ke member.
4. Akun `linked` mendapat badge "Akun Broker Terhubung" di profil member (fase lanjut: syarat untuk fitur tertentu).

`broker_accounts`: `user_id`, `broker_id`, `account_login` (unik per broker), `account_type`, `status` (`submitted`, `linked`, `rejected`), `verified_by`, `verified_at`, `notes`.

## Rebate IB

- Admin mengimpor CSV laporan IB di `/admin/broker-rebates`: pilih broker, periode (`YYYY-MM`), dan kurs USD→IDR; CSV wajib berkolom `account_login`, `lots`, `rebate_usd` (baris header). Impor bersifat all-or-nothing: satu baris tidak valid membatalkan seluruh file.
- `ImportBrokerRebates` → `broker_rebates` (`broker_id`, `broker_account_id` nullable jika tidak cocok, `account_login`, `period`, `lots`, `rebate_usd`, `fx_rate`, `rebate_idr` = `round(rebate_usd × fx_rate)`). Unik per (`broker_id`, `account_login`, `period`); impor ulang periode yang sama menggantikan datanya.
- Baris yang `account_login`-nya tidak dikenal tetap disimpan dan ditandai **unmatched** untuk ditindaklanjuti.
- Laporan revenue IB per broker per bulan ada di admin.
- **Fase lanjut**: bagi sebagian rebate ke trader yang di-follow/di-subscribe member tersebut (insentif trader). Persentase diatur di master.
