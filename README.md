# Kaya Kayu Workshop — Sistem Manajemen & Inventori

Sistem web untuk membantu operasional **Kaya Kayu Workshop**: pencatatan pesanan dari WhatsApp/walk-in, perhitungan harga otomatis, antrian produksi, pembayaran DP dan pelunasan, stok bahan baku, stok produk jadi, serta laporan.

Project ini disusun sebagai project kerja praktik (KP) dengan studi kasus workshop mebel Kaya Kayu.

Dokumentasi pendukung: [ERD & Kamus Data](docs/ERD.md) · [Panduan Penggunaan](docs/PANDUAN.md)

## Fitur Utama

- **Pesanan multi-item** dalam satu nomor pesanan dengan satu tenggat kesepakatan.
- **Harga otomatis** per m² dengan faktor jenis kayu, kualitas kayu, tingkat kesulitan, dan jumlah.
- **Approval Pemilik** untuk pesanan yang dibuat Admin/Sales.
- **DP 30%** dan pencatatan pembayaran cicilan/pelunasan/refund.
- **Papan produksi** per item: antrian, dikerjakan, finishing, QC, selesai.
- **Konfirmasi selesai produksi hanya oleh Pemilik**.
- **Pemotongan stok bahan otomatis** saat produksi dimulai berdasarkan BOM.
- **Stok bahan baku** dengan ledger mutasi, stok masuk, stok keluar, dan stock opname.
- **Stok produk jadi** dengan penyesuaian stok dan riwayat mutasi.
- **Katalog produk** dan **Bill of Materials (BOM)** per model.
- **Pengalihan pesanan** selesai tetapi belum lunas setelah masa tenggang: menjadi stok jadi atau ditautkan ke pesanan berikutnya, dengan DP hangus.
- **Invoice PDF**, pesan WhatsApp, laporan PDF/Excel, dan activity log.
- **Hak akses berlapis** untuk Pemilik, Admin/Sales, dan Pegawai.

## Peran Pengguna

| Peran | Hak akses utama |
|---|---|
| **Pemilik** | Semua fitur, approval pesanan, konfirmasi selesai produksi, pengalihan pesanan, pengguna, pengaturan |
| **Admin / Sales** | Pesanan, pembayaran, katalog, stok, laporan, produksi (tanpa konfirmasi selesai) |
| **Pegawai** | Dashboard, papan produksi, pembaruan progres item yang ditugaskan |

## Teknologi

- Laravel 12
- PHP 8.2+
- MySQL / MariaDB
- Blade + Bootstrap 5 (CDN)
- Bootstrap Icons
- Chart.js
- `barryvdh/laravel-dompdf` untuk PDF
- `phpoffice/phpspreadsheet` untuk Excel

## Kebutuhan Server

- PHP 8.2 atau lebih baru
- Composer
- MySQL/MariaDB
- Ekstensi PHP: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`
- Laragon/XAMPP direkomendasikan untuk Windows

## Instalasi

```bash
cd kaya-kayu
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan `.env`:

```env
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kaya_kayu
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `kaya_kayu`, lalu jalankan:

```sql
CREATE DATABASE kaya_kayu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Buka `http://127.0.0.1:8000`.

> Jika memakai Laragon, project dapat diletakkan di folder `www` dan diakses melalui virtual host Laragon. Perintah `php artisan serve` tetap bisa dipakai untuk pengembangan.

## Akun Demo

Semua akun memakai password: **`password`**

| Username | Peran | Nama |
|---|---|---|
| `owner` | Pemilik | Pemilik Kaya Kayu |
| `admin` | Admin / Sales | Admin Sales |
| `pegawai` | Pegawai | Pegawai Produksi |
| `finishing` | Pegawai | Pegawai Finishing |

Data demo mencakup bahan, BOM, produk jadi, supplier, pelanggan, pesanan berjalan, pesanan menunggu approval, pesanan selesai belum lunas, dan pesanan lunas/terkirim.

## Aturan Bisnis Singkat

- Harga item:

  `luas m² × harga per m² × faktor kesulitan × faktor jenis kayu × faktor kualitas kayu × qty`

- Harga cepat default: **Rp 1.000.000/m²** (dapat diubah di Pengaturan).
- DP default: **30%**.
- Pelunasan dilakukan sebelum barang diambil/dikirim.
- Masa tenggang pelunasan default: **3 hari** setelah tenggat.
- Pesanan selesai produksi dan belum lunas setelah masa tenggang dapat dialihkan; DP hangus.
- Status akhir produksi (`production_done`) hanya dapat dikonfirmasi Pemilik.
- Pesanan yang dibuat Admin/Sales berstatus `pending_approval` sampai disetujui Pemilik.

## Struktur Penting

```text
app/
  Http/Controllers/     # Pesanan, produksi, inventori, laporan, master data
  Models/               # Model Eloquent
  Services/             # OrderService, StockService, ActivityLogger, NumberGenerator
  Http/Middleware/      # EnsureRole
database/
  migrations/           # Skema database
  seeders/              # UserSeeder, MasterSeeder, DemoSeeder
resources/views/
  dashboard.blade.php
  orders/               # Form, detail, invoice
  production/           # Papan produksi
  inventory/            # Stok masuk/keluar/opname
  products/ materials/ product-models/
  reports/              # Laporan + PDF
  activity-logs/        # Audit trail khusus Pemilik
  layouts/              # Layout utama dan sidebar
docs/
  ERD.md                # Entity Relationship Diagram
  PANDUAN.md            # Panduan penggunaan per peran
```

## Pengujian yang Sudah Dilakukan

- Login dan pembatasan akses per peran.
- Pembuatan pesanan multi-item dan perhitungan harga.
- Approval Pemilik dan validasi status.
- Pencatatan DP, pelunasan, dan validasi nominal.
- Perpindahan status produksi dan pembatasan konfirmasi selesai oleh Pemilik.
- Stok masuk, stok keluar, stock opname, dan penyesuaian stok produk.
- CRUD master data, pengguna, bahan, model produk, dan produk jadi.
- Upload foto bahan, model produk, dan produk jadi.
- BOM tambah/hapus.
- Invoice PDF, laporan PDF, dan laporan Excel.
- Tampilan responsif dan sidebar mobile.

## Catatan

- Foto produk/Instagram tidak disertakan dalam studi kasus, sehingga tampilan menggunakan ilustrasi warna dan ikon, bukan foto asli.
- Data demo dapat direset kapan saja dengan `php artisan migrate:fresh --seed`.
- Sebelum digunakan untuk data nyata, ubah password akun demo dan isi identitas workshop di menu **Pengaturan**.
