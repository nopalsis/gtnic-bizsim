<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    /**
     * Display inventory catalog, low stock alerts, and waste tracker.
     */
    public function index(): Response
    {
        // 1. Katalog Produk dengan status stok
        $products = Product::orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $isLowStock = $product->current_stock <= $product->min_stock_alert;
                $stockValuation = (float) ($product->current_stock * $product->cost_price);

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category,
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

        // 2. Riwayat Pergerakan Stok & Waste
        $recentMovements = StockMovement::with('product')
            ->latest('movement_date')
            ->take(20)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'product_name' => $m->product?->name ?? 'Produk Kulit',
                'product_sku' => $m->product?->sku ?? '-',
                'unit' => $m->product?->unit ?? 'pasang',
                'type' => $m->type,
                'quantity' => (int) $m->quantity,
                'cost_impact' => $m->type === 'waste' ? (float) ($m->quantity * ($m->product?->cost_price ?? 0)) : 0,
                'date' => $m->movement_date?->format('d M Y') ?? (string) $m->movement_date,
                'notes' => $m->notes,
            ]);

        // 3. Rekap Total Inventaris & Waste
        $totalStockUnits = (int) Product::sum('current_stock');
        $totalStockValuation = (float) Product::sum(DB::raw('current_stock * cost_price'));
        $totalWasteUnits = (int) StockMovement::where('type', 'waste')->sum('quantity');
        $totalWasteCost = (float) StockMovement::where('stock_movements.type', 'waste')
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * products.cost_price'));
        $lowStockCount = $products->filter(fn ($p) => $p['is_low_stock'])->count();

        return Inertia::render('Inventory/Index', [
            'products' => $products,
            'recentMovements' => $recentMovements,
            'stats' => [
                'totalStockUnits' => $totalStockUnits,
                'totalStockValuation' => $totalStockValuation,
                'totalWasteUnits' => $totalWasteUnits,
                'totalWasteCost' => $totalWasteCost,
                'lowStockCount' => $lowStockCount,
            ],
        ]);
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
