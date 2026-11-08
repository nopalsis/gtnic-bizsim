<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    /**
     * Display inventory catalog, low stock alerts, and waste tracker.
     */
    public function index(): Response
    {
        // 1. Katalog Produk & Material dengan status stok dan tipe kategori
        $categoriesList = Category::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $rawCategories = $categoriesList->where('type', 'raw')->pluck('name')->toArray();
        if (empty($rawCategories)) {
            $rawCategories = ['Bahan Baku Kulit', 'Komponen Sol', 'Bahan Pendukung'];
        }

        $products = Product::orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($rawCategories) {
                $isLowStock = $product->current_stock <= $product->min_stock_alert;
                $stockValuation = (float) ($product->current_stock * $product->cost_price);
                $isRaw = in_array($product->category, $rawCategories, true);

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category,
                    'category_type' => $isRaw ? 'raw' : 'finished',
                    'unit' => $product->unit,
                    'base_price' => (float) $product->base_price,
                    'cost_price' => (float) $product->cost_price,
                    'current_stock' => (int) $product->current_stock,
                    'min_stock_alert' => (int) $product->min_stock_alert,
                    'is_active' => (bool) $product->is_active,
                    'is_low_stock' => $isLowStock,
                    'valuation' => $stockValuation,
                ];
            });

        // 2. Riwayat Mutasi Stok Keseluruhan
        $recentMovements = StockMovement::with('product')
            ->latest('movement_date')
            ->latest('id')
            ->take(25)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'product_id' => $m->product_id,
                'product_name' => $m->product?->name ?? 'Produk Kulit',
                'product_sku' => $m->product?->sku ?? '-',
                'unit' => $m->product?->unit ?? 'pasang',
                'type' => $m->type,
                'quantity' => (int) $m->quantity,
                'cost_impact' => $m->type === 'waste' ? (float) ($m->quantity * ($m->product?->cost_price ?? 0)) : 0,
                'date' => $m->movement_date?->format('d M Y') ?? (string) $m->movement_date,
                'notes' => $m->notes,
            ]);

        // 3. Log Pencatatan Limbah Terkini (Khusus type = 'waste' untuk audit panel bawah)
        $recentWasteLogs = StockMovement::with('product')
            ->where('type', 'waste')
            ->latest('movement_date')
            ->latest('id')
            ->take(6)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'product_name' => $m->product?->name ?? 'Material Kulit',
                'product_sku' => $m->product?->sku ?? '-',
                'unit' => $m->product?->unit ?? 'sq ft',
                'quantity' => (int) $m->quantity,
                'cost_impact' => (float) ($m->quantity * ($m->product?->cost_price ?? 0)),
                'date' => $m->movement_date?->format('d M Y') ?? (string) $m->movement_date,
                'notes' => $m->notes ?? 'Residu cacat potong pola atau seset',
            ]);

        // 4. Rekap Total Inventaris & Finansial Gudang
        $totalStockUnits = (int) Product::sum('current_stock');
        $totalStockValuation = (float) Product::sum(DB::raw('current_stock * cost_price'));
        $totalProductsCount = $products->count();

        $totalWasteUnits = (int) StockMovement::where('type', 'waste')->sum('quantity');
        $totalWasteCost = (float) StockMovement::where('stock_movements.type', 'waste')
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * products.cost_price'));

        $totalProductionInUnits = (int) StockMovement::where('type', 'in')->sum('quantity');
        $historicalWasteRate = $totalProductionInUnits > 0
            ? round(($totalWasteUnits / $totalProductionInUnits) * 100, 1)
            : 0;

        $lowStockProducts = $products->filter(fn ($p) => $p['is_low_stock'])->values();
        $lowStockCount = $lowStockProducts->count();
        $lowStockNames = $lowStockProducts->pluck('name')->take(4)->implode(', ');

        // 5. Advisori Pintar Pemanfaatan Limbah Kulit
        $advisory = [
            'title' => 'Gunakan Residu Potongan Kulit Nabati untuk Gantungan Kunci & Dompet Kartu',
            'description' => 'Residu sisa potongan lembaran kulit berukuran >10cm² dapat diolah kembali menjadi produk turunan mikro senilai Rp 350.000 dibanding dicatat sebagai kerugian murni.',
            'efficiency_gain' => '+18.2%',
            'action_label' => 'Simulasikan Daur Residu',
            'action_url' => '/simulator',
        ];

        return Inertia::render('Inventory/Index', [
            'products' => $products,
            'recentMovements' => $recentMovements,
            'recentWasteLogs' => $recentWasteLogs,
            'stats' => [
                'totalStockUnits' => $totalStockUnits,
                'totalStockValuation' => $totalStockValuation,
                'totalProductsCount' => $totalProductsCount,
                'lowStockCount' => $lowStockCount,
                'lowStockNames' => $lowStockNames ?: 'Semua stok dalam ambang aman',
                'totalWasteUnits' => $totalWasteUnits,
                'totalWasteCost' => $totalWasteCost,
                'historicalWasteRate' => $historicalWasteRate,
                'valuationChangePct' => '+5.4% vs bulan lalu',
                'wasteChangePct' => '-4.2% efisiensi sisa membaik',
            ],
            'advisory' => $advisory,
            'categories' => $categoriesList->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'prefix_sku' => $c->prefix_sku,
            ]),
        ]);
    }

    /**
     * Store a newly created product / raw material.
     */
    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:30',
            'cost_price' => 'required|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0',
            'current_stock' => 'required|integer|min:0',
            'min_stock_alert' => 'required|integer|min:0',
        ]);

        $isRaw = Category::where('name', $validated['category'])->where('type', 'raw')->exists()
            || in_array($validated['category'], ['Bahan Baku Kulit', 'Komponen Sol', 'Bahan Pendukung'], true);

        $validated['base_price'] = $isRaw ? 0.00 : (float) ($validated['base_price'] ?? 0);

        if (empty($validated['sku'])) {
            $foundCat = Category::where('name', $validated['category'])->first();
            $prefix = $foundCat?->prefix_sku ?: match ($validated['category']) {
                'Bahan Baku Kulit' => 'RAW-LEA',
                'Komponen Sol' => 'CMP-SOL',
                'Bahan Pendukung' => 'SUP-CHM',
                'Sepatu Pria', 'Sepatu Wanita' => 'FG-SHO',
                'Sandal Kulit Pria', 'Sandal Kulit Wanita' => 'FG-SND',
                default => 'PRD',
            };
            $validated['sku'] = $prefix . '-' . strtoupper(Str::random(4));
        }

        DB::transaction(function () use ($validated): void {
            $product = Product::create([
                'sku' => $validated['sku'],
                'name' => $validated['name'],
                'category' => $validated['category'],
                'unit' => $validated['unit'],
                'cost_price' => $validated['cost_price'],
                'base_price' => $validated['base_price'],
                'current_stock' => $validated['current_stock'],
                'min_stock_alert' => $validated['min_stock_alert'],
                'is_active' => true,
            ]);

            if ($product->current_stock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $product->current_stock,
                    'movement_date' => now()->toDateString(),
                    'notes' => 'Stok awal registrasi item inventaris baru',
                ]);
            }
        });

        return redirect()->route('inventory.index');
    }

    /**
     * Update an existing product / raw material.
     */
    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:30',
            'cost_price' => 'required|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0',
            'min_stock_alert' => 'required|integer|min:0',
        ]);

        $isRaw = Category::where('name', $validated['category'])->where('type', 'raw')->exists()
            || in_array($validated['category'], ['Bahan Baku Kulit', 'Komponen Sol', 'Bahan Pendukung'], true);

        $validated['base_price'] = $isRaw ? 0.00 : (float) ($validated['base_price'] ?? 0);

        $product->update($validated);

        return redirect()->route('inventory.index');
    }

    /**
     * Delete or deactivate a product.
     */
    public function destroyProduct(Product $product): RedirectResponse
    {
        // Jika memiliki transaksi, nonaktifkan saja untuk menjaga integritas data historis
        if ($product->transactionItems()->exists()) {
            $product->update(['is_active' => false]);
        } else {
            $product->stockMovements()->delete();
            $product->delete();
        }

        return redirect()->route('inventory.index');
    }

    /**
     * Quick stock mutation (+ / -) from workshop or warehouse.
     */
    public function recordMutation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($validated): void {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $qty = (int) $validated['quantity'];

            if ($validated['type'] === 'in') {
                $product->increment('current_stock', $qty);
            } elseif ($validated['type'] === 'out') {
                $actualDeduction = min($product->current_stock, $qty);
                $product->decrement('current_stock', $actualDeduction);
            } elseif ($validated['type'] === 'adjustment') {
                $product->current_stock = $qty;
                $product->save();
            }

            StockMovement::create([
                'product_id' => $product->id,
                'type' => $validated['type'],
                'quantity' => $qty,
                'movement_date' => now()->toDateString(),
                'notes' => $validated['notes'],
            ]);
        });

        return redirect()->route('inventory.index');
    }

    /**
     * Record a new waste / production reject event.
     */
    public function recordWaste(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'movement_date' => 'required|date',
            'notes' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($validated): void {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $qty = (int) $validated['quantity'];

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'waste',
                'quantity' => $qty,
                'movement_date' => $validated['movement_date'],
                'notes' => $validated['notes'],
            ]);

            // Kurangi stok jika ada
            if ($product->current_stock > 0) {
                $product->decrement('current_stock', min($product->current_stock, $qty));
            }
        });

        return redirect()->route('inventory.index');
    }

    /**
     * Record incoming stock from workshop production.
     */
    public function recordProductionIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'movement_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated): void {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $qty = (int) $validated['quantity'];

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $qty,
                'movement_date' => $validated['movement_date'],
                'notes' => $validated['notes'] ?? 'Batch produksi baru selesai dari pengrajin workshop',
            ]);

            $product->increment('current_stock', $qty);
        });

        return redirect()->route('inventory.index');
    }
}
