<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Product::count() === 0) {
            $this->call(ProductSeeder::class);
        }

        $salesPersons = [
            'Kang Asep (Sales Pengrajin)',
            'Pak Bambang (Workshop Head)',
            'Rian (Wholesale & Store)',
            'Siti (Admin Toko & Reseller)',
        ];

        $customers = [
            ['name' => 'Toko Langkah Makmur Jakarta', 'payment' => 'Transfer Bank (BCA)', 'notes' => 'Pesanan grosir display toko cabang Kelapa Gading'],
            ['name' => 'Distributor Kulit Sentra Cibaduyut', 'payment' => 'Tempo / Termin 14 Hari', 'notes' => 'Pengadaan rutin partai besar sepatu formal'],
            ['name' => 'Boutique Leather Goods Bali', 'payment' => 'Transfer Bank (Mandiri)', 'notes' => 'Restock sandal kulit veg-tan & mules wanita'],
            ['name' => 'Toko Sepatu Berkah Bandung', 'payment' => 'Transfer Bank (BCA)', 'notes' => 'Pesanan reseller sandal pria & perawatan kulit'],
            ['name' => 'Pak Hendra (Pesanan Custom)', 'payment' => 'Transfer Bank (BCA)', 'notes' => 'Custom Oxford ukuran 45 tapak lebar'],
            ['name' => 'Ibu Ratna (Reseller Tas & Sepatu)', 'payment' => 'QRIS', 'notes' => 'Pembelian paket Mary Jane & dompet kulit'],
            ['name' => 'Walk-in Customer Showroom', 'payment' => 'QRIS', 'notes' => 'Pembelian eceran langsung di workshop'],
            ['name' => 'dr. Farhan (Order Custom)', 'payment' => 'Transfer Bank (BCA)', 'notes' => 'Chelsea boots custom insole empuk'],
            ['name' => 'Showroom Bazar Kerajinan UMKM', 'payment' => 'Tunai', 'notes' => 'Penjualan pameran produk kerajinan kulit lokal'],
            ['name' => 'CV Ksatria Footwear Malang', 'payment' => 'Transfer Bank (BCA)', 'notes' => 'Order partai sandal casual buckle'],
        ];

        $paymentPool = ['Transfer Bank (BCA)', 'Transfer Bank (Mandiri)', 'QRIS', 'Tunai', 'Tempo / Termin 14 Hari'];

        for ($i = 1; $i <= 25; $i++) {
            $invoiceNumber = sprintf('INV-OBZ-202610-%04d', $i);
            // Rentang 25 hari ke belakang
            $daysAgo = (int) floor((25 - $i) * 1.1);
            $transactionDate = now()->subDays($daysAgo)->setHour(rand(9, 19))->setMinute(rand(0, 59))->setSecond(rand(0, 59));

            $customerData = $customers[($i - 1) % count($customers)];
            $salesPerson = $salesPersons[($i - 1) % count($salesPersons)];

            Transaction::firstOrCreate(
                ['invoice_number' => $invoiceNumber],
                [
                    'transaction_date' => $transactionDate,
                    'customer_name' => $customerData['name'],
                    'sales_person_name' => $salesPerson,
                    'payment_method' => $customerData['payment'] ?? $paymentPool[array_rand($paymentPool)],
                    'total_amount' => 0.00,
                    'total_discount' => 0.00,
                    'net_amount' => 0.00,
                    'notes' => $customerData['notes'],
                ]
            );
        }

        $this->call(TransactionItemSeeder::class);
    }
}
