<?php

namespace App\Http\Controllers;

use App\Models\OperationalSetting;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the role-tailored dashboard for OptimaBiz Leathercraft.
     * Superadmin: Executive & Financial View (Keuntungan, Omzet, Margin, HPP, BEP)
     * Admin: Admin Operasional View (Input Transaksi, Katalog Produk & Mutasi Stok - Tanpa Finansial)
     * Sales: Sales Performance & Deal View
     * Craftsman: Workshop Operations & Task View
     */
    public function index(): Response
    {
        $user = Auth::user();
        $role = $user->role ?? 'admin';
        $isSuperAdmin = ($role === 'superadmin');

        $setting = OperationalSetting::first() ?? new OperationalSetting([
            'workshop_name' => 'OptimaBiz Leathercraft & Shoes Workshop',
            'employee_count' => 5,
            'salary_per_employee' => 3200000,
            'monthly_fixed_cost' => 6500000,
            'monthly_max_capacity' => 500,
        ]);

        $maxCapacity = (int) ($setting->monthly_max_capacity ?? 500);

        // ==========================================
        // 1. EXECUTIVE & FINANCIAL CALCULATIONS (SUPERADMIN ONLY)
        // ==========================================
        $netRevenue = (float) Transaction::sum('net_amount');
        $grossRevenue = (float) Transaction::sum('total_amount');
        $totalDiscount = (float) Transaction::sum('total_discount');
        $discountRate = $grossRevenue > 0 ? round(($totalDiscount / $grossRevenue) * 100, 1) : 0.0;

        $cogs = (float) TransactionItem::sum(DB::raw('quantity * cost_price'));
        $grossProfit = $netRevenue - $cogs;
        $profitMargin = $netRevenue > 0 ? round(($grossProfit / $netRevenue) * 100, 1) : 0.0;

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

        $totalTransactionsCount = (int) Transaction::count();
        $totalItemsSold = (int) TransactionItem::sum('quantity');

        // Rata-rata omset per hari aktif transaksi
        $activeDaysCount = (int) Transaction::selectRaw('COUNT(DISTINCT DATE(transaction_date)) as d')->value('d') ?: 1;
        $dailyAvgRevenue = $activeDaysCount > 0 ? round($netRevenue / $activeDaysCount) : 0.0;

        $capacityUsagePercent = $maxCapacity > 0 ? round(($totalItemsSold / $maxCapacity) * 100, 1) : 0.0;

        // Category Sales Breakdown
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

        // Recent Transactions with Margin Analysis for Super Admin (25 transaksi terakhir untuk pagination dashboard)
        $recentTransactions = Transaction::with(['transactionItems.product'])
            ->latest('transaction_date')
            ->take(25)
            ->get()
            ->map(function ($tx) {
                $itemNames = $tx->transactionItems->map(fn ($it) => ($it->product->name ?? 'Produk').' (x'.$it->quantity.' '.($it->product->unit ?? 'pasang').')')->implode(', ');
                $primaryCategory = $tx->transactionItems->first()?->product?->category ?? 'Sepatu Pria';
                $discountPct = $tx->total_amount > 0 ? round(($tx->total_discount / $tx->total_amount) * 100, 1) : 0.0;

                return [
                    'id' => $tx->id,
                    'invoice_number' => $tx->invoice_number,
                    'date' => $tx->transaction_date?->format('d M Y · H:i'),
                    'customer_name' => $tx->customer_name ?? 'Pelanggan Showroom',
                    'sales_person' => $tx->sales_person_name ?? 'Staff Workshop',
                    'payment_method' => $tx->payment_method ?? 'Transfer Bank',
                    'primary_category' => $primaryCategory,
                    'items' => $itemNames,
                    'total_amount' => (float) $tx->total_amount,
                    'total_discount' => (float) $tx->total_discount,
                    'net_amount' => (float) $tx->net_amount,
                    'discount_percent' => $discountPct,
                    'status' => $discountPct === 0.0 ? 'normal' : ($discountPct <= 10.0 ? 'safe' : 'warning'),
                ];
            });

        // Cost Distribution Breakdown
        $totalExpenses = $cogs + $totalSalary + $fixedCost + $wasteCost;
        $costDistribution = [
            [
                'category' => 'Bahan Kulit & Sol',
                'amount' => $cogs,
                'percentage' => $totalExpenses > 0 ? round(($cogs / $totalExpenses) * 100, 1) : 0.0,
            ],
            [
                'category' => 'Upah Tukang Sepatu',
                'amount' => $totalSalary,
                'percentage' => $totalExpenses > 0 ? round(($totalSalary / $totalExpenses) * 100, 1) : 0.0,
            ],
            [
                'category' => 'Sewa Bengkel & Listrik',
                'amount' => $fixedCost,
                'percentage' => $totalExpenses > 0 ? round(($fixedCost / $totalExpenses) * 100, 1) : 0.0,
            ],
            [
                'category' => 'Bahan Rusak / Sisa Kulit',
                'amount' => $wasteCost,
                'percentage' => $totalExpenses > 0 ? round(($wasteCost / $totalExpenses) * 100, 1) : 0.0,
            ],
        ];

        // Intelligent Health & Risk Alerts
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'min_stock_alert')
            ->pluck('name')
            ->toArray();

        $alerts = [];
        if (! empty($lowStockProducts)) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Stok Sepatu & Sandal Mau Habis',
                'message' => 'Produk '.implode(', ', array_slice($lowStockProducts, 0, 3)).' sudah hampir habis. Segera jadwalkan pembuatan sepatu baru.',
            ];
        }

        if ($wasteCost > 0) {
            $alerts[] = [
                'type' => 'risk',
                'title' => 'Peringatan Bahan Kulit Rusak',
                'message' => 'Ada bahan rusak/cacat senilai Rp '.number_format($wasteCost, 0, ',', '.').' ('.$wasteCount.' pasang/potongan kulit). Periksa kembali kerapian pengeleman dan pemotongan kulit.',
            ];
        }

        $alerts[] = [
            'type' => 'negotiation',
            'title' => 'Pengingat Potongan Harga (Diskon)',
            'message' => 'Rata-rata diskon pesanan yang diberikan adalah '.$discountRate.'%. Sebaiknya diskon dijaga di bawah 15% agar keuntungan bengkel tetap aman.',
        ];

        $alerts[] = [
            'type' => 'target',
            'title' => 'Target Penjualan untuk Balik Modal',
            'message' => 'Untuk menutup gaji tukang dan sewa bengkel (Rp '.number_format($totalFixedOpex, 0, ',', '.').'/bulan), penjualan minimal agar tidak rugi adalah Rp '.number_format($bepRevenue, 0, ',', '.').'.',
        ];

        // All Transactions for Ledger (Super Admin Only)
        $allTransactions = Transaction::with(['transactionItems.product'])
            ->latest('transaction_date')
            ->get()
            ->map(function ($tx) {
                $itemNames = $tx->transactionItems->map(fn ($it) => ($it->product->name ?? 'Produk').' (x'.$it->quantity.' '.($it->product->unit ?? 'pasang').')')->implode(', ');
                $primaryCategory = $tx->transactionItems->first()?->product?->category ?? 'Sepatu Pria';
                $discountPct = $tx->total_amount > 0 ? round(($tx->total_discount / $tx->total_amount) * 100, 1) : 0.0;

                return [
                    'id' => $tx->id,
                    'invoice_number' => $tx->invoice_number,
                    'date' => $tx->transaction_date?->format('d M Y · H:i'),
                    'customer_name' => $tx->customer_name ?? 'Pelanggan Showroom',
                    'sales_person' => $tx->sales_person_name ?? 'Staff Workshop',
                    'payment_method' => $tx->payment_method ?? 'Transfer Bank',
                    'primary_category' => $primaryCategory,
                    'items' => $itemNames,
                    'total_amount' => (float) $tx->total_amount,
                    'total_discount' => (float) $tx->total_discount,
                    'net_amount' => (float) $tx->net_amount,
                    'discount_percent' => $discountPct,
                    'status' => $discountPct === 0.0 ? 'normal' : ($discountPct <= 10.0 ? 'safe' : 'warning'),
                ];
            });

        // Top Selling Footwear
        $topProducts = TransactionItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->product?->name ?? 'Produk Kulit',
                'sku' => $item->product?->sku ?? '-',
                'category' => $item->product?->category ?? 'Sepatu Pria',
                'unit' => $item->product?->unit ?? 'pasang',
                'quantity' => (int) $item->total_qty,
                'sales' => (float) $item->total_sales,
            ]);

        // Daily Sales Trend
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

        // Staff Performance Metrics
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

        // Quick Simulator Baseline Data for Super Admin
        $avgPricePerPair = $totalItemsSold > 0 ? round($netRevenue / $totalItemsSold) : 450000;
        $avgCostPerPair = $totalItemsSold > 0 ? round($cogs / $totalItemsSold) : 220000;
        $quickSimulatorBaseline = [
            'avgPricePerPair' => $avgPricePerPair,
            'avgCostPerPair' => $avgCostPerPair,
            'fixedCost' => $totalFixedOpex,
            'wasteCost' => $wasteCost,
            'currentVolume' => $totalItemsSold,
            'maxCapacity' => $maxCapacity,
            'currentNetProfit' => $netProfit,
        ];

        // ==========================================
        // 2. ADMIN OPERASIONAL VIEW (ADMIN BIASA)
        // Fokus hanya pada kuantitas fisik, input data, kontrol stok tanpa finansial/keuntungan/omzet
        // ==========================================
        $totalTransactionsCount = (int) Transaction::count();
        $dailyInputTrend = Transaction::selectRaw('DATE(transaction_date) as tx_date, COUNT(*) as tx_count')
            ->groupBy('tx_date')
            ->orderBy('tx_date')
            ->get()
            ->map(fn ($t) => [
                'date' => date('d M', strtotime($t->tx_date)),
                'count' => (int) $t->tx_count,
            ]);

        $lowStockProductsDetailed = Product::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'min_stock_alert')
            ->orderBy('current_stock')
            ->take(6)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'category' => $p->category,
                'current_stock' => (int) $p->current_stock,
                'min_stock_alert' => (int) $p->min_stock_alert,
                'unit' => $p->unit ?? 'pasang',
            ]);

        $recentInputActivities = Transaction::with(['transactionItems.product'])
            ->latest('transaction_date')
            ->take(5)
            ->get()
            ->map(function ($tx) {
                $itemNames = $tx->transactionItems->map(fn ($it) => ($it->product->name ?? 'Produk').' (x'.$it->quantity.' '.($it->product->unit ?? 'pasang').')')->implode(', ');

                return [
                    'id' => $tx->id,
                    'invoice_number' => $tx->invoice_number,
                    'date' => $tx->transaction_date?->format('d M Y · H:i'),
                    'customer_name' => $tx->customer_name ?? 'Pelanggan Showroom',
                    'sales_person' => $tx->sales_person_name ?? 'Staff Input',
                    'payment_method' => $tx->payment_method ?? 'Transfer Bank',
                    'items' => $itemNames,
                ];
            });

        $adminOperationsData = [
            'totalTransactionsInput' => $totalTransactionsCount,
            'totalItemsSoldQty' => $totalItemsSold,
            'activeProductsCount' => (int) Product::where('is_active', true)->count(),
            'lowStockCount' => count($lowStockProducts),
            'dailyInputTrend' => $dailyInputTrend,
            'lowStockProducts' => $lowStockProductsDetailed,
            'recentInputActivities' => $recentInputActivities,
        ];

        // ==========================================
        // 3. SALES PERFORMANCE & DEAL VIEW (SALES)
        // ==========================================
        $userName = $user->name ?? '';
        $salesTxQuery = Transaction::where(function ($q) use ($userName) {
            $q->where('sales_person_name', $userName)
                ->orWhere('sales_person_name', 'LIKE', '%'.strtok($userName, ' ').'%');
        });

        if ($salesTxQuery->count() === 0) {
            $salesTxQuery = Transaction::query();
        }

        $myNetRevenue = (float) (clone $salesTxQuery)->sum('net_amount');
        $myGrossRevenue = (float) (clone $salesTxQuery)->sum('total_amount');
        $myTotalDiscount = (float) (clone $salesTxQuery)->sum('total_discount');
        $myAvgDiscount = $myGrossRevenue > 0 ? round(($myTotalDiscount / $myGrossRevenue) * 100, 1) : 0.0;
        $myTotalTx = (clone $salesTxQuery)->count();

        $myWholesaleTx = (clone $salesTxQuery)->where(function ($q) {
            $q->where('total_discount', '>', 0)
                ->orWhere('total_amount', '>=', 1000000);
        })->count();
        $myRetailTx = max(0, $myTotalTx - $myWholesaleTx);

        $mySalesTrend = (clone $salesTxQuery)
            ->selectRaw('DATE(transaction_date) as tx_date, SUM(net_amount) as total_net, COUNT(*) as tx_count')
            ->groupBy('tx_date')
            ->orderBy('tx_date')
            ->get()
            ->map(fn ($t) => [
                'date' => date('d M', strtotime($t->tx_date)),
                'amount' => (float) $t->total_net,
                'count' => (int) $t->tx_count,
            ]);

        $topSellingStock = TransactionItem::select('product_id', DB::raw('SUM(quantity) as sold_qty'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('sold_qty')
            ->take(5)
            ->get()
            ->map(function ($it) {
                $p = $it->product;
                $stock = $p ? (int) $p->current_stock : 0;
                $min = $p ? (int) $p->min_stock_alert : 5;
                $status = $stock <= 0 ? 'Habis' : ($stock <= $min ? 'Stok Menipis' : 'Ready');

                return [
                    'id' => $p?->id,
                    'name' => $p?->name ?? 'Sepatu Kulit',
                    'sku' => $p?->sku ?? '-',
                    'category' => $p?->category ?? 'Sepatu',
                    'unit' => $p?->unit ?? 'pasang',
                    'current_stock' => $stock,
                    'min_stock_alert' => $min,
                    'sold_qty' => (int) $it->sold_qty,
                    'status' => $status,
                ];
            });

        $myRecentTransactions = (clone $salesTxQuery)
            ->with('transactionItems.product')
            ->latest('transaction_date')
            ->take(5)
            ->get()
            ->map(function ($tx) {
                $itemsDesc = $tx->transactionItems->map(fn ($it) => ($it->product->name ?? 'Produk').' (x'.$it->quantity.')')->implode(', ');
                $discountPct = $tx->total_amount > 0 ? round(($tx->total_discount / $tx->total_amount) * 100, 1) : 0.0;

                return [
                    'id' => $tx->id,
                    'invoice_number' => $tx->invoice_number,
                    'date' => $tx->transaction_date?->format('d M Y · H:i'),
                    'customer_name' => $tx->customer_name ?? 'Pelanggan Showroom',
                    'payment_method' => $tx->payment_method ?? 'Transfer Bank',
                    'net_amount' => (float) $tx->net_amount,
                    'total_discount' => (float) $tx->total_discount,
                    'discount_percent' => $discountPct,
                    'items' => $itemsDesc,
                    'status' => $discountPct > 10.0 ? 'Diskon Nego Tinggi' : 'Normal / Aman',
                ];
            });

        $salesData = [
            'myNetRevenue' => $myNetRevenue,
            'myGrossRevenue' => $myGrossRevenue,
            'myAvgDiscount' => $myAvgDiscount,
            'completedTransactions' => [
                'total' => $myTotalTx,
                'retail' => $myRetailTx,
                'wholesale' => $myWholesaleTx,
            ],
            'salesTrend' => $mySalesTrend,
            'topSellingStock' => $topSellingStock,
            'recentTransactions' => $myRecentTransactions,
        ];

        // ==========================================
        // 4. WORKSHOP OPERATIONS & TASK VIEW (CRAFTSMAN)
        // ==========================================
        $completedProductionUnits = (int) StockMovement::where('type', 'in')->sum('quantity');

        $salaryType = $user->salary_type ?? 'fixed_monthly';
        $baseSalary = (float) ($user->base_salary ?? 3200000);
        $pieceRate = (float) ($user->piece_rate ?? 25000);

        $pieceEarnings = $completedProductionUnits * $pieceRate;
        $totalEstimatedSalary = match ($salaryType) {
            'piece_rate' => $pieceEarnings,
            'hybrid' => $baseSalary + $pieceEarnings,
            default => $baseSalary,
        };

        $salaryTypeLabel = match ($salaryType) {
            'piece_rate' => 'Upah Borongan Murni',
            'hybrid' => 'Gaji Pokok + Borongan',
            default => 'Gaji Pokok Tetap',
        };

        $totalWasteUnits = (int) StockMovement::where('type', 'waste')->sum('quantity');
        $totalWasteLogs = (int) StockMovement::where('type', 'waste')->count();

        $capacityLoadPercent = $maxCapacity > 0 ? round(($completedProductionUnits / $maxCapacity) * 100, 1) : 0.0;
        $capacityLoadStatus = match (true) {
            $capacityLoadPercent >= 90 => 'Kritis / Mendekati Maksimal',
            $capacityLoadPercent >= 75 => 'Tinggi / Waspada Overload',
            default => 'Optimal & Terkendali',
        };

        $lowStockRawMaterials = Product::where('is_active', true)
            ->where(function ($q) {
                $q->whereIn('category', ['Bahan Baku Kulit', 'Komponen Sol & Lem', 'Perawatan & Aksesoris'])
                    ->orWhere('unit', 'sq ft')
                    ->orWhere('unit', 'lembar');
            })
            ->whereColumn('current_stock', '<=', 'min_stock_alert')
            ->orderBy('current_stock')
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'category' => $p->category,
                'current_stock' => (int) $p->current_stock,
                'min_stock_alert' => (int) $p->min_stock_alert,
                'unit' => $p->unit ?? 'feet',
                'urgency' => $p->current_stock <= max(1, floor($p->min_stock_alert / 2)) ? 'Kritis' : 'Menipis',
            ]);

        if ($lowStockRawMaterials->isEmpty()) {
            $lowStockRawMaterials = Product::where('is_active', true)
                ->whereIn('category', ['Bahan Baku Kulit', 'Komponen Sol & Lem', 'Perawatan & Aksesoris'])
                ->orderBy('current_stock')
                ->take(4)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'category' => $p->category,
                    'current_stock' => (int) $p->current_stock,
                    'min_stock_alert' => (int) $p->min_stock_alert,
                    'unit' => $p->unit ?? 'feet',
                    'urgency' => 'Normal',
                ]);
        }

        $recentWorkshopLogs = StockMovement::whereIn('type', ['in', 'waste'])
            ->with('product')
            ->latest('movement_date')
            ->take(6)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'product_name' => $m->product?->name ?? 'Produk Kulit',
                'product_sku' => $m->product?->sku ?? '-',
                'category' => $m->product?->category ?? 'Produksi',
                'type' => $m->type,
                'quantity' => (int) $m->quantity,
                'unit' => $m->product?->unit ?? 'pasang',
                'date' => $m->movement_date?->format('d M Y') ?? (string) $m->movement_date,
                'notes' => $m->notes ?? ($m->type === 'in' ? 'Hasil produksi workshop selesai' : 'Cacat proses pemotongan/skiving'),
            ]);

        $craftsmanData = [
            'completedProductionUnits' => $completedProductionUnits,
            'estimatedSalary' => [
                'totalEstimatedSalary' => $totalEstimatedSalary,
                'baseSalary' => $baseSalary,
                'pieceEarnings' => $pieceEarnings,
                'pieceRate' => $pieceRate,
                'salaryType' => $salaryType,
                'salaryTypeLabel' => $salaryTypeLabel,
            ],
            'wasteMetrics' => [
                'totalUnits' => $totalWasteUnits,
                'totalLogs' => $totalWasteLogs,
            ],
            'capacityLoad' => [
                'producedUnits' => $completedProductionUnits,
                'maxCapacity' => $maxCapacity,
                'percentage' => $capacityLoadPercent,
                'remaining' => max(0, $maxCapacity - $completedProductionUnits),
                'status' => $capacityLoadStatus,
            ],
            'lowStockRawMaterials' => $lowStockRawMaterials,
            'recentWorkshopLogs' => $recentWorkshopLogs,
        ];

        return Inertia::render('Dashboard/Index', [
            'currentRole' => $role,
            'workshopName' => $setting->workshop_name ?? 'OptimaBiz Leathercraft & Shoes',
            // Data Finansial Eksekutif: Hanya diberikan jika Super Admin
            'kpis' => $isSuperAdmin ? [
                'netRevenue' => $netRevenue,
                'grossRevenue' => $grossRevenue,
                'totalDiscount' => $totalDiscount,
                'discountRate' => $discountRate,
                'totalTransactions' => $totalTransactionsCount,
                'totalProductsSold' => $totalItemsSold,
                'dailyAvgRevenue' => (float) $dailyAvgRevenue,
                'activeDaysCount' => $activeDaysCount,
                'netProfit' => $netProfit,
                'grossProfit' => $grossProfit,
                'profitMargin' => $profitMargin,
                'totalFixedOpex' => $totalFixedOpex,
                'bepRevenue' => $bepRevenue,
                'wasteCost' => $wasteCost,
                'wasteCount' => $wasteCount,
            ] : null,
            'capacity' => [
                'soldUnits' => $totalItemsSold,
                'maxCapacity' => $maxCapacity,
                'usagePercent' => $capacityUsagePercent,
                'status' => $capacityUsagePercent > 85 ? 'Kritis / Mendekati Kapasitas Puncak Workshop' : 'Optimal & Terkendali',
            ],
            'categorySales' => $isSuperAdmin ? $categorySales : [],
            'costDistribution' => $isSuperAdmin ? $costDistribution : [],
            'recentTransactions' => $isSuperAdmin ? $recentTransactions : [],
            'allTransactions' => $isSuperAdmin ? $allTransactions : [],
            'topProducts' => $topProducts,
            'trendHistory' => $isSuperAdmin ? $trendHistory : [],
            'inventoryStats' => [
                'totalStockUnits' => (int) Product::sum('current_stock'),
                'totalProductsCount' => (int) Product::count(),
                'lowStockCount' => count($lowStockProducts),
                'lowStockProducts' => $lowStockProducts,
            ],
            'staffPerformance' => $isSuperAdmin ? $staffPerformance : [],
            'alerts' => $alerts,
            'quickSimulatorBaseline' => $isSuperAdmin ? $quickSimulatorBaseline : null,
            'adminOperationsData' => $adminOperationsData,
            'salesData' => $salesData,
            'craftsmanData' => $craftsmanData,
        ]);
    }
}
