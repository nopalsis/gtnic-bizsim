<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Seeder;

class TransactionItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Product::count() === 0) {
            $this->call(ProductSeeder::class);
        }

        $products = Product::where('is_active', true)->get();
        if ($products->isEmpty()) {
            return;
        }

        $transactions = Transaction::all();
        if ($transactions->isEmpty()) {
            return;
        }

        foreach ($transactions as $index => $transaction) {
            if ($transaction->transactionItems()->exists()) {
                continue;
            }

            // Apakah pesanan partai/wholesale atau eceran/retail
            $isWholesale = str_contains($transaction->customer_name, 'Toko') ||
                           str_contains($transaction->customer_name, 'Distributor') ||
                           str_contains($transaction->customer_name, 'CV') ||
                           str_contains($transaction->customer_name, 'Boutique');

            // Ambil 1 sampai 3 jenis produk berbeda per transaksi
            $itemCount = $isWholesale ? rand(2, 4) : rand(1, 2);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            $totalAmount = 0.0;
            $totalDiscount = 0.0;
            $netAmount = 0.0;

            foreach ($selectedProducts as $product) {
                // Kuantitas: wholesale 3-8 pasang, retail 1-2 pasang
                $qty = $isWholesale ? rand(3, 8) : rand(1, 2);
                $basePrice = (float) $product->base_price;
                $costPrice = (float) $product->cost_price;

                // Negosiasi Harga (Deal Price):
                // Jika wholesale: diskon berkisar 10% - 18% dari base price
                // Jika retail: diskon berkisar 0% - 8% (kadang tawar, kadang pas)
                $discountPercent = $isWholesale ? rand(10, 18) : (rand(1, 100) <= 40 ? rand(5, 8) : 0);
                $discountPerUnit = round(($basePrice * $discountPercent / 100) / 1000) * 1000;
                $dealPrice = max($costPrice * 1.15, $basePrice - $discountPerUnit); // Pastikan tetap di atas HPP

                $effectiveDiscountPerUnit = $basePrice - $dealPrice;
                $itemSubtotal = $dealPrice * $qty;
                $itemGross = $basePrice * $qty;
                $itemDiscountTotal = $effectiveDiscountPerUnit * $qty;

                $notes = null;
                if ($effectiveDiscountPerUnit > 0) {
                    $notes = $isWholesale
                        ? 'Diskon negosiasi partai grosir (potongan Rp '.number_format($effectiveDiscountPerUnit, 0, ',', '.').'/'.$product->unit.')'
                        : 'Diskon promo showroom / tawar langsung';
                }

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'base_price' => $basePrice,
                    'deal_price' => $dealPrice,
                    'cost_price' => $costPrice,
                    'subtotal' => $itemSubtotal,
                    'notes' => $notes,
                ]);

                $totalAmount += $itemGross;
                $totalDiscount += $itemDiscountTotal;
                $netAmount += $itemSubtotal;
            }

            $transaction->update([
                'total_amount' => $totalAmount,
                'total_discount' => $totalDiscount,
                'net_amount' => $netAmount,
            ]);
        }
    }
}
