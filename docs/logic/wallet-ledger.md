# Wallet, Ledger & Withdraw

Setiap user punya satu wallet (dibuat otomatis saat pertama dibutuhkan). Hanya trader yang punya pendapatan, tetapi struktur ini berlaku untuk semua user agar nanti bisa dipakai untuk refund/cashback.

Member **membayar** subscription dan produk langsung lewat payment gateway, **bukan** dari saldo wallet (MVP). Wallet adalah tempat pendapatan trader.

## Tabel

`wallets`

| Kolom             | Keterangan                           |
| ----------------- | ------------------------------------ |
| user_id           | unik                                 |
| available_balance | bigint, bisa ditarik                 |
| pending_balance   | bigint, masih dalam hold period      |
| locked_balance    | bigint, sedang dalam proses withdraw |

`wallet_transactions` (**append-only**, tidak ada update/delete)

| Kolom         | Keterangan                                                                |
| ------------- | ------------------------------------------------------------------------- |
| wallet_id     |                                                                           |
| type          | enum, lihat tabel di bawah                                                |
| bucket        | `available`, `pending`, `locked`                                          |
| amount        | bigint bertanda (+ masuk, − keluar)                                       |
| balance_after | saldo bucket setelah transaksi                                            |
| reference     | string **unik** (idempotency), contoh `subscription:12`, `payroll_item:7` |
| description   |                                                                           |
| available_at  | untuk `pending`: kapan dirilis                                            |

Kolom saldo di `wallets` adalah cache dari Σ ledger. Command `wallets:reconcile` (harian 00:30) memverifikasi `Σ amount per bucket = saldo` dan mencatat log `critical` jika berbeda.

Status pendapatan pending **tidak disimpan dengan meng-update baris ledger**. Sebuah transaksi pending dianggap sudah dirilis jika ada transaksi dengan reference `release:{id}:in`, dan dianggap sudah di-reverse (refund) jika ada `reverse:{id}`. Command `wallets:release` berjalan tiap jam.

## Tipe Transaksi

| type                | bucket              | Arah | Pemicu                                                |
| ------------------- | ------------------- | ---- | ----------------------------------------------------- |
| subscription_income | pending             | +    | Pembayaran subscription sukses (setelah fee platform) |
| marketplace_income  | pending             | +    | Pembelian produk sukses (setelah komisi)              |
| pending_release     | pending → available | −/+  | `wallets:release` tiap jam, `available_at ≤ now`      |
| payroll_credit      | available           | +    | Approve payroll                                       |
| refund_reversal     | pending             | −    | Refund subscription/produk yang masih pending         |
| withdraw_lock       | available → locked  | −/+  | User request withdraw (amount + fee)                  |
| withdraw_paid       | locked              | −    | Admin/disbursement sukses                             |
| withdraw_unlock     | locked → available  | −/+  | Withdraw ditolak/gagal                                |
| admin_adjustment    | available           | ±    | Koreksi manual (wajib catatan, hanya admin)           |

Perpindahan antar bucket = **dua baris** transaksi dengan reference turunan (`...:out`, `...:in`).

## Aturan Operasi

- Semua mutasi lewat `App\Actions\Wallet\PostTransaction` di dalam `DB::transaction()` + `Wallet::lockForUpdate()`.
- Saldo bucket tidak boleh negatif. Jika negatif, lempar `InsufficientBalance` dan rollback.
- `reference` duplikat → kembalikan transaksi lama tanpa membuat baru (idempotent).

## Withdraw

`withdrawals`: `user_id`, `bank_account_id`, `amount`, `fee`, `net_amount` (= `amount`; biaya dipotong dari saldo, bukan dari transfer), `status`, `name_mismatch`, `reviewed_by`, `reviewed_at`, `paid_at`, `rejection_reason`, `disbursement_reference`.

`bank_accounts`: `user_id`, `bank_id`, `account_number` (**terenkripsi**, ditampilkan tersamarkan), `account_name`, `is_verified`. Maksimal `wallet.max_bank_accounts` rekening per pengguna. Rekening yang pernah dipakai untuk penarikan tidak bisa dihapus.

### Syarat request

1. KYC `verified`.
2. Rekening milik user. Jika `account_name` tidak cocok dengan nama KYC (setelah normalisasi huruf besar/spasi), penarikan tetap dibuat dengan `name_mismatch = true` dan diberi peringatan di antrian admin.
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
- MVP: transfer manual oleh admin (`/admin/withdrawals`): **Setujui** → transfer → **Tandai dibayar** (wajib referensi transfer). **Tolak** saat `requested` → `rejected`; saat `processing` → `failed`. Keduanya mengembalikan saldo. Setiap langkah tercatat di audit log dan dikirim ke pengguna (email + notifikasi). Fase lanjut: Xendit Disbursement + webhook.
- Admin dapat mengoreksi saldo tersedia di `/admin/wallets` (wajib catatan, `admin_adjustment`, audit `wallet.adjusted`).
