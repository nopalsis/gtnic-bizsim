<?php

namespace Tests\Unit;

use App\Models\OperationalSetting;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_relationships_and_casts(): void
    {
        $product = Product::create([
            'name' => 'Signature Blend Coffee',
            'category' => 'Beverage',
            'base_price' => 25000.00,
            'cost_price' => 12000.00,
            'current_stock' => 50,
            'min_stock_alert' => 10,
            'is_active' => true,
        ]);

        $this->assertEquals('25000.00', $product->base_price);
        $this->assertEquals('12000.00', $product->cost_price);
        $this->assertSame(50, $product->current_stock);
        $this->assertTrue($product->is_active);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 50,
            'movement_date' => now()->toDateString(),
            'notes' => 'Stok awal bahan baku',
        ]);

        $this->assertTrue($product->stockMovements->contains($movement));
        $this->assertEquals($product->id, $movement->product->id);

        $transaction = Transaction::create([
            'invoice_number' => 'INV-2026-0001',
            'transaction_date' => now(),
            'sales_person_name' => 'Budi Santoso',
            'total_amount' => 45000.00,
            'total_discount' => 5000.00,
            'net_amount' => 40000.00,
        ]);

        $this->assertEquals('45000.00', $transaction->total_amount);
        $this->assertEquals('40000.00', $transaction->net_amount);

        $item = TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'base_price' => 25000.00,
            'deal_price' => 22500.00,
            'cost_price' => 12000.00,
            'subtotal' => 45000.00,
        ]);

        $this->assertTrue($transaction->transactionItems->contains($item));
        $this->assertTrue($product->transactionItems->contains($item));
        $this->assertEquals($transaction->id, $item->transaction->id);
        $this->assertEquals($product->id, $item->product->id);

        $setting = OperationalSetting::create([
            'employee_count' => 3,
            'salary_per_employee' => 2750000.00,
            'monthly_fixed_cost' => 3500000.00,
            'monthly_max_capacity' => 5000,
        ]);

        $this->assertSame(3, $setting->employee_count);
        $this->assertEquals('2750000.00', $setting->salary_per_employee);
        $this->assertSame(5000, $setting->monthly_max_capacity);

        // Test cascade on delete
        $product->delete();
        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->id]);
        $this->assertDatabaseMissing('transaction_items', ['id' => $item->id]);
    }
}
