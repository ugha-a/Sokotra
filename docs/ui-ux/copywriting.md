# Copywriting & Bahasa

## Gaya

- Bahasa Indonesia yang santai tapi sopan. Sapaan **"kamu"**.
- Kalimat pendek, kata kerja aktif. Tombol memakai kata kerja: "Publish Sinyal", "Langganan", "Tarik Saldo", bukan "OK" atau "Submit".
- Istilah trading tetap dalam bahasa aslinya: Buy, Sell, Stop Loss (SL), Take Profit (TP), pips, Risk Reward (RR), lot, broker, Expert Advisor (EA).
- Pesan error menjelaskan cara memperbaiki: "Risk Reward minimal 1:1. Jauhkan TP atau dekatkan SL." Bukan "Input tidak valid."
- Angka selalu diberi konteks: "Win rate 58% dari 120 sinyal (90 hari)".

## Kata & Klaim Terlarang

Dilarang di UI, notifikasi, deskripsi produk, profil trader, dan materi marketing:

| Terlarang | Alternatif |
|---|---|
| "pasti profit", "dijamin untung", "anti loss", "tanpa risiko" | "track record terverifikasi", "statistik transparan" |
| "cepat kaya", "passive income pasti", "balik modal" | "belajar dari trader berpengalaman" |
| "win rate 100%" (kecuali data asli dengan sampel ditampilkan) | tampilkan angka asli + jumlah sinyal |
| "investasi" untuk subscription/sinyal | "langganan analisa" |
| "Sokotra mengelola dana kamu" | Sokotra tidak mengelola dana |

Validator `App\Support\ProhibitedClaims::find(string $text): array` memeriksa deskripsi produk, bio trader, dan analisa sinyal. Daftar kata disimpan di setting `content.prohibited_phrases` (bisa ditambah admin).

## Teks Wajib

### Disclaimer risiko (default `content.risk_disclaimer`)
> Trading forex, emas, dan instrumen derivatif lainnya berisiko tinggi dan dapat menyebabkan kehilangan seluruh modal. Kinerja masa lalu tidak menjamin hasil di masa depan. Analisa di Sokotra adalah pendapat masing-masing trader, bukan nasihat investasi. Keputusan dan risiko trading sepenuhnya ada pada kamu.

### Versi singkat (SignalCard)
> Bukan nasihat investasi. Kinerja masa lalu tidak menjamin hasil.

### Checkbox checkout
> Saya memahami bahwa trading berisiko tinggi, analisa ini tidak menjamin profit, dan saya bertanggung jawab atas keputusan trading saya sendiri.

### Konfirmasi publish sinyal
> Setelah dipublish, entry, Stop Loss, dan Take Profit **tidak bisa diubah atau dihapus**. Hasilnya dinilai otomatis dari harga pasar dan masuk ke statistik kamu.

## Label Status (konsisten di semua halaman)

| Status | Label |
|---|---|
| signal.pending | Menunggu Entry |
| signal.active | Berjalan |
| signal.tp_hit | Kena TP |
| signal.sl_hit | Kena SL |
| signal.closed_timeout | Ditutup (Waktu Habis) |
| signal.expired | Kedaluwarsa |
| signal.cancelled | Dibatalkan |
| withdraw.requested / processing / paid / rejected / failed | Diajukan / Diproses / Dibayar / Ditolak / Gagal |
| kyc.submitted / verified / rejected | Menunggu Review / Terverifikasi / Ditolak |
| subscription.active / expired | Aktif / Berakhir |

Label didefinisikan sekali: backend lewat method `label()` di enum, dikirim ke frontend bersama value-nya.
