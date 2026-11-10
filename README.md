# OptimaBiz — Sistem Manajemen Penjualan, Produksi & Audit Kebocoran Laba (UMKM Alas Kaki Kulit)

**OptimaBiz** adalah aplikasi web terintegrasi (*Sales Intelligence, Production Control & Profit Leakage Guardian*) yang dirancang khusus untuk membantu pelaku UMKM bengkel kerajinan sepatu dan sandal kulit (*leathercraft workshop*) dalam mencatat operasional harian, memantau stok bahan baku serta limbah kulit, menghitung estimasi upah pengrajin, hingga mendeteksi kebocoran margin profit berdasarkan data riil.


---


## Fitur Utama


### 1. Dashboard Berbasis Peran (_Role-Tailored Workspace_)

Satu pintu `/dashboard` dengan tampilan dan hak data yang disesuaikan untuk tiap peran:

- **Super Admin (Pemilik)**: Ringkasan total penjualan (omset), jumlah transaksi, produk terjual terhadap batas kapasitas bulanan, rata-rata omset harian, grafik tren penjualan interaktif (7 hari, per 7 hari, hingga bulanan), top produk terlaris, riwayat transaksi terbaru, serta modal **Buku Catatan Penjualan** (ekspor CSV).
- **Admin Operasional**: Pantauan non-finansial (jumlah nota tercatat, pasang sepatu keluar, model siap jual, stok menipis, grafik transaksi harian, dan 5 transaksi terakhir).
- **Sales / Kasir**: Ringkasan penjualan pribadi, jumlah nota eceran vs partai, rata-rata diskon yang diberikan, grafik penjualan harian pribadi, serta cek cepat stok 5 produk terlaris.
- **Pengrajin (_Craftsman_)**: Jumlah sepatu selesai dibuat, perkiraan upah (gaji pokok / borongan), catatan sisa kulit rusak, progres target produksi bengkel bulanan, dan peringatan bahan baku menipis.


### 2. Manajemen Transaksi & Penjualan (`/sales`)

- Pencatatan transaksi penjualan ritel maupun pesanan partai/grosir beserta harga katalog (`base_price`), harga hasil negosiasi (`deal_price`), dan snapshot harga pokok (`cost_price`).
- Analisis selisih diskon rata-rata dan peringatan visual otomatis jika harga sepakat berada di bawah modal bahan baku.
- Pencarian transaksi dan pagination ringkas ramah perangkat seluler.


### 3. Inventaris, Kategori & Pencatatan Limbah Kulit (`/inventory`, `/categories`)

- Manajemen master katalog produk jadi (sepatu/sandal) serta bahan baku (lembaran kulit dalam satuan _feet_, komponen sol, lem/pendukung) dan pengelompokan kategori (`/categories`).
- Peringatan otomatis saat stok menyentuh batas minimum (`min_stock_alert`).
- Pencatatan mutasi stok masuk produksi (`in`), keluar (`out`), penyesuaian (`adjustment`), serta pencatatan limbah/cacat potong & seset (`waste`) yang langsung menghitung nilai kerugian rupiahnya.


### 4. Manajemen Karyawan, Pengrajin & Upah (`/users` — Khusus Super Admin)

- Pengelolaan akun staf (_Admin_, _Sales_, _Pengrajin_), filter peran, aktivasi/nonaktivasi akun, dan pelacakan performa bulanan.
- Dukungan 3 skema pengupahan: gaji tetap bulanan (`fixed_monthly`), upah borongan per pasang (`piece_rate`), dan kombinasi (`hybrid`), lengkap dengan ekspor data karyawan & gaji ke format **CSV**.

### 5. Profit Leakage & Margin Guardian (`/simulator` — Khusus Super Admin)

