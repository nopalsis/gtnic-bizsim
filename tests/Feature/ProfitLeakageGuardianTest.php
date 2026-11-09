<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfitLeakageGuardianTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_profit_leakage_and_margin_guardian(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superadmin)->get(route('simulator.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Simulator/Index')
            ->where('systemName', 'Profit Leakage & Margin Guardian')
            ->has('leakageCards')
            ->has('leakageCards.marginSlippage')
            ->has('leakageCards.wasteCost')
            ->has('leakageCards.idleCapital')
            ->has('productAudits')
            ->has('actionableInsights')
            ->where('actionableInsights.0.code', 'A')
            ->where('actionableInsights.1.code', 'B')
            ->where('actionableInsights.2.code', 'C')
        );
    }

    public function test_leakage_cards_contain_specified_operational_metrics(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superadmin)->get(route('simulator.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('leakageCards.marginSlippage.amount')
            ->has('leakageCards.marginSlippage.note')
            ->has('leakageCards.wasteCost.amount')
            ->has('leakageCards.wasteCost.note')
            ->has('leakageCards.idleCapital.amount')
            ->has('leakageCards.idleCapital.note')
        );
    }

    public function test_filter_period_dynamically_updates_period_label(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $res7 = $this->actingAs($superadmin)->get(route('simulator.index', ['period' => '7_days']));
        $res7->assertStatus(200);
        $res7->assertInertia(fn (Assert $page) => $page
            ->where('period', '7_days')
            ->where('periodLabel', '7 Hari Terakhir')
            ->where('leakageCards.marginSlippage.periodLabel', '7 Hari Terakhir')
            ->where('leakageCards.wasteCost.periodLabel', '7 Hari Terakhir')
        );

        $resMonth = $this->actingAs($superadmin)->get(route('simulator.index', ['period' => 'this_month']));
        $resMonth->assertStatus(200);
        $resMonth->assertInertia(fn (Assert $page) => $page
            ->where('period', 'this_month')
            ->where('periodLabel', 'Bulan Ini')
            ->where('leakageCards.marginSlippage.periodLabel', 'Bulan Ini')
            ->where('leakageCards.wasteCost.periodLabel', 'Bulan Ini')
        );
    }

    public function test_product_audits_contain_leaking_models_and_hpp_breakdowns(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superadmin)->get(route('simulator.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('productAudits', fn (Assert $audits) => $audits
                ->each(fn (Assert $item) => $item
                    ->has('name')
                    ->has('targetMargin')
                    ->has('realMargin')
                    ->has('leakPoint')
                    ->has('status')
                    ->has('hppBreakdown')
                    ->has('hppBreakdown.materialCost')
                    ->has('hppBreakdown.wasteCost')
                    ->has('hppBreakdown.laborCost')
                    ->has('hppBreakdown.salesDiscount')
                    ->has('hppBreakdown.realNetProfit')
                    ->etc()
                )
            )
        );
    }

    public function test_non_superadmin_users_are_forbidden_from_profit_leakage_guardian(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $sales = User::factory()->create([
            'role' => 'sales',
            'is_active' => true,
        ]);
        $craftsman = User::factory()->create([
            'role' => 'craftsman',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get(route('simulator.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($sales)->get(route('simulator.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($craftsman)->get(route('simulator.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }
}

