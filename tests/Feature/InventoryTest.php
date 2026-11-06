<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.local',
            'role' => 'admin',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    public function test_inventory_page_renders_with_inertia_props(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('inventory.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Inventory/Index')
            ->has('products')
            ->has('recentMovements')
            ->has('recentWasteLogs')
            ->has('stats')
            ->has('advisory')
        );
    }

    public function test_can_record_waste_and_reduce_stock(): void
    {
        $admin = $this->createAdmin();

        $product = Product::create([
            'sku' => 'TEST-001',
            'name' => 'Kulit Test',
            'category' => 'Bahan Baku Kulit',
            'unit' => 'sq ft',
            'cost_price' => 38000,
            'base_price' => 45000,
            'current_stock' => 20,
            'min_stock_alert' => 5,
            'is_active' => true,
        ]);
        $initialStock = $product->current_stock;

        $response = $this->actingAs($admin)->post(route('inventory.waste'), [
            'product_id' => $product->id,
            'quantity' => 2,
            'movement_date' => now()->toDateString(),
            'notes' => 'Test reject kulit sobek',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $this->assertEquals($initialStock - 2, $product->fresh()->current_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'waste',
            'quantity' => 2,
        ]);
    }

    public function test_can_record_stock_mutation(): void
    {
        $admin = $this->createAdmin();

        $product = Product::create([
            'sku' => 'TEST-002',
            'name' => 'Sol Test',
            'category' => 'Komponen Sol',
            'unit' => 'pasang',
            'cost_price' => 100000,
            'base_price' => 150000,
            'current_stock' => 10,
            'min_stock_alert' => 3,
            'is_active' => true,
        ]);
        $initialStock = $product->current_stock;

        $response = $this->actingAs($admin)->post(route('inventory.mutation'), [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
            'notes' => 'Restock pengiriman bahan',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $this->assertEquals($initialStock + 5, $product->fresh()->current_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
        ]);
    }
}
