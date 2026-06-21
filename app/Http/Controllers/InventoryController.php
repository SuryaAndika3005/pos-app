<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Daftar produk + ringkasan stok untuk halaman Gudang.
     */
    public function index(Request $request): View
    {
        $products = Product::query()
            ->when($request->filled('q'), fn ($q) =>
                $q->where('name', 'like', '%' . $request->string('q') . '%')
                  ->orWhere('sku', 'like', '%' . $request->string('q') . '%')
            )
            ->when($request->filled('category'), fn ($q) =>
                $q->where('category', $request->string('category'))
            )
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->string('status') == 'low') {
                    $q->whereColumn('stock', '<=', 'min_stock');
                }
            })
            ->orderBy('name')
            ->get();

        $categories = Product::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Ringkasan kecil di atas halaman.
        $summary = [
            'total_products' => Product::count(),
            'low_stock'      => Product::whereColumn('stock', '<=', 'min_stock')->count(),
            'total_value'    => Product::query()->selectRaw('SUM(stock * cost_price) as total')->value('total') ?? 0,
        ];

        return view('inventory.index', compact('products', 'categories', 'summary'));
    }

    /**
     * Form tambah produk baru.
     */
    public function create(): View
    {
        return view('inventory.create');
    }

    /**
     * Simpan produk baru.
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($data);

        // Catat stok awal sebagai pergerakan 'in' jika lebih dari 0.
        if ($product->stock > 0) {
            StockMovement::create([
                'product_id'   => $product->id,
                'user_id'      => Auth::id(),
                'type'         => 'in',
                'quantity'     => $product->stock,
                'stock_before' => 0,
                'stock_after'  => $product->stock,
                'source'       => 'manual',
                'note'         => 'Stok awal saat produk dibuat.',
            ]);
        }

        return redirect()->route('inventory.index')->with('status', "Produk \"{$product->name}\" berhasil ditambahkan.");
    }

    /**
     * Form edit produk.
     */
    public function edit(Product $product): View
    {
        return view('inventory.edit', compact('product'));
    }

    /**
     * Perbarui data produk. Perubahan stok lewat form ini dicatat sebagai
     * 'adjustment' di stock_movements (bukan 'in'/'out' transaksi penjualan).
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($product, $data) {
            $stockBefore = (float) $product->stock;
            $stockAfter  = (float) $data['stock'];

            $product->update($data);

            if ($stockBefore != $stockAfter) {
                StockMovement::create([
                    'product_id'   => $product->id,
                    'user_id'      => Auth::id(),
                    'type'         => 'adjustment',
                    'quantity'     => abs($stockAfter - $stockBefore),
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'source'       => 'manual',
                    'note'         => 'Penyesuaian stok manual melalui form edit produk.',
                ]);
            }
        });

        return redirect()->route('inventory.index')->with('status', "Produk \"{$product->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus produk (soft delete).
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('inventory.index')->with('status', "Produk \"{$product->name}\" berhasil dihapus.");
    }

    /**
     * Riwayat pergerakan stok untuk satu produk (restock, penjualan, koreksi).
     */
    public function history(Product $product): View
    {
        $movements = $product->stockMovements()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('inventory.history', compact('product', 'movements'));
    }

    /**
     * Restock cepat (tambah stok) tanpa membuka form edit penuh.
     */
    public function restock(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'note'     => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $product) {
            $stockBefore = (float) $product->stock;
            $qty = (float) $request->input('quantity');

            $product->increment('stock', $qty);

            StockMovement::create([
                'product_id'   => $product->id,
                'user_id'      => Auth::id(),
                'type'         => 'in',
                'quantity'     => $qty,
                'stock_before' => $stockBefore,
                'stock_after'  => $stockBefore + $qty,
                'source'       => 'restock',
                'note'         => $request->input('note') ?: 'Restock manual.',
            ]);
        });

        return back()->with('status', "Stok \"{$product->name}\" berhasil ditambahkan.");
    }
}
