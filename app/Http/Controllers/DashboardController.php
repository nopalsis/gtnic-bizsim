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
        // 1. KPI & Profit Calculations
        $netRevenue = (float) Transaction::sum('net_amount');
        $grossRevenue = (float) Transaction::sum('total_amount');
        $totalDiscount = (float) Transaction::sum('total_discount');
        $discountRate = $grossRevenue > 0 ? round(($totalDiscount / $grossRevenue) * 100, 1) : 0.0;

        $cogs = (float) TransactionItem::sum(DB::raw('quantity * cost_price'));
        $grossProfit = $netRevenue - $cogs;
        $profitMargin = $netRevenue > 0 ? round(($grossProfit / $netRevenue) * 100, 1) : 0.0;

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

        $totalFixedOpex = $totalSalary + $fixedCost;
        $netProfit = $grossProfit - ($totalFixedOpex + $wasteCost);

        // Break Even Point (BEP) Revenue Calculation
        $bepRevenue = $profitMargin > 0 ? round($totalFixedOpex / ($profitMargin / 100)) : 0.0;

        // 2. Capacity & Sales Volume
        $totalItemsSold = (int) TransactionItem::sum('quantity');
        $maxCapacity = (int) $setting->monthly_max_capacity;
        $capacityUsagePercent = $maxCapacity > 0 ? round(($totalItemsSold / $maxCapacity) * 100, 1) : 0.0;

        // 3. Category Sales Breakdown from Real Database
        $categorySales = TransactionItem::join('products', 'transaction_items.product_id', '=', 'products.id')
            ->select(
                'products.category',
                DB::raw('SUM(transaction_items.subtotal) as total_sales'),
                DB::raw('SUM(transaction_items.quantity) as total_qty')
            )
            ->groupBy('products.category')
            ->orderByDesc('total_sales')
            ->get()
            ->map(function ($cat) use ($netRevenue) {
                $sales = (float) $cat->total_sales;
                $pct = $netRevenue > 0 ? round(($sales / $netRevenue) * 100, 1) : 0.0;

                return [
                    'category' => $cat->category,
                    'sales' => $sales,
                    'quantity' => (int) $cat->total_qty,
                    'percentage' => $pct,
                ];
            });

        // 4. Recent Transactions with Detailed Items
        $recentTransactions = Transaction::with(['transactionItems.product'])
            ->latest('transaction_date')
            ->take(6)
            ->get()
            ->map(function ($tx) {
                $itemNames = $tx->transactionItems->map(fn ($it) => ($it->product->name ?? 'Produk').' (x'.$it->quantity.')')->implode(', ');
                $primaryCategory = $tx->transactionItems->first()?->product?->category ?? 'Beverage';
                $discountPct = $tx->total_amount > 0 ? round(($tx->total_discount / $tx->total_amount) * 100, 1) : 0.0;

                return [
                    'id' => $tx->id,
                    'invoice_number' => $tx->invoice_number,
                    'date' => $tx->transaction_date?->format('d M Y · H:i'),
                    'sales_person' => $tx->sales_person_name ?? 'Staff',
                    'primary_category' => $primaryCategory,
                    'items' => $itemNames,
                    'total_amount' => (float) $tx->total_amount,
                    'total_discount' => (float) $tx->total_discount,
                    'net_amount' => (float) $tx->net_amount,
                    'discount_percent' => $discountPct,
                    'status' => $discountPct === 0.0 ? 'normal' : ($discountPct <= 8.0 ? 'safe' : 'warning'),
                ];
            });

        // 5. Cost Distribution Breakdown
        $totalExpenses = $cogs + $totalSalary + $fixedCost + $wasteCost;
        $costDistribution = [
            [
                'category' => 'HPP Bahan (COGS)',
                'amount' => $cogs,
                'percentage' => $totalExpenses > 0 ? round(($cogs / $totalExpenses) * 100, 1) : 0.0,
            ],
            [
                'category' => 'Gaji Karyawan (4 Staff)',
                'amount' => $totalSalary,
                'percentage' => $totalExpenses > 0 ? round(($totalSalary / $totalExpenses) * 100, 1) : 0.0,
            ],
            [
                'category' => 'Sewa Toko & Operasional',
                'amount' => $fixedCost,
                'percentage' => $totalExpenses > 0 ? round(($fixedCost / $totalExpenses) * 100, 1) : 0.0,
            ],
            [
                'category' => 'Kerugian Limbah (Waste)',
                'amount' => $wasteCost,
                'percentage' => $totalExpenses > 0 ? round(($wasteCost / $totalExpenses) * 100, 1) : 0.0,
            ],
        ];

        // 6. Intelligent Health & Risk Alerts
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
                'message' => 'Terjadi kerugian bahan senilai Rp '.number_format($wasteCost, 0, ',', '.').' ('.$wasteCount.' unit produk pastry terbuang). Evaluasi batas kedaluwarsa etalase.',
            ];
        }

        $alerts[] = [
            'type' => 'negotiation',
            'title' => 'Proteksi Margin Negosiasi',
            'message' => 'Rata-rata diskon kasir tercatat '.$discountRate.'% dengan margin kotor '.$profitMargin.'%. Pertahankan toleransi diskon di bawah 10%.',
        ];

        $alerts[] = [
            'type' => 'target',
            'title' => 'Target Impas (BEP) Operasional',
            'message' => 'Untuk menutup biaya operasional bulanan (Rp '.number_format($totalFixedOpex, 0, ',', '.').'), target omzet impas adalah Rp '.number_format($bepRevenue, 0, ',', '.').'.',
        ];

        // 7. All Transactions for Detailed Ledger Modal
        $allTransactions = Transaction::with(['transactionItems.product'])
            ->latest('transaction_date')
            ->get()
            ->map(function ($tx) {
                $itemNames = $tx->transactionItems->map(fn ($it) => ($it->product->name ?? 'Produk').' (x'.$it->quantity.')')->implode(', ');
                $primaryCategory = $tx->transactionItems->first()?->product?->category ?? 'Beverage';
                $discountPct = $tx->total_amount > 0 ? round(($tx->total_discount / $tx->total_amount) * 100, 1) : 0.0;

                return [
                    'id' => $tx->id,
                    'invoice_number' => $tx->invoice_number,
                    'date' => $tx->transaction_date?->format('d M Y · H:i'),
                    'sales_person' => $tx->sales_person_name ?? 'Staff',
                    'primary_category' => $primaryCategory,
                    'items' => $itemNames,
                    'total_amount' => (float) $tx->total_amount,
                    'total_discount' => (float) $tx->total_discount,
                    'net_amount' => (float) $tx->net_amount,
                    'discount_percent' => $discountPct,
                    'status' => $discountPct === 0.0 ? 'normal' : ($discountPct <= 8.0 ? 'safe' : 'warning'),
                ];
            });

        // 8. Dynamic Team / Sales Recipients
        $recipients = Transaction::select('sales_person_name')
            ->distinct()
            ->whereNotNull('sales_person_name')
            ->pluck('sales_person_name')
            ->map(fn ($name, $idx) => [
                'id' => $idx + 1,
                'name' => $name,
                'role' => 'Kasir / Sales Barista',
            ]);

        // 9. Top Selling Products
        $topProducts = TransactionItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->product?->name ?? 'Produk',
                'category' => $item->product?->category ?? 'General',
                'quantity' => (int) $item->total_qty,
                'sales' => (float) $item->total_sales,
            ]);

        // 10. Chronological Daily Sales Trend for Bézier Line Chart
        $trendHistory = Transaction::selectRaw('DATE(transaction_date) as tx_date, SUM(net_amount) as total_net, SUM(total_amount) as total_gross, COUNT(*) as tx_count')
            ->groupBy('tx_date')
            ->orderBy('tx_date')
            ->get()
            ->map(fn ($t) => [
                'date' => date('d M', strtotime($t->tx_date)),
                'full_date' => (string) $t->tx_date,
                'amount' => (float) $t->total_net,
                'gross' => (float) $t->total_gross,
                'count' => (int) $t->tx_count,
            ]);

        // 11. Inventory & Waste Statistics
        $totalStockUnits = (int) Product::sum('current_stock');
        $totalProductsCount = (int) Product::count();
        $recentMovements = StockMovement::with('product')
            ->latest('movement_date')
            ->take(5)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'product_name' => $m->product?->name ?? 'Produk',
                'type' => $m->type,
                'quantity' => (int) $m->quantity,
                'date' => $m->movement_date?->format('d M Y') ?? (string) $m->movement_date,
                'notes' => $m->notes,
            ]);

        $wasteBreakdown = StockMovement::where('type', 'waste')
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->select(
                'products.name',
                'products.category',
                DB::raw('SUM(stock_movements.quantity) as waste_qty'),
                DB::raw('SUM(stock_movements.quantity * products.cost_price) as waste_loss')
            )
            ->groupBy('products.name', 'products.category')
            ->get()
            ->map(fn ($w) => [
                'name' => $w->name,
                'category' => $w->category,
                'quantity' => (int) $w->waste_qty,
                'loss' => (float) $w->waste_loss,
            ]);

        // 12. Staff Cashier Performance Metrics
        $staffPerformance = Transaction::select(
            'sales_person_name',
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(net_amount) as total_revenue'),
            DB::raw('ROUND(AVG(CASE WHEN total_amount > 0 THEN (total_discount / total_amount) * 100 ELSE 0 END), 1) as avg_discount_pct')
        )
            ->whereNotNull('sales_person_name')
            ->groupBy('sales_person_name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(fn ($s) => [
                'name' => $s->sales_person_name,
                'transactions' => (int) $s->total_transactions,
                'revenue' => (float) $s->total_revenue,
                'avg_discount' => (float) $s->avg_discount_pct,
            ]);

        // 13. Dynamic Business Health Score Algorithm (Margin + Waste + Discount Discipline)
        $marginPoints = min(40, max(0, ($profitMargin / 60) * 40));
        $wasteRatio = $netRevenue > 0 ? ($wasteCost / $netRevenue) * 100 : 0.0;
        $wastePoints = $wasteRatio <= 2.0 ? 30 : max(0, 30 - (($wasteRatio - 2.0) * 5));
        $discountPoints = $discountRate <= 5.0 ? 30 : max(0, 30 - (($discountRate - 5.0) * 3));
        $healthScoreValue = (int) round($marginPoints + $wastePoints + $discountPoints);

        $grade = match (true) {
            $healthScoreValue >= 90 => 'A',
            $healthScoreValue >= 80 => 'A-',
            $healthScoreValue >= 70 => 'B+',
            $healthScoreValue >= 60 => 'B',
            default => 'C',
        };
        $healthStatus = match (true) {
            $healthScoreValue >= 85 => 'Stabilitas Finansial Sangat Sehat',
            $healthScoreValue >= 70 => 'Stabilitas Finansial Cukup Terjaga',
            default => 'Perlu Pengawasan Biaya & Diskon',
        };

        return Inertia::render('Dashboard/Index', [
            'kpis' => [
                'netRevenue' => $netRevenue,
                'grossRevenue' => $grossRevenue,
                'totalDiscount' => $totalDiscount,
                'discountRate' => $discountRate,
                'netProfit' => $netProfit,
                'grossProfit' => $grossProfit,
                'profitMargin' => $profitMargin,
                'totalFixedOpex' => $totalFixedOpex,
                'bepRevenue' => $bepRevenue,
                'wasteCost' => $wasteCost,
                'wasteCount' => $wasteCount,
            ],
            'capacity' => [
                'soldUnits' => $totalItemsSold,
                'maxCapacity' => $maxCapacity,
                'usagePercent' => $capacityUsagePercent,
                'status' => $capacityUsagePercent > 85 ? 'Kritis / Mendekati Beban Puncak' : 'Optimal & Terkendali',
            ],
            'categorySales' => $categorySales,
            'costDistribution' => $costDistribution,
            'recentTransactions' => $recentTransactions,
            'allTransactions' => $allTransactions,
            'recipients' => $recipients,
            'topProducts' => $topProducts,
            'trendHistory' => $trendHistory,
            'inventoryStats' => [
                'totalStockUnits' => $totalStockUnits,
                'totalProductsCount' => $totalProductsCount,
                'lowStockCount' => count($lowStockProducts),
                'lowStockProducts' => $lowStockProducts,
                'recentMovements' => $recentMovements,
                'wasteBreakdown' => $wasteBreakdown,
            ],
            'staffPerformance' => $staffPerformance,
            'healthScore' => [
                'score' => $healthScoreValue,
                'grade' => $grade,
                'status' => $healthStatus,
            ],
            'alerts' => $alerts,
        ]);
    }
}
