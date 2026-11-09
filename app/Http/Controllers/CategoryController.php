<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $type = $request->string('type')->trim()->toString() ?: 'all';

        $query = Category::query()
            ->withCount(['products as total_products' => function ($q): void {
                $q->where('is_active', true);
            }])
            ->withCount('products');

        if (! empty($search)) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('prefix_sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (in_array($type, ['finished', 'raw'], true)) {
            $query->where('type', $type);
        }

        $categories = $query->orderBy('type')->orderBy('name')->get()->map(function ($cat) {
            $stockSum = Product::where('category', $cat->name)->sum('current_stock');

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'type' => $cat->type,
                'type_label' => $cat->type === 'finished' ? 'Sepatu & Sandal Jadi' : 'Bahan Baku & Sol',
                'prefix_sku' => $cat->prefix_sku ?: 'PRD',
                'description' => $cat->description ?: '-',
                'is_active' => (bool) $cat->is_active,
                'products_count' => (int) $cat->products_count,
                'active_products_count' => (int) $cat->total_products,
                'total_stock' => (int) $stockSum,
                'created_at' => $cat->created_at?->format('d M Y'),
            ];
        });

        $allCategories = Category::all();
        $counts = [
            'all' => $allCategories->count(),
            'finished' => $allCategories->where('type', 'finished')->count(),
            'raw' => $allCategories->where('type', 'raw')->count(),
            'active' => $allCategories->where('is_active', true)->count(),
            'total_products' => Product::where('is_active', true)->count(),
        ];

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'counts' => $counts,
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'type' => 'required|string|in:finished,raw',
            'prefix_sku' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah terdaftar.',
            'type.required' => 'Jenis kategori harus dipilih.',
            'type.in' => 'Jenis kategori harus Produk Jadi atau Bahan Baku.',
        ]);

        $prefix = ! empty($validated['prefix_sku'])
            ? strtoupper(trim($validated['prefix_sku']))
            : ($validated['type'] === 'finished' ? 'FG-PRD' : 'RAW-MAT');

        Category::create([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']),
            'type' => $validated['type'],
            'prefix_sku' => $prefix,
            'description' => isset($validated['description']) ? trim($validated['description']) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('categories.index');
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'type' => 'required|string|in:finished,raw',
            'prefix_sku' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori ini sudah digunakan.',
            'type.required' => 'Jenis kategori harus dipilih.',
        ]);

        $oldName = $category->name;
        $prefix = ! empty($validated['prefix_sku'])
            ? strtoupper(trim($validated['prefix_sku']))
            : $category->prefix_sku;

        $category->update([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']),
            'type' => $validated['type'],
            'prefix_sku' => $prefix,
            'description' => isset($validated['description']) ? trim($validated['description']) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Jika nama kategori berubah, sinkronkan field category pada produk yang terkait
        if ($oldName !== $category->name) {
            Product::where('category', $oldName)->update(['category' => $category->name]);
        }

        return redirect()->route('categories.index');
    }

    /**
     * Toggle active status of category.
     */
    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        return redirect()->route('categories.index');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $linkedProductsCount = Product::where('category', $category->name)->count();

        if ($linkedProductsCount > 0) {
            return redirect()->route('categories.index')->withErrors([
                'delete' => "Kategori \"{$category->name}\" tidak dapat dihapus karena masih digunakan oleh {$linkedProductsCount} barang. Anda dapat menonaktifkan statusnya.",
            ]);
        }

        $category->delete();

        return redirect()->route('categories.index');
    }
}

