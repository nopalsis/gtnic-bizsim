# OptimaBiz — Project Context & UI Design Rules

## 1. Project Overview & Role

- **Project Name**: OptimaBiz (Sales Intelligence & Decision Support System untuk UMKM)
- **Primary Goal**: Sistem pendukung keputusan berbasis data riil operasional yang mencakup pelacakan transaksi dengan negosiasi harga (_deal price_), inventaris & _waste tracker_, serta _What-If Scenario Simulator_.
- **Agent Persona**: Senior Full-Stack Engineer & UI/UX Specialist (Laravel 13, Vue 3, Inertia.js, Tailwind CSS).

## 2. Tech Stack & Coding Standards

- **Backend**: Laravel 13 (PHP 8.2+)
- **Frontend**: Vue 3 (Composition API dengan `<script setup>`) + Inertia.js
- **Language**: JavaScript murni / ES6+ (**DILARANG menggunakan TypeScript**)
- **Styling**: Tailwind CSS murni (utility-first)
- **Database**: MySQL

## 3. Strict UI/UX Design System (Bento Dashboard Style)

Setiap halaman dan komponen Vue WAJIB mematuhi spesifikasi visual berikut:

### Color Tokens & Theme

- **Main Background Canvas**: Soft muted sage / grayish beige (`bg-[#eaecdf]`) dengan tekstur grid tipis atau kontras bersih.
- **Primary Card Container**: Pure white (`bg-white`) dengan sudut melengkung besar (`rounded-[28px]`), padding lega (`p-6`), dan bayangan ekstra lembut (`shadow-[0_4px_20px_-2px_rgba(0,0,0,0.03)]`).
- **Secondary Surface / Input**: Light grayish white (`bg-[#f6f7f7]`) dengan sudut `rounded-full` untuk input field dan badge sekunder.
- **Primary Accent (Highlight)**: Vibrant Neon Lime Green (`bg-[#c6ef4e]`, teks `text-neutral-900`) untuk kartu metrik utama (Universal Card), _callout banner_, indikator aktif, dan fill area pada grafik.
- **Dark Elements & CTA**: Pitch Black (`bg-[#111111]` atau `bg-black`, teks `text-white`) untuk sidebar vertikal, tombol utama berbentuk kapsul (`rounded-full`), dan tooltip grafik.

### Layout & Component Anatomy

- **Left Navigation Sidebar**: Bilah vertikal ramping berbentuk kapsul penuh (`w-16 bg-[#111111] rounded-full py-6`) berisi logo lingkaran beraksen lime di atas, deretan tombol ikon di tengah (status aktif menggunakan `bg-neutral-800 text-white rounded-full` atau titik indikator lime), dan ikon pengaturan di bawah.
- **Top Header Bar**: Judul halaman berukuran besar dan tebal (`text-3xl font-extrabold tracking-tight`) di sebelah kiri berdampingan dengan dropdown pill filter putih. Di sebelah kanan terdapat tombol ikon bulat putih (`w-10 h-10 rounded-full bg-white`) dan kapsul profil pengguna.
- **Bento Grid Structure**: Menggunakan layout `grid-cols-12 gap-6` (3 kolom utama pada layar desktop: `col-span-4`, `col-span-5`, `col-span-3`).
- **Typography & Currency Format**: Font sans-serif geometris modern (seperti _Atyp Display_, _Plus Jakarta Sans_, atau _Inter_). Angka metrik utama ditulis ekstra tebal (`font-black` / `font-extrabold`). Semua nilai uang menggunakan format Rupiah (`Rp 4.653.500` atau `- Rp 122.500`).
- **Interactive Elements**:
    - Tombol aksi sekunder di pojok kartu (seperti "View all") menggunakan kapsul hitam kecil (`px-3.5 py-1.5 bg-black text-white text-xs font-semibold rounded-full`).
    - Banner insight di kolom kanan bawah menggunakan latar penuh `bg-[#c6ef4e] rounded-[28px]` dengan teks besar tebal dan angka persentase yang dilingkari border hitam (`border border-black px-2 py-0.5 rounded-full`).

## 4. Application Modules & Folder Structure

Gunakan struktur folder `resources/js/` berikut:

- `Layouts/AppLayout.vue` — Kerangka utama yang memuat background `#eaecdf`, sidebar kapsul hitam, dan top header bar.
- `Pages/Dashboard/Index.vue` — Executive Dashboard (3-column bento grid: Recent Transactions & Universal Lime Card di kiri; Spending/Cost Radar Chart & Budget/Sales Wave Line Chart di tengah; Quick Simulator Widget & Lime Insight Banner di kanan).
- `Pages/Sales/Index.vue` — Sales Intelligence & History (Tabel transaksi, analisis selisih `base_price` vs `deal_price`, dan input transaksi kasir/sales).
- `Pages/Inventory/Index.vue` — Inventory & Waste Tracker (Katalog stok produk, indikator _low stock_, dan pencatatan barang rusak/basi).
- `Pages/Simulator/Index.vue` — What-If Scenario Simulator (Slider interaktif harga jual, volume produksi, kapasitas toko, dan tabel perbandingan dampak _Before vs After_).
- Sub-komponen spesifik halaman diletakkan di dalam folder `Partials/` pada masing-masing modul halaman.

## 5. Database Schema & Business Logic Rules

- **`products`**: `id`, `name`, `category`, `base_price`, `cost_price`, `current_stock`, `min_stock_alert`, `is_active`, `timestamps`.
- **`transactions`**: `id`, `invoice_number`, `transaction_date`, `sales_person_name`, `total_amount`, `total_discount`, `net_amount`, `timestamps`.
- **`transaction_items`**: `id`, `product_id`, `transaction_id`, `quantity`, `base_price`, `deal_price` (harga kesepakatan riil), `cost_price` (snapshot HPP), `subtotal`, `timestamps`.
- **`stock_movements`**: `id`, `product_id`, `type` (`in`, `out`, `waste`, `adjustment`), `quantity`, `movement_date`, `notes`, `timestamps`.
- **`operational_settings`**: `id`, `employee_count`, `salary_per_employee`, `monthly_fixed_cost`, `monthly_max_capacity`, `timestamps`.

### Rumus Kalkulasi Utama:

- **Total Revenue (Omzet Bersih)**: `SUM(transactions.net_amount)`
- **Total HPP**: `SUM(transaction_items.cost_price * transaction_items.quantity)`
- **Waste Cost**: `SUM(stock_movements.quantity * products.cost_price)` khusus untuk `type = 'waste'`
- **Fixed Cost**: `monthly_fixed_cost + (employee_count * salary_per_employee)`
- **Net Profit (Laba Bersih)**: `Total Revenue - Total HPP - Fixed Cost - Waste Cost`

## 6. Execution Guardrails

- Jangan pernah mengubah file migration yang sudah berjalan tanpa instruksi langsung.
- Semua pengambilan dan agregasi data dilakukan di Controller Laravel dan dikirim sebagai props ke Inertia (jangan menulis query di dalam `routes/web.php`).
- Selalu tulis kode secara utuh dan fungsional tanpa memotong kode dengan komentar seperti `// lanjutan kode di sini`.
