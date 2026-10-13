<?php

namespace Tests\Feature;

use App\Models\OperationalSetting;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_all_tables(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@gtnic.test',
        ]);
        $this->assertGreaterThanOrEqual(5, User::count());

        $this->assertGreaterThanOrEqual(1, OperationalSetting::count());
        $this->assertGreaterThanOrEqual(10, Product::count());
        $this->assertGreaterThanOrEqual(10, StockMovement::count());
        $this->assertGreaterThanOrEqual(10, Transaction::count());
        $this->assertGreaterThanOrEqual(10, TransactionItem::count());

        $transaction = Transaction::with('transactionItems')->first();
        $this->assertNotNull($transaction);
        $this->assertNotEmpty($transaction->transactionItems);
        $this->assertGreaterThan(0, (float) $transaction->net_amount);
    }

    public function test_model_factories_can_create_valid_records(): void
    {
        $product = Product::factory()->create();
        $this->assertDatabaseHas('products', ['id' => $product->id]);

        $setting = OperationalSetting::factory()->create();
        $this->assertDatabaseHas('operational_settings', ['id' => $setting->id]);

        $movement = StockMovement::factory()->create();
        $this->assertDatabaseHas('stock_movements', ['id' => $movement->id]);

        $transaction = Transaction::factory()->create();
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);

        $item = TransactionItem::factory()->create();
        $this->assertDatabaseHas('transaction_items', ['id' => $item->id]);
    }
}
