<?php

namespace App\Http\Controllers;

use App\Models\OperationalSetting;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the executive dashboard.
     */
    public function index(): Response
    {
        // 1. KPI Calculations
        $netRevenue = (float) Transaction::sum('net_amount');
        $grossRevenue = (float) Transaction::sum('total_amount');
        $totalDiscount = (float) Transaction::sum('total_discount');

        $cogs = (float) TransactionItem::sum(DB::raw('quantity * cost_price'));
        $grossProfit = $netRevenue - $cogs;
        $profitMargin = $netRevenue > 0 ? round(($grossProfit / $netRevenue) * 100, 1) : 0;

        $setting = OperationalSetting::first() ?? new OperationalSetting([
            'employee_count' => 4,
            'salary_per_employee' => 2800000,
            'monthly_fixed_cost' => 4500000,
            'monthly_max_capacity' => 5000,
        ]);

        $totalSalary = (float) ($setting->employee_count * $setting->salary_per_employee);
        $fixedCost = (float) $setting->monthly_fixed_cost;

        $wasteCost = (float) StockMovement::where('stock_movements.type', 'waste')
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * products.cost_price'));

        $wasteCount = (int) StockMovement::where('type', 'waste')->sum('quantity');

        $netProfit = $grossProfit - ($totalSalary + $fixedCost + $wasteCost);

        // 2. Capacity & Sales Volume
        $totalItemsSold = (int) TransactionItem::sum('quantity');
        $maxCapacity = (int) $setting->monthly_max_capacity;
        $capacityUsagePercent = $maxCapacity > 0 ? round(($totalItemsSold / $maxCapacity) * 100, 1) : 0;

        // 3. Recent Deal Activity
        $recentTransactions = Transaction::with(['transactionItems.product'])
            ->latest('transaction_date')
            ->take(6)
            ->get()
            ->map(function ($tx) {
                $itemNames = $tx->transactionItems->map(fn ($it) => ($it->product->name ?? 'Produk').' (x'.$it->quantity.')')->implode(', ');
                $discountPct = $tx->total_amount > 0 ? round(($tx->total_discount / $tx->total_amount) * 100, 1) : 0;

                return [
                    'id' => $tx->id,
                    'invoice_number' => $tx->invoice_number,
                    'date' => $tx->transaction_date?->format('d M Y · H:i'),
                    'sales_person' => $tx->sales_person_name ?? 'Staff',
                    'items' => $itemNames,
                    'total_amount' => (float) $tx->total_amount,
                    'total_discount' => (float) $tx->total_discount,
                    'net_amount' => (float) $tx->net_amount,
                    'discount_percent' => $discountPct,
                    'status' => $discountPct === 0.0 ? 'normal' : ($discountPct <= 8.0 ? 'safe' : 'warning'),
                ];
            });

        // 4. Cost Distribution Breakdown
        $totalExpenses = $cogs + $totalSalary + $fixedCost + $wasteCost;
        $costDistribution = [
            [
                'category' => 'HPP Bahan (COGS)',
                'amount' => $cogs,
                'percentage' => $totalExpenses > 0 ? round(($cogs / $totalExpenses) * 100, 1) : 0,
            ],
            [
                'category' => 'Gaji Karyawan',
                'amount' => $totalSalary,
                'percentage' => $totalExpenses > 0 ? round(($totalSalary / $totalExpenses) * 100, 1) : 0,
            ],
            [
                'category' => 'Operasional / Sewa',
                'amount' => $fixedCost,
                'percentage' => $totalExpenses > 0 ? round(($fixedCost / $totalExpenses) * 100, 1) : 0,
            ],
            [
                'category' => 'Kerugian Bahan (Waste)',
                'amount' => $wasteCost,
                'percentage' => $totalExpenses > 0 ? round(($wasteCost / $totalExpenses) * 100, 1) : 0,
            ],
        ];

        // 5. Intelligent Health & Risk Alerts
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'min_stock_alert')
            ->pluck('name')
            ->toArray();

        $alerts = [];
        if (! empty($lowStockProducts)) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Stok Mendekati Habis',
                'message' => 'Produk '.implode(', ', array_slice($lowStockProducts, 0, 3)).' sudah menyentuh batas minimum stok alert.',
            ];
        }

        if ($wasteCost > 0) {
            $alerts[] = [
                'type' => 'risk',
                'title' => 'Peringatan Kebocoran Waste',
                'message' => 'Terjadi kerugian bahan senilai Rp '.number_format($wasteCost, 0, ',', '.').' ('.$wasteCount.' unit terbuang). Evaluasi batas kedaluwarsa produk etalase.',
            ];
        }

        $alerts[] = [
            'type' => 'negotiation',
            'title' => 'Proteksi Margin Negosiasi',
            'message' => 'Rata-rata margin keuntungan saat ini berada di level '.$profitMargin.'%. Pastikan diskon tawar-menawar sales tidak melebihi 10% per transaksi.',
        ];

        return Inertia::render('Dashboard/Index', [
            'kpis' => [
                'netRevenue' => $netRevenue,
                'grossRevenue' => $grossRevenue,
                'totalDiscount' => $totalDiscount,
                'netProfit' => $netProfit,
                'grossProfit' => $grossProfit,
                'profitMargin' => $profitMargin,
                'wasteCost' => $wasteCost,
                'wasteCount' => $wasteCount,
            ],
            'capacity' => [
                'soldUnits' => $totalItemsSold,
                'maxCapacity' => $maxCapacity,
                'usagePercent' => $capacityUsagePercent,
                'status' => $capacityUsagePercent > 85 ? 'Kritis / Mendekati Kewalahan' : 'Optimal & Terkendali',
            ],
            'costDistribution' => $costDistribution,
            'recentTransactions' => $recentTransactions,
            'healthScore' => [
                'score' => 88,
                'grade' => 'A-',
                'status' => 'Stabilitas Keuangan Sehat',
            ],
            'alerts' => $alerts,
        ]);
    }
}
