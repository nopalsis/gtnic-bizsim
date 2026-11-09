<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        TransactionItem::truncate();
        Transaction::truncate();
        Schema::enableForeignKeyConstraints();

        if (Product::count() === 0) {
            $this->call(ProductSeeder::class);
        }

        // Tiga sales person resmi workshop
        $salesPersons = [
            'retail' => 'Kang Asep (Sales Retail)',
            'wholesale' => 'Rian (Sales Grosir & Partai)',
            'reseller' => 'Siti (Sales Reseller & Toko)',
        ];

        // Daftar pelanggan grosir, reseller, dan retail eceran
        $wholesaleCustomers = [
            ['name' => 'Toko Langkah Makmur Jakarta', 'notes' => 'Pesanan partai grosir display etalase toko'],
            ['name' => 'Distributor Kulit Cibaduyut', 'notes' => 'Pengadaan rutin partai besar sepatu & sandal kulit'],
            ['name' => 'Toko Sepatu Berkah Bandung', 'notes' => 'Pesanan partai reseller sandal pria & sandal selop'],
            ['name' => 'CV Ksatria Footwear Malang', 'notes' => 'Order grosir sepatu pantofel formal & loafer'],
            ['name' => 'Toko Sepatu Mandiri Surabaya', 'notes' => 'Restock partai besar sandal kasual & formal'],
            ['name' => 'Distro Leather Heritage Jogja', 'notes' => 'Pesanan kustom sandal tali & loafer kasual'],
            ['name' => 'Boutique Footwear Bali', 'notes' => 'Restock sandal kulit wanita & flat shoes turis'],
            ['name' => 'Koperasi Pegawai Mitra Usaha', 'notes' => 'Pengadaan sepatu pantofel seragam dinas kantor'],
        ];

        $resellerCustomers = [
            ['name' => 'Reseller Toko Siti Online', 'notes' => 'Pesanan kemitraan reseller paket sandal kulit'],
            ['name' => 'Distro Kulit Casual Tasik', 'notes' => 'Pesanan reseller model sepatu slop santai'],
            ['name' => 'Toko Sepatu Barokah Cirebon', 'notes' => 'Paket reseller sandal jepit & selop kulit'],
            ['name' => 'Reseller Sandal Sentosa Solo', 'notes' => 'Restock bulanan sandal kulit pria & wanita'],
        ];

        $retailCustomers = [
            ['name' => 'Bpk. Doni Pratama', 'notes' => 'Beli sepatu pantofel formal untuk kerja'],
            ['name' => 'Ibu Ratna Dewi', 'notes' => 'Pembelian sandal kulit wanita di showroom'],
            ['name' => 'Bpk. Kurniawan', 'notes' => 'Beli sandal selop kulit pria'],
            ['name' => 'dr. Farhan Alamsyah', 'notes' => 'Beli sepatu loafer kasual insole empuk'],
            ['name' => 'Ibu Maya Anggraeni', 'notes' => 'Beli sepatu flat wanita kulit'],
            ['name' => 'Bpk. Rizal Fahmi', 'notes' => 'Beli sandal tali kasual & semir balsam'],
            ['name' => 'Bpk. Hendra Wijaya', 'notes' => 'Beli sandal kulit jepit pria'],
            ['name' => 'Ibu Diana Permata', 'notes' => 'Beli sandal kulit wanita kasual'],
            ['name' => 'Walk-in Customer Showroom', 'notes' => 'Pembelian langsung pelanggan showroom workshop'],
        ];

        $paymentPool = ['Transfer Bank (BCA)', 'Transfer Bank (Mandiri)', 'QRIS', 'Tunai', 'Tempo / Termin 14 Hari'];

        // Hapus transaksi lama agar seeder baru ter-generate dengan data 3 bulan yang rapi
        Transaction::query()->delete();

        // Buat ~85 transaksi tersebar merata selama 90 hari terakhir (~3 bulan)
        $totalTransactions = 85;

        for ($i = 1; $i <= $totalTransactions; $i++) {
            $invoiceNumber = sprintf('INV-OBZ-2026-%04d', $i);

            // Distribusi hari: dari 89 hari lalu sampai hari ini (0 hari lalu)
            // Sebagian hari ada 1 transaksi, hari sibuk/akhir pekan ada 2 transaksi
            $daysAgo = (int) round((($totalTransactions - $i) / $totalTransactions) * 89);
            $hour = rand(9, 19);
            $minute = rand(0, 59);
            $second = rand(0, 59);
            $transactionDate = now()->subDays($daysAgo)->setHour($hour)->setMinute($minute)->setSecond($second);

            // Tentukan tipe transaksi: 40% grosir, 25% reseller, 35% retail
            $typeRoll = rand(1, 100);
            if ($typeRoll <= 40) {
                // Wholesale
                $cust = $wholesaleCustomers[array_rand($wholesaleCustomers)];
                $salesPerson = $salesPersons['wholesale'];
                $payment = (rand(1, 100) <= 60) ? 'Transfer Bank (BCA)' : 'Tempo / Termin 14 Hari';
            } elseif ($typeRoll <= 65) {
                // Reseller
                $cust = $resellerCustomers[array_rand($resellerCustomers)];
                $salesPerson = $salesPersons['reseller'];
                $payment = (rand(1, 100) <= 50) ? 'Transfer Bank (BCA)' : 'Transfer Bank (Mandiri)';
            } else {
                // Retail
                $cust = $retailCustomers[array_rand($retailCustomers)];
                $salesPerson = $salesPersons['retail'];
                $payment = (rand(1, 100) <= 60) ? 'QRIS' : 'Tunai';
            }

            Transaction::create([
                'invoice_number' => $invoiceNumber,
                'transaction_date' => $transactionDate,
                'customer_name' => $cust['name'],
                'sales_person_name' => $salesPerson,
                'payment_method' => $payment,
                'total_amount' => 0.00,
                'total_discount' => 0.00,
                'net_amount' => 0.00,
                'notes' => $cust['notes'],
            ]);
        }
    }
}
