<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SalesController extends Controller
{
    /**
     * Display sales intelligence, transactions history, and deal price analysis.
     */
    public function index(): Response
    {
        // 1. Ambil seluruh transaksi dengan relasi item dan produk
        $transactions = Transaction::with(['transactionItems.product'])
            ->latest('transaction_date')
            ->get()
            ->map(function ($tx) {
                $discountPct = $tx->total_amount > 0
                    ? round(($tx->total_discount / $tx->total_amount) * 100, 1)
                    : 0.0;

                return [
                    'id' => $tx->id,
                    'invoice_number' => $tx->invoice_number,
                    'transaction_date' => $tx->transaction_date?->format('d M Y · H:i'),
                    'customer_name' => $tx->customer_name ?? 'Pelanggan Showroom',
                    'sales_person_name' => $tx->sales_person_name ?? 'Staff Workshop',
                    'payment_method' => $tx->payment_method ?? 'Transfer Bank',
                    'total_amount' => (float) $tx->total_amount,
                    'total_discount' => (float) $tx->total_discount,
                    'net_amount' => (float) $tx->net_amount,
                    'discount_percent' => $discountPct,
                    'notes' => $tx->notes,
                    'items' => $tx->transactionItems->map(fn ($item) => [
                        'id' => $item->id,
                        'product_name' => $item->product?->name ?? 'Produk Kulit',
                        'product_sku' => $item->product?->sku ?? '-',
                        'unit' => $item->product?->unit ?? 'pasang',
                        'quantity' => (int) $item->quantity,
                        'base_price' => (float) $item->base_price,
                        'deal_price' => (float) $item->deal_price,
                        'cost_price' => (float) $item->cost_price,
                        'subtotal' => (float) $item->subtotal,
                        'discount_per_unit' => (float) ($item->base_price - $item->deal_price),
                        'notes' => $item->notes,
                    ]),
                ];
            });

        // 2. Metrik Analisis Negosiasi (Deal Price vs Base Price)
        $totalGrossRevenue = (float) Transaction::sum('total_amount');
        $totalNetRevenue = (float) Transaction::sum('net_amount');
        $totalNegotiatedDiscount = (float) Transaction::sum('total_discount');
        $avgDiscountPercent = $totalGrossRevenue > 0
            ? round(($totalNegotiatedDiscount / $totalGrossRevenue) * 100, 1)
            : 0.0;

        // 3. Produk aktif untuk opsi input transaksi kasir / pesanan baru
        $availableProducts = Product::where('is_active', true)
            ->where('current_stock', '>', 0)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'category', 'unit', 'base_price', 'cost_price', 'current_stock']);

        return Inertia::render('Sales/Index', [
            'transactions' => $transactions,
            'metrics' => [
                'totalGrossRevenue' => $totalGrossRevenue,
                'totalNetRevenue' => $totalNetRevenue,
                'totalDiscount' => $totalNegotiatedDiscount,
                'avgDiscountPercent' => $avgDiscountPercent,
                'totalTransactions' => $transactions->count(),
            ],
            'availableProducts' => $availableProducts,
        ]);
    }

    /**
     * Store a newly created sales transaction with deal price negotiations.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'sales_person_name' => 'required|string|max:255',
            'payment_method' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.deal_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated): void {
            $count = Transaction::count() + 1;
            $invoiceNumber = sprintf('INV-OBZ-%s-%04d', now()->format('Ym'), $count);

            $totalAmount = 0.0;
            $totalDiscount = 0.0;
            $netAmount = 0.0;

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'transaction_date' => now(),
                'customer_name' => $validated['customer_name'],
                'sales_person_name' => $validated['sales_person_name'],
                'payment_method' => $validated['payment_method'],
                'total_amount' => 0,
                'total_discount' => 0,
                'net_amount' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                $qty = (int) $itemData['quantity'];
                $basePrice = (float) $product->base_price;
                $dealPrice = (float) $itemData['deal_price'];
                $costPrice = (float) $product->cost_price;

                $gross = $basePrice * $qty;
                $subtotal = $dealPrice * $qty;
                $discountTotal = max(0, $gross - $subtotal);

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'base_price' => $basePrice,
                    'deal_price' => $dealPrice,
                    'cost_price' => $costPrice,
                    'subtotal' => $subtotal,
                    'notes' => $discountTotal > 0 ? 'Negosiasi harga saat order' : null,
                ]);

                // Kurangi stok produk
                $product->decrement('current_stock', min($product->current_stock, $qty));

                $totalAmount += $gross;
                $totalDiscount += $discountTotal;
                $netAmount += $subtotal;
            }

            $transaction->update([
                'total_amount' => $totalAmount,
                'total_discount' => $totalDiscount,
                'net_amount' => $netAmount,
            ]);
        });

        return redirect()->route('sales.index');
    }
}
