<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(string $role = 'admin'): User
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin_category@test.local',
            'role' => $role,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    protected function createCraftsman(): User
    {
        return User::create([
            'name' => 'Tukang Sepatu',
            'email' => 'tukang@test.local',
            'role' => 'craftsman',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_categories_page(): void
    {
        $response = $this->get(route('categories.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_craftsman_cannot_access_categories_crud(): void
    {
        $craftsman = $this->createCraftsman();

        $response = $this->actingAs($craftsman)->get(route('categories.index'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_view_categories_page_with_inertia_props(): void
    {
        $admin = $this->createAdmin();

        Category::create([
            'name' => 'Sepatu Formal Pria',
            'slug' => 'sepatu-formal-pria',
            'type' => 'finished',
            'prefix_sku' => 'FG-SHO',
            'description' => 'Sepatu pesta dan kantor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Categories/Index')
            ->has('categories')
            ->has('counts')
            ->has('filters')
        );
    }

    public function test_can_create_new_category(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Sepatu Boots Tactical Kulit',
            'type' => 'finished',
            'prefix_sku' => 'FG-BOT',
            'description' => 'Boots lapangan kulit sapi tahan air',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Sepatu Boots Tactical Kulit',
            'slug' => 'sepatu-boots-tactical-kulit',
            'prefix_sku' => 'FG-BOT',
            'type' => 'finished',
        ]);
    }

    public function test_can_update_category_and_sync_existing_products(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'name' => 'Kulit Kambing Suede',
            'slug' => 'kulit-kambing-suede',
            'type' => 'raw',
            'prefix_sku' => 'RAW-SUE',
            'description' => 'Bahan suede halus',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'RAW-SUE-001',
            'name' => 'Suede Coklat Tua',
            'category' => 'Kulit Kambing Suede',
            'unit' => 'sq ft',
            'cost_price' => 30000,
            'base_price' => 35000,
            'current_stock' => 10,
            'min_stock_alert' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('categories.update', $category), [
            'name' => 'Kulit Suede Premium',
            'type' => 'raw',
            'prefix_sku' => 'RAW-SUE',
            'description' => 'Bahan suede halus kualitas impor',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Kulit Suede Premium',
        ]);
        $this->assertEquals('Kulit Suede Premium', $product->fresh()->category);
    }

    public function test_can_toggle_category_status(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'name' => 'Kategori Uji Status',
            'slug' => 'kategori-uji-status',
            'type' => 'finished',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('categories.toggle-status', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertFalse((bool) $category->fresh()->is_active);
    }

    public function test_cannot_delete_category_when_products_exist(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'name' => 'Sepatu Olahraga Kulit',
            'slug' => 'sepatu-olahraga-kulit',
            'type' => 'finished',
            'is_active' => true,
        ]);

        Product::create([
            'sku' => 'FG-SPO-001',
            'name' => 'Sneakers Kulit Putih',
            'category' => 'Sepatu Olahraga Kulit',
            'unit' => 'pasang',
            'cost_price' => 200000,
            'base_price' => 350000,
            'current_stock' => 5,
            'min_stock_alert' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHasErrors(['delete']);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_can_delete_empty_category(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'name' => 'Kategori Kosong',
            'slug' => 'kategori-kosong',
            'type' => 'raw',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
