# Panduan Penggunaan — Kaya Kayu Workshop

Panduan ini menjelaskan alur kerja sistem dari sudut pandang masing-masing peran. Sistem dibuat untuk membantu pencatatan pesanan, produksi, pembayaran, dan stok, bukan untuk menggantikan komunikasi dengan pelanggan.

## 1. Masuk ke Sistem

1. Buka alamat aplikasi, misalnya `http://127.0.0.1:8000`.
2. Masukkan **username** dan **password**.
3. Setelah masuk, pengguna diarahkan ke dashboard sesuai peran.

Akun demo:

| Username | Peran | Password |
|---|---|---|
| `owner` | Pemilik | `password` |
| `admin` | Admin / Sales | `password` |
| `pegawai` | Pegawai Produksi | `password` |
| `finishing` | Pegawai Finishing | `password` |

> Ganti password akun demo sebelum sistem dipakai untuk data nyata.

## 2. Hak Akses per Peran

| Menu / Aksi | Pemilik | Admin / Sales | Pegawai |
|---|---:|---:|---:|
| Dashboard | Ya | Ya | Ya |
| Pesanan | Ya | Ya | Tidak |
| Papan produksi | Ya | Ya | Ya |
| Konfirmasi item selesai | Ya | Tidak | Tidak |
| Konfirmasi pesanan selesai produksi | Ya | Tidak | Tidak |
| Pembayaran | Ya | Ya | Tidak |
| Pengalihan pesanan | Ya | Tidak | Tidak |
| Inventori bahan | Ya | Ya | Tidak |
| Katalog, BOM, produk jadi | Ya | Ya | Tidak |
| Master data | Ya | Ya | Tidak |
| Laporan | Ya | Ya | Tidak |
| Pengguna | Ya | Tidak | Tidak |
| Log aktivitas | Ya | Tidak | Tidak |
| Pengaturan | Ya | Tidak | Tidak |

Jika pengguna membuka menu di luar hak aksesnya, sistem menampilkan halaman **403 — Akses Ditolak**.

## 3. Alur Pesanan

### 3.1. Membuat Pesanan

1. Buka **Pesanan → Pesanan Baru**.
2. Pilih pelanggan yang sudah ada. Jika pelanggan baru, tambahkan melalui **Master → Pelanggan** terlebih dahulu.
3. Pilih kanal pesanan: WhatsApp, walk-in, telepon, atau lainnya.
4. Tentukan tanggal pesanan dan **satu tenggat kesepakatan** untuk seluruh item.
5. Tambahkan satu atau beberapa item:
   - pilih model produk dari katalog, atau
   - biarkan model kosong untuk produk custom.
6. Isi ukuran, jumlah, jenis kayu, kualitas kayu, dan tingkat kesulitan.
7. Harga item dihitung otomatis. Jika perlu menimpa harga, isi alasan override agar tercatat.
8. Periksa ringkasan harga, lalu simpan.

**Status awal pesanan:**

- Pesanan yang dibuat **Pemilik** langsung berstatus `confirmed`.
- Pesanan yang dibuat **Admin/Sales** berstatus `pending_approval` dan harus disetujui Pemilik.

### 3.2. Persetujuan Pemilik

1. Buka detail pesanan yang berstatus **Menunggu Approval**.
2. Periksa item, harga, tenggat, dan catatan.
3. Klik **Setujui Pesanan**.
4. Setelah disetujui, status pesanan menjadi `confirmed` dan item masuk antrian produksi.

Pemilik juga dapat membatalkan pesanan dengan alasan yang akan tercatat.

### 3.3. Pembayaran DP dan Pelunasan

1. Buka detail pesanan.
2. Pada panel **Catat Pembayaran**, isi:
   - tipe pembayaran: DP, cicilan, pelunasan, atau refund;
   - nominal;
   - metode pembayaran;
   - tanggal bayar;
   - bukti pembayaran (opsional).
3. Simpan. Status pembayaran akan diperbarui otomatis:
   - `unpaid` — belum ada pembayaran;
   - `dp_paid` — DP sudah terbayar;
   - `partially_paid` — dibayar sebagian;
   - `paid` — lunas;
   - `overdue` — melewati tenggat pembayaran.

Ketentuan default:

- DP **30%** dari total pesanan.
- Pelunasan dilakukan sebelum barang diambil/dikirim.
- Sistem menolak penyerahan barang jika pesanan belum lunas.
- Nominal pembayaran tidak boleh melebihi sisa tagihan.
- Refund tidak boleh melebihi total pembayaran yang sudah diterima.

## 4. Produksi

### 4.1. Papan Produksi

Menu **Papan Produksi** menampilkan item yang sedang antri, dikerjakan, finishing, atau QC.

