# Kepatuhan, KYC & Perlindungan Konsumen

> **Wajib sebelum launch:** konsultasi dengan konsultan hukum terkait status jasa sinyal berbayar, skema IB, dan marketplace EA di bawah **Bappebti** (perdagangan berjangka/forex/komoditi) dan **OJK** (derivatif efek & aset kripto sejak Januari 2025, UU P2SK). Pantau penindakan **Satgas PASTI** terhadap tawaran investasi ilegal. Dokumen ini adalah aturan produk, bukan nasihat hukum.

## Prinsip

1. **Tidak ada janji profit.** Semua copy di UI, notifikasi, produk, dan marketing tunduk pada `ui-ux/copywriting.md`.
2. **Track record transparan.** Sinyal kalah ditampilkan sama jelasnya dengan sinyal menang. Statistik dihitung sistem, bukan diinput trader.
3. **Sokotra bukan pialang dan tidak mengelola dana trading user.** Dana trading ada di broker. Sokotra hanya menyalurkan analisa, edukasi, dan alat.
4. **Hanya broker berizin** (lihat `broker.md`).

## Disclaimer Risiko

- Teks dari `content.risk_disclaimer` (admin) ditampilkan:
  - footer setiap halaman publik
  - di setiap SignalCard (versi singkat + link)
  - checkbox wajib sebelum checkout subscription dan produk
  - saat registrasi (persetujuan S&K + disclaimer)
- Persetujuan disimpan di `user_consents` (`user_id`, `type`, `version`, `accepted_at`, `ip`).

## KYC

Wajib untuk: menjadi trader, withdraw, menjual produk, membuka premium.

`kyc_verifications`: `user_id`, `full_name`, `nik` (**terenkripsi**, plus `nik_hash` unik untuk deteksi duplikat), `birth_date`, `id_card_path`, `selfie_path` (disk private), `status` (`submitted`, `verified`, `rejected`), `reviewed_by`, `reviewed_at`, `rejection_reason`.

- Satu NIK = satu akun (`nik_hash` unik).
- Dokumen hanya bisa dilihat admin dengan permission `kyc.review`, lewat signed URL. Setiap akses dicatat di `kyc_access_logs`.
- Fase lanjut: OCR KTP & liveness check via provider.

## Data Pribadi (UU PDP)

- Enkripsi kolom sensitif (`nik`, `account_number`) dengan cast `encrypted`.
- Data KYC user yang menghapus akun disimpan sesuai kewajiban retensi, lalu dihapus. Masa retensi ditentukan konsultan hukum.
- Kebijakan privasi di `content_pages`.

## Pajak

- Gaji/reward dan pendapatan trader berpotensi kena PPh. `payroll_items.tax_amount` disiapkan. Rumus ditentukan bersama konsultan pajak sebelum payroll pertama.
- Platform menerbitkan bukti potong jika diwajibkan.

## Anti-Fraud & Audit

- Lihat tabel anti-fraud di `scoring-ranking.md`.
- Audit log admin: `admin_audit_logs` untuk approve/reject KYC, trader, produk, withdraw, payroll, adjustment, dan perubahan master.
- Rate limit: publish sinyal, follow, login, dan checkout.
