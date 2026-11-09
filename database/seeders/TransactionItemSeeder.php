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
        $finishedProducts = Product::where('is_active', true)
            ->where('base_price', '>', 0)
            ->get();

        if ($finishedProducts->isEmpty()) {
            return;
        }

        $transactions = Transaction::all();
        if ($transactions->isEmpty()) {
            return;
        }

        // Hapus item transaksi lama jika ada
        TransactionItem::query()->delete();

        foreach ($transactions as $index => $transaction) {
            $isWholesale = str_contains($transaction->customer_name, 'Toko') ||
                           str_contains($transaction->customer_name, 'Distributor') ||
                           str_contains($transaction->customer_name, 'CV') ||
                           str_contains($transaction->customer_name, 'Boutique') ||
                           str_contains($transaction->customer_name, 'Koperasi');

            $isReseller = str_contains($transaction->customer_name, 'Reseller') ||
                          str_contains($transaction->customer_name, 'Distro');

            // Pilih produk unik
            if ($isWholesale) {
                $itemCount = rand(2, 4);
            } elseif ($isReseller) {
                $itemCount = rand(2, 3);
            } else {
                $itemCount = (rand(1, 100) <= 75) ? 1 : 2;
            }

            $selectedProducts = $finishedProducts->random(min($itemCount, $finishedProducts->count()));

            $totalAmount = 0.0;
            $totalDiscount = 0.0;
            $netAmount = 0.0;

            foreach ($selectedProducts as $product) {
                $basePrice = (float) $product->base_price;
                $costPrice = (float) $product->cost_price;

                if ($isWholesale) {
                    $qty = rand(6, 16);
                    // Diskon partai: 8% - 15%, kadang 18%
                    $discountPct = (rand(1, 100) <= 25) ? 18 : rand(8, 14);
                } elseif ($isReseller) {
                    $qty = rand(3, 8);
                    // Diskon reseller: 5% - 12%
                    $discountPct = rand(5, 12);
                } else {
                    $qty = rand(1, 2);
                    // Diskon retail: 0% (harga pas), kadang 5%
                    $discountPct = (rand(1, 100) <= 30) ? 5 : 0;
                }

                // Kalkulasi harga deal
                $discountPerUnit = round(($basePrice * ($discountPct / 100)) / 1000) * 1000;
                // Pastikan deal_price tidak di bawah cost_price kecuali skenario khusus
                $dealPrice = max($costPrice + 5000, $basePrice - $discountPerUnit);
                $effectiveDiscount = $basePrice - $dealPrice;

                $itemSubtotal = $dealPrice * $qty;
                $itemGross = $basePrice * $qty;
                $itemDiscountTotal = $effectiveDiscount * $qty;

                $notes = null;
                if ($effectiveDiscount > 0) {
                    $notes = $isWholesale
                        ? 'Diskon negosiasi partai grosir (-Rp ' . number_format($effectiveDiscount, 0, ',', '.') . '/pasang)'
                        : ($isReseller ? 'Harga paket reseller kemitraan' : 'Potongan promo showroom');
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
