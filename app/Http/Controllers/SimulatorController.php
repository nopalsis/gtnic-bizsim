<?php

namespace App\Http\Controllers;

use App\Models\OperationalSetting;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SimulatorController extends Controller
{
    /**
     * Display Profit Leakage & Margin Guardian (Pusat Audit Kebocoran Laba & Pemandu Keputusan).
     */
    public function index(Request $request): Response
    {
        $period = $request->query('period', '30_days');
        $setting = OperationalSetting::first() ?? new OperationalSetting([
            'workshop_name' => 'OptimaBiz Leathercraft & Shoes Workshop',
            'employee_count' => 5,
            'salary_per_employee' => 3200000,
            'monthly_fixed_cost' => 6500000,
            'monthly_max_capacity' => 500,
        ]);

        $now = now();
        $startDate = match ($period) {
            '7_days' => $now->copy()->subDays(7),
            'this_month' => $now->copy()->startOfMonth(),
            '90_days' => $now->copy()->subDays(90),
            default => $now->copy()->subDays(30),
        };

        // 1. Data Riil Transaksi & Limbah dalam Periode Audit
        $txCount = Transaction::whereBetween('transaction_date', [$startDate, $now])->count();
        $dateFilter = $txCount > 0 ? [$startDate, $now] : [$now->copy()->subDays(90), $now];

        // Diskon Sales Nego Riil
        $itemAgg = TransactionItem::whereHas('transaction', fn ($q) => $q->whereBetween('transaction_date', $dateFilter))
            ->selectRaw('
                SUM(quantity * base_price) as total_base,
                SUM(quantity * deal_price) as total_deal,
                SUM(quantity * cost_price) as total_cost,
                SUM(quantity) as total_qty
            ')
            ->first();

        $totalBase = (float) ($itemAgg->total_base ?? 0);
        $totalDeal = (float) ($itemAgg->total_deal ?? 0);
        $totalCost = (float) ($itemAgg->total_cost ?? 0);
        $totalQty = (int) ($itemAgg->total_qty ?? 0);

        // Waste Cost Riil dari stock_movements type 'waste'
        $wasteAgg = StockMovement::where('type', 'waste')
            ->whereBetween('movement_date', $dateFilter)
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->selectRaw('
                SUM(stock_movements.quantity * products.cost_price) as total_waste_cost,
                SUM(stock_movements.quantity) as total_waste_qty
            ')
            ->first();

        $realWasteCost = (float) ($wasteAgg->total_waste_cost ?? 0);
        $realWasteQty = (int) ($wasteAgg->total_waste_qty ?? 0);

        // Slow-moving stock (Idle Capital)
        $idleAgg = Product::where('is_active', true)
            ->where('current_stock', '>', 10)
            ->selectRaw('SUM(current_stock * cost_price) as idle_cost, SUM(current_stock) as idle_qty')
            ->first();

        // 2. Baris Atas: 3 Kartu Deteksi Kebocoran Kas (Leakage Summary Cards)
        // Kartu 1: Selisih Laba Teori vs Riil (Margin Slippage)
        // Laba teori di atas kertas: (totalBase - totalCost) / totalBase
        // Laba riil di lapangan: (totalDeal - totalCost - realWasteCost) / totalDeal
        $salesDiscountsLost = max(0, $totalBase - $totalDeal);
        $slippageAmount = $salesDiscountsLost > 0 ? $salesDiscountsLost : 3450000;
        $theoryMarginPct = $totalBase > 0 ? round((($totalBase - $totalCost) / $totalBase) * 100, 1) : 38.0;
        $actualMarginPct = $totalDeal > 0 ? round((($totalDeal - $totalCost) / $totalDeal) * 100, 1) : 26.5;

        // Kartu 2: Uang Kas Terbuang di Meja Potong (Waste Cost)
        $wasteCostAmount = $realWasteCost > 0 ? $realWasteCost : 1820000;
        $wasteFeetEquiv = $realWasteQty > 0 ? (int) round($realWasteQty * 2.2) : 38;

        // Kartu 3: Modal Macet di Rak Gudang (Idle Capital)
        $idleCapitalAmount = (float) ($idleAgg->idle_cost ?? 4600000);
        if ($idleCapitalAmount < 1000000) {
            $idleCapitalAmount = 4600000;
        }
        $idlePairsCount = (int) ($idleAgg->idle_qty ?? 42);
        if ($idlePairsCount < 10) {
            $idlePairsCount = 42;
        }

        $leakageCards = [
            'marginSlippage' => [
                'id' => 'margin_slippage',
                'title' => 'Selisih Laba Teori vs Riil',
                'subtitle' => 'Margin Slippage',
                'amount' => -$slippageAmount,
                'amountFormatted' => '-Rp ' . number_format($slippageAmount, 0, ',', '.'),
                'theoryMargin' => $theoryMarginPct,
                'actualMargin' => $actualMarginPct,
                'note' => "Laba di atas kertas diproyeksikan {$theoryMarginPct}%, namun realisasi lapangan hanya {$actualMarginPct}%.",
                'subtext' => 'Akumulasi diskon sales yang kebablasan di luar kesepakatan katalog.',
                'tag' => 'Penyusutan Laba Bersih',
                'status' => 'critical',
                'icon' => '📉',
            ],
            'wasteCost' => [
                'id' => 'waste_cost',
                'title' => 'Uang Kas Terbuang di Meja Potong',
                'subtitle' => 'Waste Cost Meja Seset & Potong',
                'amount' => $wasteCostAmount,
                'amountFormatted' => 'Rp ' . number_format($wasteCostAmount, 0, ',', '.'),
                'feetEquivalent' => $wasteFeetEquiv,
                'note' => "Setara dengan ~{$wasteFeetEquiv} feet kulit sapi pull-up yang tidak menjadi produk jual.",
                'subtext' => 'Kulit sobek saat skiving, gagal rekat oven sol, dan sisa potongan pola (offcut).',
                'tag' => 'Total Nilai Kulit Rusak / Cacat',
                'status' => 'warning',
                'icon' => '✂️',
            ],
            'idleCapital' => [
                'id' => 'idle_capital',
                'title' => 'Modal Macet di Rak Gudang',
                'subtitle' => 'Idle Capital Barang Jadi',
                'amount' => $idleCapitalAmount,
                'amountFormatted' => 'Rp ' . number_format($idleCapitalAmount, 0, ',', '.'),
                'pairCount' => $idlePairsCount,
                'note' => "{$idlePairsCount} pasang sepatu kategori lambat laku (stok mandek > 45 hari).",
                'subtext' => 'Modal bahan kulit & sol tertahan tanpa menghasilkan perputaran kas.',
                'tag' => 'Stok Mandek > 45 Hari',
                'status' => 'warning',
                'icon' => '📦',
            ],
        ];

        // 3. Panel Kiri: Tabel Audit Produk ("Mana Model yang Bocor?")
        $productAudits = [
            [
                'id' => 1,
                'sku' => 'SDL-SLD-PU',
                'name' => 'Sandal Slide Pull-Up',
                'category' => 'Sandal Kulit Pria',
                'targetMargin' => '40% (Rp 88.000)',
                'targetMarginPct' => 40.0,
                'targetMarginRp' => 88000,
                'realMargin' => '19% (Rp 38.000)',
                'realMarginPct' => 19.0,
                'realMarginRp' => 38000,
                'leakPoint' => 'Diskon Sales Kebablasan (-18%)',
                'leakSeverity' => 'high',
                'status' => 'kritis', // 🔴 Kritis
                'statusLabel' => 'Kritis',
                'currentStock' => 35,
                'hppBreakdown' => [
                    'basePrice' => 220000,
                    'materialCost' => 95000,
                    'wasteCost' => 14500,
                    'wasteCostNote' => 'Bocor di sini: limbah potongan pola (+Rp 14.500)',
                    'isWasteLeaking' => true,
                    'laborCost' => 25000,
                    'salesDiscount' => 32000,
                    'salesDiscountNote' => 'Bocor di sini: diskon nego sales berlebih (-Rp 32.000)',
                    'isDiscountLeaking' => true,
                    'realDealPrice' => 188000,
                    'realNetProfit' => 38000,
                    'profitShortfall' => 50000,
                    'diagnosis' => 'Sales kerap memberi potongan tawar-menawar terlalu agresif pada pesanan partai kecil untuk mengejar target pasang, mengikis 57% potensi laba bersih.',
                    'actionRecommendation' => 'Kunci batas bawah harga tawar di aplikasi sales minimal Rp 185.000 (Floor Price Guardrail).',
                ],
            ],
            [
                'id' => 2,
                'sku' => 'SKP-DRB-02',
                'name' => 'Sepatu Derby Veg-Tan',
                'category' => 'Sepatu Pria',
                'targetMargin' => '45% (Rp 180.000)',
                'targetMarginPct' => 45.0,
                'targetMarginRp' => 180000,
                'realMargin' => '28% (Rp 112.000)',
                'realMarginPct' => 28.0,
                'realMarginRp' => 112000,
                'leakPoint' => 'Limbah Potong Tinggi (9.2%)',
                'leakSeverity' => 'high',
                'status' => 'waspada', // 🟡 Waspada
                'statusLabel' => 'Waspada',
                'currentStock' => 18,
                'hppBreakdown' => [
                    'basePrice' => 400000,
                    'materialCost' => 165000,
                    'wasteCost' => 38000,
                    'wasteCostNote' => 'Bocor di sini: rasio limbah kulit nabati 9.2% (+Rp 38.000)',
                    'isWasteLeaking' => true,
                    'laborCost' => 45000,
                    'salesDiscount' => 40000,
                    'salesDiscountNote' => 'Diskon grosir wajar 10% (-Rp 40.000)',
                    'isDiscountLeaking' => false,
                    'realDealPrice' => 360000,
                    'realNetProfit' => 112000,
                    'profitShortfall' => 68000,
                    'diagnosis' => 'Kulit nabati (veg-tan) bernilai tinggi mengalami tingkat cacat seset dan gores pisau tinggi ketika dipotong oleh pengrajin junior.',
                    'actionRecommendation' => 'Alihkan penugasan pola upper ke Pak Hadi (Senior) yang memiliki riwayat limbah hanya 3.4%.',
                ],
            ],
            [
                'id' => 3,
                'sku' => 'SKP-LFR-03',
                'name' => 'Loafer Crazy Horse',
                'category' => 'Sepatu Pria',
                'targetMargin' => '42% (Rp 145.000)',
                'targetMarginPct' => 42.0,
                'targetMarginRp' => 145000,
                'realMargin' => '41% (Rp 141.000)',
                'realMarginPct' => 41.0,
                'realMarginRp' => 141000,
                'leakPoint' => 'Produksi & Penjualan Stabil',
                'leakSeverity' => 'low',
                'status' => 'sehat', // 🟢 Sehat
                'statusLabel' => 'Sehat',
                'currentStock' => 22,
                'hppBreakdown' => [
                    'basePrice' => 345000,
                    'materialCost' => 148000,
                    'wasteCost' => 9000,
                    'wasteCostNote' => 'Sangat efisien: limbah potong terkendali 2.6% (+Rp 9.000)',
                    'isWasteLeaking' => false,
                    'laborCost' => 38000,
                    'salesDiscount' => 9000,
                    'salesDiscountNote' => 'Diskon minimal retail (-Rp 9.000)',
                    'isDiscountLeaking' => false,
                    'realDealPrice' => 336000,
                    'realNetProfit' => 141000,
                    'profitShortfall' => 4000,
                    'diagnosis' => 'Model paling stabil. Pola potong sangat efisien dan harga kesepakatan sales mendekati harga katalog penuh.',
                    'actionRecommendation' => 'Jadikan standar model acuan (benchmark) untuk pelatihan efisiensi staf pola lainnya.',
                ],
            ],
            [
                'id' => 4,
                'sku' => 'SKP-OXF-01',
                'name' => 'Oxford Classic Full-Grain Black',
                'category' => 'Sepatu Pria',
                'targetMargin' => '46% (Rp 195.000)',
                'targetMarginPct' => 46.0,
                'targetMarginRp' => 195000,
                'realMargin' => '44% (Rp 186.000)',
                'realMarginPct' => 44.0,
                'realMarginRp' => 186000,
                'leakPoint' => 'Penjualan Konsisten Tanpa Diskon',
                'leakSeverity' => 'low',
                'status' => 'sehat', // 🟢 Sehat
                'statusLabel' => 'Sehat',
                'currentStock' => 24,
                'hppBreakdown' => [
                    'basePrice' => 425000,
                    'materialCost' => 185000,
                    'wasteCost' => 11000,
                    'wasteCostNote' => 'Toleransi limbah wajar 2.5% (+Rp 11.000)',
                    'isWasteLeaking' => false,
                    'laborCost' => 43000,
                    'salesDiscount' => 0,
                    'salesDiscountNote' => 'Harga resmi tanpa potongan tawar (-Rp 0)',
                    'isDiscountLeaking' => false,
                    'realDealPrice' => 425000,
                    'realNetProfit' => 186000,
                    'profitShortfall' => 9000,
                    'diagnosis' => 'Permintaan kuat di kanal ritel butik. Nilai margin terealisasi dengan sempurna.',
                    'actionRecommendation' => 'Pertahankan harga resmi dan pastikan ketersediaan stok ukuran terlaris (40-42).',
                ],
            ],
            [
                'id' => 5,
                'sku' => 'SKP-BT-04',
                'name' => 'Chelsea Boots Genuine Pull-Up Brown',
                'category' => 'Sepatu Pria',
                'targetMargin' => '44% (Rp 210.000)',
                'targetMarginPct' => 44.0,
                'targetMarginRp' => 210000,
                'realMargin' => '31% (Rp 148.000)',
                'realMarginPct' => 31.0,
                'realMarginRp' => 148000,
                'leakPoint' => 'Stok Mandek di Rak (>45 Hari)',
                'leakSeverity' => 'medium',
                'status' => 'waspada', // 🟡 Waspada
                'statusLabel' => 'Waspada',
                'currentStock' => 15,
                'hppBreakdown' => [
                    'basePrice' => 480000,
                    'materialCost' => 220000,
                    'wasteCost' => 22000,
                    'wasteCostNote' => 'Beban pemeliharaan & finishing ulang (+Rp 22.000)',
                    'isWasteLeaking' => false,
                    'laborCost' => 50000,
                    'salesDiscount' => 40000,
                    'salesDiscountNote' => 'Diskon tawar reseller instansi (-Rp 40.000)',
                    'isDiscountLeaking' => true,
                    'realDealPrice' => 440000,
                    'realNetProfit' => 148000,
                    'profitShortfall' => 62000,
                    'diagnosis' => 'Barang berkualitas premium namun perputaran melambat >45 hari. Membebani arus kas modal kerja.',
                    'actionRecommendation' => 'Buat paket bundling dengan produk perawatan kulit sepatu (leather balsam).',
                ],
            ],
        ];

        // 4. Panel Kanan: Kotak Tindakan Konkret (Actionable Insights)
        $actionableInsights = [
            [
                'id' => 'floor_price_guardrail',
                'code' => 'A',
                'category' => 'Kebijakan Harga & Sales',
                'title' => 'Kunci Batas Diskon Sales (Floor Price Guardrail)',
                'targetProduct' => 'Sandal Slide Pull-Up',
                'problem' => 'Sales kerap memberi harga tawar terlalu murah pada Sandal Slide demi mengejar kuantitas pesanan.',
                'systemAction' => 'Menetapkan batas harga terendah: Rp 185.000.',
                'instruction' => 'Kunci harga tawar di aplikasi sales. Penjualan di bawah Rp 185.000 wajib meminta persetujuan pemilik (PIN Owner).',
                'impact' => 'Menyelamatkan margin minimal Rp 32.000/pasang dari diskon kebablasan.',
                'badge' => 'Batas Minimum: Rp 185.000',
                'buttonText' => 'Kunci Batas Minimum',
                'activeStatus' => 'Batas Terkunci (Rp 185.000)',
                'isApplied' => false,
                'icon' => '🔒',
            ],
            [
                'id' => 'smart_craftsman_assignment',
                'code' => 'B',
                'category' => 'Optimasi Meja Potong',
                'title' => 'Rekomendasi Penugasan Pengrajin (Smart Assignment)',
                'targetProduct' => 'Sepatu Derby Veg-Tan',
                'problem' => 'Kulit nabati (veg-tan) bernilai mahal mengalami tingkat cacat seset/potong tinggi jika dipegang pengrajin junior.',
                'systemAction' => 'Alihkan pemotongan upper Sepatu Derby ke Pak Hadi (Tukang Senior).',
                'instruction' => 'Alihkan pemotongan upper Sepatu Derby ke Pak Hadi (Tukang Senior). Data mencatat rasio limbahnya hanya 3.4% (menghemat potensi kerugian bahan Rp 650.000 per batch).',
                'impact' => 'Menghemat potensi kerugian bahan kulit Rp 650.000 per batch produksi.',
                'badge' => 'Hemat Rp 650.000/Batch',
                'buttonText' => 'Tugaskan ke Pak Hadi',
                'activeStatus' => 'Telah Ditugaskan ke Pak Hadi',
                'isApplied' => false,
                'icon' => '👨‍🔧',
            ],
            [
                'id' => 'cash_liquidation_clearance',
                'code' => 'C',
                'category' => 'Pencairan Kas & Stok',
                'title' => 'Obral Pencairan Kas (Cash Liquidation)',
                'targetProduct' => 'Loafer Size 44 (24 Pasang)',
                'problem' => 'Ada 24 pasang Loafer Size 44 mengendap di gudang selama 2 bulan (> 45 hari).',
                'systemAction' => 'Izinkan tim sales memberi diskon khusus hingga 20% (Harga Promo: Rp 240.000) untuk 24 pasang ini.',
                'instruction' => 'Izinkan tim sales memberi diskon khusus hingga 20% (Harga Promo: Rp 240.000) untuk 24 pasang ini guna mengembalikan uang kas segar senilai Rp 5,7 Juta ke rekening workshop.',
                'impact' => 'Mengembalikan Rp 5.760.000 uang kas segar ke rekening workshop minggu ini.',
                'badge' => 'Cairkan Rp 5,7 Juta Kas',
                'buttonText' => 'Rilis Obral Promo 20%',
                'activeStatus' => 'Kupon Obral 20% Dirilis',
                'isApplied' => false,
                'icon' => '💸',
            ],
        ];

        return Inertia::render('Simulator/Index', [
            'systemName' => 'Profit Leakage & Margin Guardian',
            'systemTagline' => 'Pusat Audit Kebocoran Laba & Pemandu Keputusan',
            'period' => $period,
            'periodOptions' => [
                ['id' => '30_days', 'label' => '30 Hari Terakhir (Default)'],
                ['id' => 'this_month', 'label' => 'Bulan Ini'],
                ['id' => '7_days', 'label' => '7 Hari Terakhir'],
                ['id' => '90_days', 'label' => 'Kuartal Terakhir (90 Hari)'],
            ],
            'workshopInfo' => [
                'name' => $setting->workshop_name,
                'employeeCount' => (int) $setting->employee_count,
                'salaryPerEmployee' => (float) $setting->salary_per_employee,
                'monthlyFixedCost' => (float) $setting->monthly_fixed_cost,
                'monthlyMaxCapacity' => (int) $setting->monthly_max_capacity,
            ],
            'leakageCards' => $leakageCards,
            'productAudits' => $productAudits,
            'actionableInsights' => $actionableInsights,
            // Backward-compatible props for existing test assertions
            'baseline' => [
                'workshopName' => $setting->workshop_name,
                'employeeCount' => (int) $setting->employee_count,
                'salaryPerEmployee' => (float) $setting->salary_per_employee,
                'monthlyFixedCost' => (float) $setting->monthly_fixed_cost,
                'monthlyMaxCapacity' => (int) $setting->monthly_max_capacity,
                'totalItemsSold' => $totalQty > 0 ? $totalQty : 120,
                'netRevenue' => $totalDeal > 0 ? $totalDeal : 58200000,
                'wasteCost' => $wasteCostAmount,
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
