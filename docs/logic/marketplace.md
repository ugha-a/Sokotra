# Marketplace Indikator / EA

Trader bisa menjual alat analisa: indikator, Expert Advisor (EA), dan template/script.

## Penjual

- Rank `can_sell_products = true` (seed: Pro ke atas) dan KYC `verified`.
- Jika rank turun: produk yang sudah tayang tetap dijual, tetapi trader tidak bisa menambah produk baru.

## Tabel

`products`

| Kolom                                       | Keterangan                                                |
| ------------------------------------------- | --------------------------------------------------------- |
| seller_id, category_id                      |                                                           |
| name, slug, summary, description (markdown) |                                                           |
| platform                                    | `mt4`, `mt5`, `tradingview`                               |
| price                                       | bigint, 0 = gratis                                        |
| version                                     | string                                                    |
| file_path                                   | disk private (tidak untuk TradingView)                    |
| tradingview_url                             | untuk script invite-only                                  |
| screenshots                                 | json path[] di disk `public` (maks. 5 gambar)             |
| status                                      | `draft`, `in_review`, `published`, `rejected`, `unlisted` |
| rejection_reason                            |                                                           |
| sales_count, rating_avg, rating_count       | cache                                                     |

`orders`: `buyer_id`, `product_id`, `price_paid`, `commission`, `seller_income`, `status` (`pending_payment`, `paid`, `refunded`, `cancelled`), `license_key` (unik, format `SKT-XXXX-XXXX-XXXX-XXXX`, dibuat saat paid), `paid_at`, `refunded_at`, `refund_reason`. Pembayaran memakai tabel `payments` yang sama dengan subscription (`OrderPaymentHandler`).

`product_reviews`: `order_id` unik, `rating` 1–5, `comment`. **Hanya pembeli** dengan order `paid`.

`product_versions` (fase lanjut): riwayat file per versi.

## Alur Penjual

1. Buat produk (`draft`) → upload file (≤ `marketplace.max_file_mb`, ekstensi: `.ex4`, `.ex5`, `.mq4`, `.mq5`, `.zip`) → **Ajukan review** → `in_review`.
2. Admin memeriksa:
    - file bisa dibuka dan bukan malware
    - deskripsi tidak mengandung klaim terlarang (lihat `ui-ux/copywriting.md`; validator otomatis menandai kata terlarang)
    - screenshot asli
3. `published` atau `rejected` (wajib alasan). Edit setelah published → kembali `in_review` untuk perubahan file/deskripsi. Perubahan harga tidak perlu review.

## Alur Pembeli

1. Halaman produk → **Beli** → centang disclaimer → checkout gateway (sama seperti subscription, `payments.payable` = order).
2. Webhook paid:
    - `orders.status = paid`, buat `license_key`
    - `PostTransaction(marketplace_income, pending, seller_income, available_at = now + hold_period_days)`
3. Pembeli mendapat akses **Download** di **Pembelian Saya** (`/purchases`): tombol mengarah ke `/purchases/{order}/download` yang membuat signed URL baru (berlaku 10 menit) ke file privat. Produk TradingView diarahkan ke link script. Penjual menerima notifikasi `ProductSold`; `sales_count` bertambah.
4. Produk gratis: order langsung `paid` tanpa gateway.

Pembeli yang sama tidak bisa membeli produk yang sama dua kali (selama order lama tidak di-refund).

## Komisi

`commission = price_paid × marketplace.commission_percent` (dibulatkan ke bawah), `seller_income = price_paid − commission`.

## Refund

Sama dengan subscription: admin (`/admin/orders`), selama pendapatan penjual masih tertahan, `refund_reversal` dengan reference `reverse:{id}`, `sales_count` dikurangi, akses download dicabut.

## Halaman

- Publik: `/marketplace` (filter kategori, platform, urutan) dan `/marketplace/{slug}` (screenshot, deskripsi, ulasan, beli).
- Penjual: `/trader/products` (daftar + ajukan review) dan form tambah/ubah.
- Admin: `/admin/products` (moderasi per status, download file via signed URL 30 menit) dan `/admin/orders` (pesanan + refund).