Alur status item:

```text
pending → queued → in_progress → finishing → qc → done
```

- `pending` — item belum disetujui/masuk antrian.
- `queued` — item siap dikerjakan.
- `in_progress` — item mulai dikerjakan.
- `finishing` — masuk tahap finishing.
- `qc` — sedang diperiksa kualitas.
- `done` — selesai dan dikonfirmasi Pemilik.

### 4.2. Tugas Pegawai

1. Buka **Papan Produksi**.
2. Cari item yang ditugaskan kepada Anda.
3. Perbarui status sesuai progres nyata.
4. Tambahkan catatan atau foto progres jika diperlukan.

Pegawai hanya dapat memperbarui item yang ditugaskan kepadanya. Pegawai **tidak dapat** menandai item sebagai `done`.

### 4.3. Konfirmasi Pemilik

1. Setelah item berada di status `qc`, Pemilik memeriksa hasil pekerjaan.
2. Pemilik mengubah status item menjadi `done`.
3. Jika seluruh item sudah `done` (atau dibatalkan/dialihkan), status pesanan menjadi `production_done` secara otomatis.
4. Pemilik juga dapat mengonfirmasi `production_done` dari halaman detail pesanan, tetapi hanya jika semua item sudah selesai.

### 4.4. Pemakaian Bahan

Saat item pertama kali dipindahkan ke `in_progress`, sistem:

1. Membaca snapshot kebutuhan bahan dari BOM.
2. Memotong stok bahan sesuai kebutuhan dan toleransi waste.
3. Mencatat mutasi stok keluar bertipe `production_out`.
4. Menyimpan pemakaian aktual pada item pesanan.

Jika stok tidak mencukupi, sistem menolak perubahan status dan menampilkan bahan mana yang kurang.

## 5. Pengambilan / Pengiriman

1. Pastikan pesanan sudah `production_done`.
2. Catat pelunasan sampai status pembayaran menjadi `paid`.
3. Buka detail pesanan dan ubah status menjadi **Diambil/Dikirim**.
4. Isi data pengiriman jika diperlukan:
   - tipe: pickup atau delivery;
   - nama penerima;
   - alamat;
   - catatan;
   - bukti pengiriman (opsional).
5. Setelah disimpan, status pesanan menjadi `delivered`.

Sistem tidak mengizinkan status `delivered` jika pesanan belum lunas.

## 6. Pengalihan Pesanan Bermasalah

Pengalihan digunakan jika:

- pesanan sudah `production_done`;
- pesanan belum lunas;
- masa tenggang pelunasan sudah berakhir;
- pengalihan dilakukan oleh Pemilik.

Langkah:

1. Buka detail pesanan.
2. Pada panel **Pengalihan Pesanan**, pilih item yang dialihkan.
3. Pilih tujuan:
   - **Pesanan berikutnya** — tautkan ke pesanan pelanggan lain yang masih aktif;
   - **Stok produk jadi** — item dimasukkan ke stok produk jadi;
   - **Lainnya** — catat tujuan secara manual.
4. Tandai apakah DP hangus.
5. Isi catatan dan simpan.

Status item menjadi `transferred`. Jika semua item dialihkan, status pesanan menjadi `transferred`. DP yang hangus tetap tercatat pada riwayat pengalihan.

## 7. Inventori Bahan

### 7.1. Stok Masuk

1. Buka **Inventori → Stok Masuk**.
2. Pilih supplier, tanggal pembelian, dan nomor invoice (opsional).
3. Tambahkan satu atau beberapa bahan, qty, dan harga satuan.
4. Simpan. Sistem membuat data pembelian dan mutasi stok masuk.

### 7.2. Stok Keluar

1. Buka **Inventori → Stok Keluar**.
2. Pilih bahan, qty, jenis keluar, dan alasan.
3. Simpan. Sistem menolak jika stok tidak mencukupi.

### 7.3. Stock Opname

1. Buka **Inventori → Stock Opname → Buat Opname**.
2. Masukkan jumlah fisik hasil hitung. Kosongkan bahan yang tidak dihitung.
3. Simpan. Sistem menghitung selisih dan mencatat mutasi penyesuaian.

### 7.4. Penyesuaian Stok Produk Jadi

1. Buka **Produk → detail produk**.
2. Gunakan form penyesuaian stok untuk menambah atau mengurangi stok.
3. Setiap perubahan tercatat di riwayat mutasi produk.

## 8. Katalog dan BOM

### 8.1. Model Produk

Model produk menyimpan:

- kode dan nama;
- kategori;
- tipe harga: per m² atau harga tetap;
- harga default per m²;
- ukuran default;
- jenis kayu, kualitas kayu, dan tingkat kesulitan default;
- estimasi hari pengerjaan;
- foto (opsional).

