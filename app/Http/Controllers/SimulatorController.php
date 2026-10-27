<?php

namespace App\Http\Controllers;

use App\Models\OperationalSetting;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SimulatorController extends Controller
{
    /**
     * Display What-If Scenario Simulator for OptimaBiz Leathercraft.
     */
    public function index(): Response
    {
        // 1. Ambil Pengaturan Operasional Riil
        $setting = OperationalSetting::first() ?? new OperationalSetting([
            'workshop_name' => 'OptimaBiz Leathercraft & Shoes Workshop',
            'employee_count' => 5,
            'salary_per_employee' => 3200000,
            'monthly_fixed_cost' => 6500000,
            'monthly_max_capacity' => 500,
        ]);

        // 2. Baseline Riil dari Database
        $netRevenue = (float) Transaction::sum('net_amount');
        $totalItemsSold = (int) TransactionItem::sum('quantity');
        $cogs = (float) TransactionItem::sum(DB::raw('quantity * cost_price'));
        $grossProfit = $netRevenue - $cogs;
        $profitMargin = $netRevenue > 0 ? round(($grossProfit / $netRevenue) * 100, 1) : 0.0;

        $totalSalary = (float) ($setting->employee_count * $setting->salary_per_employee);
        $fixedCost = (float) $setting->monthly_fixed_cost;
        $totalFixedOpex = $totalSalary + $fixedCost;

        $wasteCost = (float) StockMovement::where('stock_movements.type', 'waste')
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * products.cost_price'));
        $wasteCount = (int) StockMovement::where('type', 'waste')->sum('quantity');

        $netProfit = $grossProfit - ($totalFixedOpex + $wasteCost);
        $bepRevenue = $profitMargin > 0 ? round($totalFixedOpex / ($profitMargin / 100)) : 0.0;

        $avgPricePerUnit = $totalItemsSold > 0 ? round($netRevenue / $totalItemsSold) : 450000;
        $avgCostPerUnit = $totalItemsSold > 0 ? round($cogs / $totalItemsSold) : 220000;

        return Inertia::render('Simulator/Index', [
            'baseline' => [
                'workshopName' => $setting->workshop_name,
                'employeeCount' => (int) $setting->employee_count,
                'salaryPerEmployee' => (float) $setting->salary_per_employee,
                'monthlyFixedCost' => (float) $setting->monthly_fixed_cost,
                'monthlyMaxCapacity' => (int) $setting->monthly_max_capacity,
                'totalItemsSold' => $totalItemsSold,
                'netRevenue' => $netRevenue,
                'cogs' => $cogs,
                'grossProfit' => $grossProfit,
                'profitMargin' => $profitMargin,
                'totalFixedOpex' => $totalFixedOpex,
                'wasteCost' => $wasteCost,
                'wasteCount' => $wasteCount,
                'netProfit' => $netProfit,
                'bepRevenue' => $bepRevenue,
                'avgPricePerUnit' => $avgPricePerUnit,
                'avgCostPerUnit' => $avgCostPerUnit,
            ],
        ]);
    }

    /**
     * Update operational baseline settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'workshop_name' => 'required|string|max:255',
            'employee_count' => 'required|integer|min:1|max:50',
            'salary_per_employee' => 'required|numeric|min:0',
            'monthly_fixed_cost' => 'required|numeric|min:0',
            'monthly_max_capacity' => 'required|integer|min:10',
        ]);

        $setting = OperationalSetting::first();
        if ($setting) {
            $setting->update($validated);
        } else {
            OperationalSetting::create($validated);
        }

        return redirect()->route('simulator.index');
    }
}
