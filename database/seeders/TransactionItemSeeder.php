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

        foreach ($transactions as $transaction) {
            if ($transaction->transactionItems()->exists()) {
                continue;
            }

            $itemCount = rand(1, min(4, $products->count()));
            $selectedProducts = $products->random($itemCount);

            $totalAmount = 0.0;
            $totalDiscount = 0.0;
            $netAmount = 0.0;

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 3);
                $basePrice = (float) $product->base_price;
                $costPrice = (float) $product->cost_price;

                $hasDiscount = (rand(1, 100) <= 25);
                $discountPerUnit = $hasDiscount ? round(($basePrice * rand(5, 10) / 100) / 500) * 500 : 0.0;
                $dealPrice = $basePrice - $discountPerUnit;

                $itemSubtotal = $dealPrice * $qty;
                $itemGross = $basePrice * $qty;
                $itemDiscountTotal = $discountPerUnit * $qty;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'base_price' => $basePrice,
                    'deal_price' => $dealPrice,
                    'cost_price' => $costPrice,
                    'subtotal' => $itemSubtotal,
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
