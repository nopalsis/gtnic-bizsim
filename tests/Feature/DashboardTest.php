<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_dashboard_returns_executive_and_financial_props(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superadmin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('currentRole', 'superadmin')
            ->has('kpis')
            ->has('kpis.netRevenue')
            ->has('kpis.netProfit')
            ->has('capacity')
            ->has('categorySales')
            ->has('costDistribution')
            ->has('recentTransactions')
            ->has('topProducts')
            ->has('trendHistory')
            ->has('inventoryStats')
            ->has('staffPerformance')
            ->has('healthScore')
            ->has('alerts')
            ->has('quickSimulatorBaseline')
        );
    }

    public function test_admin_dashboard_returns_operations_props_without_sensitive_financials(): void
    {
        $adminUser = User::factory()->create([
            'name' => 'Dewi Admin Operasional',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($adminUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('currentRole', 'admin')
            ->where('kpis', null)
            ->where('quickSimulatorBaseline', null)
            ->has('adminOperationsData')
            ->has('adminOperationsData.totalTransactionsInput')
            ->has('adminOperationsData.totalItemsSoldQty')
            ->has('adminOperationsData.activeProductsCount')
            ->has('adminOperationsData.lowStockCount')
            ->has('adminOperationsData.dailyInputTrend')
            ->has('adminOperationsData.lowStockProducts')
            ->has('adminOperationsData.recentInputActivities')
        );
    }

    public function test_sales_dashboard_returns_sales_performance_props(): void
    {
        $salesUser = User::factory()->create([
            'name' => 'Asep Sales',
            'role' => 'sales',
            'is_active' => true,
        ]);

        $response = $this->actingAs($salesUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('currentRole', 'sales')
            ->has('salesData')
            ->has('salesData.myNetRevenue')
            ->has('salesData.completedTransactions')
            ->has('salesData.completedTransactions.retail')
            ->has('salesData.completedTransactions.wholesale')
            ->has('salesData.myAvgDiscount')
            ->has('salesData.salesTrend')
            ->has('salesData.topSellingStock')
            ->has('salesData.recentTransactions')
        );
    }

    public function test_craftsman_dashboard_returns_workshop_operations_props(): void
    {
        $craftsmanUser = User::factory()->create([
            'name' => 'Bambang Pengrajin',
            'role' => 'craftsman',
            'salary_type' => 'piece_rate',
            'base_salary' => 0,
            'piece_rate' => 25000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($craftsmanUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('currentRole', 'craftsman')
            ->has('craftsmanData')
            ->has('craftsmanData.completedProductionUnits')
            ->has('craftsmanData.estimatedSalary')
            ->has('craftsmanData.wasteMetrics')
            ->has('craftsmanData.capacityLoad')
            ->has('craftsmanData.lowStockRawMaterials')
            ->has('craftsmanData.recentWorkshopLogs')
        );
    }
}
