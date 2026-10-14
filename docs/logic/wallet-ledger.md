# Wallet, Ledger & Withdraw

Setiap user punya satu wallet (dibuat otomatis saat pertama dibutuhkan). Hanya trader yang punya pendapatan, tetapi struktur ini berlaku untuk semua user agar nanti bisa dipakai untuk refund/cashback.

Member **membayar** subscription dan produk langsung lewat payment gateway, **bukan** dari saldo wallet (MVP). Wallet adalah tempat pendapatan trader.

## Tabel

`wallets`
| Kolom | Keterangan |
|---|---|
| user_id | unik |
| available_balance | bigint, bisa ditarik |
| pending_balance | bigint, masih dalam hold period |
| locked_balance | bigint, sedang dalam proses withdraw |

`wallet_transactions` (**append-only**, tidak ada update/delete)
| Kolom | Keterangan |
|---|---|
| wallet_id | |
| type | enum, lihat tabel di bawah |
| bucket | `available`, `pending`, `locked` |
| amount | bigint bertanda (+ masuk, − keluar) |
| balance_after | saldo bucket setelah transaksi |
| reference | string **unik** (idempotency), contoh `subscription:12`, `payroll_item:7` |
| description | |
| available_at | untuk `pending`: kapan dirilis |
| released_at | untuk `pending`: kapan sudah dirilis |

Kolom saldo di `wallets` adalah cache dari Σ ledger. Job harian `ReconcileWallets` memverifikasi `Σ amount per bucket = saldo` dan melapor ke admin jika berbeda.

## Tipe Transaksi

| type | bucket | Arah | Pemicu |
|---|---|---|---|
| subscription_income | pending | + | Pembayaran subscription sukses (setelah fee platform) |
| marketplace_income | pending | + | Pembelian produk sukses (setelah komisi) |
| pending_release | pending → available | −/+ | Job harian, `available_at ≤ now` |
| payroll_credit | available | + | Approve payroll |
| refund_reversal | pending | − | Refund subscription/produk yang masih pending |
| withdraw_lock | available → locked | −/+ | User request withdraw (amount + fee) |
| withdraw_paid | locked | − | Admin/disbursement sukses |
| withdraw_unlock | locked → available | −/+ | Withdraw ditolak/gagal |
| admin_adjustment | available | ± | Koreksi manual (wajib catatan, hanya admin) |

Perpindahan antar bucket = **dua baris** transaksi dengan reference turunan (`...:out`, `...:in`).

## Aturan Operasi

- Semua mutasi lewat `App\Actions\Wallet\PostTransaction` di dalam `DB::transaction()` + `Wallet::lockForUpdate()`.
- Saldo bucket tidak boleh negatif. Jika negatif, lempar `InsufficientBalance` dan rollback.
- `reference` duplikat → kembalikan transaksi lama tanpa membuat baru (idempotent).

## Withdraw

`withdrawals`: `user_id`, `bank_account_id`, `amount`, `fee`, `net_amount`, `status`, `reviewed_by`, `reviewed_at`, `rejection_reason`, `disbursement_reference`.

`bank_accounts`: `user_id`, `bank_id`, `account_number`, `account_name`, `is_verified`.

### Syarat request
1. KYC `verified`.
2. Rekening milik user, dan `account_name` cocok dengan nama KYC (normalisasi huruf besar/spasi; jika tidak cocok → admin review manual).
3. `amount ≥ wallet.min_withdraw`.
4. `amount + wallet.withdraw_fee ≤ available_balance`.
5. Tidak ada withdraw lain berstatus `requested`/`processing`.

### Status
```
requested ──approve──► processing ──sukses──► paid
    │                       └──gagal──► failed  (unlock)
    └──reject──► rejected  (unlock)
```
- `requested`: saldo sudah di-lock (`withdraw_lock` sebesar `amount + fee`).
- `paid`: `withdraw_paid` sebesar `amount + fee`. Fee menjadi revenue platform.
- MVP: transfer manual oleh admin, lalu tandai paid. Fase lanjut: Xendit Disbursement + webhook.