### 8.2. BOM

1. Buka detail model produk.
2. Tambahkan bahan, satuan, qty, dan persentase waste.
3. BOM dipakai saat produksi dimulai.
4. BOM dapat diubah untuk pesanan berikutnya tanpa mengubah snapshot pesanan lama.

## 9. Laporan

Menu **Laporan** menyediakan:

- **Stok Bahan** — posisi stok saat ini;
- **Mutasi Stok** — riwayat keluar/masuk bahan;
- **Pesanan** — daftar pesanan dan statusnya;
- **Pembayaran** — penerimaan dan refund;
- **Produksi** — progres item dan status produksi.

Laporan dapat difilter berdasarkan tanggal, bahan, atau status, lalu diunduh sebagai **PDF** atau **Excel**.

## 10. Pengaturan

Menu **Pengaturan** hanya dapat diakses Pemilik. Pengaturan mencakup:

- nama, alamat, telepon, dan email workshop;
- persentase DP;
- masa tenggang pelunasan;
- harga cepat per m²;
- catatan invoice.

### 10.1. Log Aktivitas

Pemilik dapat membuka **Log Aktivitas** untuk melihat jejak perubahan data, termasuk:

- pengguna dan waktu kejadian;
- modul dan jenis aksi;
- deskripsi aktivitas;
- nilai sebelum dan sesudah perubahan (jika tersedia);
- alamat IP pengguna.

Log dapat difilter berdasarkan kata kunci, modul, pengguna, dan rentang tanggal. Log ini berguna untuk menelusuri perubahan status, pembayaran, stok, dan data master.

## 11. Perhitungan Harga

Rumus dasar:

```text
harga = luas_m2 × harga_per_m2 × faktor_kesulitan × faktor_jenis_kayu × faktor_kualitas_kayu × qty
```

Contoh sederhana:

- luas = 1,8 m²
- harga per m² = Rp 1.000.000
- faktor kesulitan = 1,10
- faktor jenis kayu = 1,00
- faktor kualitas = 1,00
- qty = 1

```text
1,8 × 1.000.000 × 1,10 × 1,00 × 1,00 × 1 = Rp 1.980.000
```

DP 30% = Rp 594.000.

Nilai faktor dapat diubah melalui master data. Harga akhir dapat disesuaikan dengan alasan override yang tercatat.

## 12. Masalah Umum

### Tidak bisa membuka menu

Periksa peran akun. Menu tertentu hanya tersedia untuk Pemilik atau Admin/Sales. Hubungi Pemilik jika akses perlu diubah.

### Tidak bisa menandai item selesai

Status `done` hanya dapat diatur oleh Pemilik. Pastikan item sudah berada di status `qc`.

### Tidak bisa mengubah status pesanan menjadi Diambil/Dikirim

Pastikan pembayaran sudah lunas. Sistem menerapkan aturan pelunasan sebelum penyerahan.

### Tidak bisa mengalihkan pesanan

Pastikan:

- pesanan sudah `production_done`;
- pesanan belum lunas;
- masa tenggang sudah berakhir;
- Anda masuk sebagai Pemilik.

### Stok tidak mencukupi saat mulai produksi

Catat stok masuk atau lakukan penyesuaian stok terlebih dahulu. Periksa juga BOM dan qty item.

### Ingin mengulang data demo

```bash
php artisan migrate:fresh --seed
```

Perintah ini menghapus seluruh data dan mengisi ulang data demo.

## 13. Struktur Menu

```text
Dashboard
Pesanan
  ├─ Daftar Pesanan
  └─ Pesanan Baru
Papan Produksi
Inventori
  ├─ Mutasi Stok
  ├─ Stok Masuk
  ├─ Stok Keluar
  └─ Stock Opname
Katalog
  ├─ Model Produk
  ├─ Bahan
  └─ Produk Jadi
Master Data
  ├─ Kategori
  ├─ Satuan
  ├─ Jenis Kayu
  ├─ Kualitas Kayu
  ├─ Tingkat Kesulitan
  ├─ Supplier
  └─ Pelanggan
Laporan
Pengguna (Pemilik)
Log Aktivitas (Pemilik)
Pengaturan (Pemilik)
```

## 14. Catatan Implementasi

- Foto produk/Instagram tidak disertakan pada studi kasus, sehingga antarmuka menggunakan warna, ikon, dan placeholder.
- Semua nominal disimpan sebagai angka desimal dan ditampilkan dalam format Rupiah.
- Perubahan status penting dicatat pada riwayat status dan activity log.
- Data yang sudah dipakai transaksi tidak boleh dihapus sembarangan; gunakan status aktif/nonaktif atau arsip sesuai kebijakan workshop.
