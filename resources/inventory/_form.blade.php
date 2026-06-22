{{--
    Partial form produk. Dipakai oleh inventory/create.blade.php dan
    inventory/edit.blade.php. Variabel $product ada saat mode edit, null saat create.
--}}
@php($product = $product ?? null)

<div class="grid grid-cols-2 gap-5">
    <div class="col-span-2 sm:col-span-1">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Nama Produk *</label>
        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">
        @error('name') <p class="text-red-500 text-xs font-semibold mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-2 sm:col-span-1">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">SKU / Kode Barcode</label>
        <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
               placeholder="Kosongkan jika belum ada"
               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">
        @error('sku') <p class="text-red-500 text-xs font-semibold mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-2">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Deskripsi</label>
        <textarea name="description" rows="2"
                  class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div class="col-span-2 sm:col-span-1">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Kategori</label>
        <input type="text" name="category" value="{{ old('category', $product->category ?? '') }}"
               placeholder="mis. Busa, Kain, Dakron" list="category-list"
               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">
        <datalist id="category-list">
            <option value="Busa"><option value="Kain"><option value="Dakron">
        </datalist>
    </div>

    <div class="col-span-2 sm:col-span-1">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Gambar Produk</label>
        <input type="file" name="image" accept="image/*"
               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold dark:text-white file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:text-indigo-600 file:font-bold file:text-xs">
        @if (($product->image ?? null))
            <p class="text-[11px] text-slate-400 mt-1">Gambar saat ini akan diganti jika upload baru.</p>
        @endif
        @error('image') <p class="text-red-500 text-xs font-semibold mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-2 sm:col-span-1">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Harga Jual (Rp) *</label>
        <input type="number" name="price" value="{{ old('price', $product->price ?? '') }}" min="0" step="0.01" required
               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">
        @error('price') <p class="text-red-500 text-xs font-semibold mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-2 sm:col-span-1">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Harga Modal / HPP (Rp)</label>
        <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? 0) }}" min="0" step="0.01"
               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">
    </div>

    <div class="col-span-2 sm:col-span-1">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Satuan *</label>
        <input type="text" name="unit" value="{{ old('unit', $product->unit ?? '') }}" placeholder="mtr, lbr, kg, pcs" required
               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">
    </div>

    <div class="col-span-2 sm:col-span-1">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Tipe Satuan *</label>
        <select name="unit_type" required
                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">
            @php($currentType = old('unit_type', $product->unit_type ?? 'integer'))
            <option value="integer" @selected($currentType === 'integer')>Bulat (mis. 1 lembar)</option>
            <option value="decimal" @selected($currentType === 'decimal')>Desimal (mis. 2.5 meter)</option>
        </select>
    </div>

    <div class="col-span-2 sm:col-span-1">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">
            Stok {{ $product ? 'Saat Ini' : 'Awal' }} *
        </label>
        <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" min="0" step="0.01" required
               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">
        @if ($product)
            <p class="text-[11px] text-slate-400 mt-1">Mengubah angka ini akan tercatat sebagai penyesuaian stok manual.</p>
        @endif
    </div>

    <div class="col-span-2 sm:col-span-1">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Ambang Stok Menipis *</label>
        <input type="number" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? 0) }}" min="0" step="0.01" required
               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">
        <p class="text-[11px] text-slate-400 mt-1">Badge "Stok Menipis" muncul saat stok ≤ angka ini.</p>
    </div>

    <div class="col-span-2">
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 rounded accent-indigo-500">
            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Aktif (tampil di katalog kasir)</span>
        </label>
    </div>
</div>