- Modul audit kebocoran laba berbasis data transaksi dan limbah riil dengan filter periode dinamis (7 hari, 30 hari, bulan ini, dan 90 hari).
- **Deteksi 3 Sumber Kebocoran Kas**:
    1. _Margin Slippage_ (laba hilang akibat diskon negosiasi berlebih).
    2. Kerugian limbah kulit di meja potong dan seset.
    3. Modal mengendap pada stok barang jadi lambat laku (> 45 hari).
- **Bedah HPP per Produk**: Inspeksi 5 komponen pembentuk HPP riil pada setiap model sepatu/sandal.
- **Rekomendasi Tindakan Konkret**: Daftar langkah perbaikan operasional otomatis lengkap dengan checklist eksekusi dan fitur salin instruksi briefing tim.



## Hak Akses Pengguna

| Peran | Dashboard | Penjualan (`/sales`) | Stok & Limbah (`/inventory`) | Kategori (`/categories`) | Audit Laba (`/simulator`) | Karyawan & Upah (`/users`) |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Super Admin** | Finansial & Eksekutif | Penuh | Penuh | Penuh | Penuh | Penuh |
| **Admin** | Operasional | Penuh | Penuh | Penuh | - | - |
| **Sales** | Performa Sales | Penuh | Lihat Stok | - | - | - |
| **Pengrajin** | Produksi & Upah | - | Catat Produksi & Limbah | - | - | - |

---

## Teknologi yang Digunakan

- **Backend**: PHP 8.3+ · Laravel 13 (`laravel/framework`)
- **Frontend**: Vue 3 (`<script setup>`) · Inertia.js v3 · JavaScript (ES6+)
- **Styling & Build**: Tailwind CSS v4 · Vite v8 (antarmuka _mobile-first_ dengan _bottom navigation bar_ di HP dan grafik sentuh interaktif)
- **Database**: MySQL 8.0 (mendukung SQLite untuk pengujian)
- **Deployment & Testing**: Docker (`php:8.4-apache`) & Docker Compose · PHPUnit 12

---

## Instalasi & Menjalankan Secara Lokal

### Prasyarat

- PHP >= 8.3 & Composer
- Node.js >= 20 & npm
- MySQL 8.0 (atau SQLite untuk pengembangan cepat)

### Langkah Setup

1. **Clone repositori dan masuk ke direktori proyek**

    ```bash
    git clone <url-repositori>
    cd GTNIC
    ```

2. **Install dependensi PHP & JavaScript**

    ```bash
    composer install
    npm install
    ```

3. **Konfigurasi file `.env`**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    Sesuaikan koneksi database pada `.env` (misalnya `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

4. **Jalankan migrasi dan seeder data awal**

    ```bash
    php artisan migrate --seed
    ```

5. **Build aset frontend dan jalankan server**
    ```bash
    npm run build
    php artisan serve
    ```
    Untuk mode pengembangan _hot-reload_, jalankan `npm run dev` secara bersamaan dengan `php artisan serve`.

---

## Menjalankan dengan Docker

Proyek ini menyediakan konfigurasi `Dockerfile` dan `docker-compose.yml` (layanan web di port `8001` dan MySQL 8.0 di port `3307`):

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

Akses aplikasi melalui `http://localhost:8001`.


## Akun Demo (Hasil Seeder)

Setelah menjalankan `php artisan db:seed`, akun berikut siap digunakan untuk menguji seluruh peran (kata sandi semua akun: **`password123`**):


| Peran                 | Email                                                                  |
| :-------------------- | :--------------------------------------------------------------------- |
| **Super Admin**       | `owner@optimabiz.test`                                                 |
| **Admin Operasional** | `admin@optimabiz.test`                                                 |
| **Sales**             | `asep@optimabiz.test`, `rian@optimabiz.test`, `siti@optimabiz.test`    |
| **Pengrajin**         | `bambang@optimabiz.test`, `budi@optimabiz.test`, `hadi@optimabiz.test` |



## Pengujian (_Testing_)

Jalankan seluruh rangkaian uji _Feature_ dan _Unit_ menggunakan PHPUnit:

```bash
php artisan test
```
