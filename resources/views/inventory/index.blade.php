@extends('layouts.app')

@section('title', 'Gudang')
<?php $activeMenu = 'inventory'; ?>

@section('content')
<main class="flex-1 h-full flex flex-col min-w-0">

    {{-- Header --}}
    <header class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-[28px] font-black text-slate-900 dark:text-white tracking-tight leading-tight">Gudang.</h1>
            <p class="text-xs font-bold text-slate-400 mt-1">Kelola produk, stok, dan riwayat pergerakan barang.</p>
        </div>
        <a href="{{ route('inventory.create') }}"
           class="flex items-center gap-2 bg-slate-900 dark:bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-sm px-5 py-3 rounded-2xl shadow-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            Tambah Produk
        </a>
    </header>

    {{-- Notifikasi status --}}
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 text-emerald-600 dark:text-emerald-400 text-sm font-bold">
            {{ session('status') }}
        </div>
    @endif

    {{-- Kartu Ringkasan --}}
    <div class="grid grid-cols-3 gap-4 mb-5">
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Total Produk</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $summary['total_products'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border {{ $summary['low_stock'] > 0 ? 'border-red-200 dark:border-red-800/50' : 'border-slate-100 dark:border-slate-700' }} rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Stok Menipis</p>
            <p class="text-2xl font-black {{ $summary['low_stock'] > 0 ? 'text-red-500' : 'text-slate-900 dark:text-white' }}">{{ $summary['low_stock'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Nilai Stok (HPP)</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white">Rp {{ number_format($summary['total_value'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-wrap gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU..."
               class="flex-1 min-w-[180px] px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm dark:text-white">

        <select name="category" onchange="this.form.submit()"
                class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
            @endforeach
        </select>

        <select name="status" onchange="this.form.submit()"
                class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white">
            <option value="">Semua Status</option>
            <option value="low" @selected(request('status') === 'low')>Stok Menipis</option>
        </select>

        <button type="submit" class="px-5 py-2.5 bg-slate-900 dark:bg-indigo-500 text-white font-bold text-sm rounded-xl">Cari</button>
        @if (request()->anyFilled(['q', 'category', 'status']))
            <a href="{{ route('inventory.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 font-bold text-sm rounded-xl">Reset</a>
        @endif
    </form>

    {{-- Tabel Produk --}}
    <div class="flex-1 overflow-y-auto hide-scrollbar">
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/40 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3">Produk</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3 text-right">Harga Jual</th>
                        <th class="px-5 py-3 text-right">Stok</th>
                        <th class="px-5 py-3">Prediksi</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden shrink-0">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-base opacity-50">{{ $product->category === 'Busa' ? '🧽' : ($product->category === 'Kain' ? '🧵' : '📦') }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800 dark:text-white truncate">{{ $product->name }}</p>
                                        <p class="text-[11px] text-slate-400 font-semibold">{{ $product->sku ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400 font-semibold">{{ $product->category ?? '—' }}</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-800 dark:text-white">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right">
                                <span class="font-bold {{ $product->isLowStock() ? 'text-red-500' : 'text-slate-800 dark:text-white' }}">
                                    {{ rtrim(rtrim(number_format($product->stock, 2, '.', ''), '0'), '.') }}
                                </span>
                                <span class="text-[11px] text-slate-400"> {{ $product->unit }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @if ($product->hasPrediction())
                                    <span class="text-[11px] font-bold text-indigo-500">
                                        Habis ~{{ \Illuminate\Support\Carbon::parse($product->predicted_stockout_at)->diffInDays(now()) }} hari lagi
                                    </span>
                                @else
                                    <span class="text-[11px] font-semibold text-slate-300 dark:text-slate-600">Belum ada data</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if ($product->isLowStock())
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-500 text-[11px] font-bold">Menipis</span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 text-[11px] font-bold">Aman</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end items-center gap-1">
                                    {{-- Restock cepat --}}
                                    <details class="relative">
                                        <summary class="list-none w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer transition-colors" title="Restock cepat">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                                        </summary>
                                        <form method="POST" action="{{ route('inventory.restock', $product) }}"
                                              class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl p-3 z-20">
                                            @csrf
                                            <label class="text-[10px] font-bold text-slate-400 uppercase">Tambah Stok ({{ $product->unit }})</label>
                                            <input type="number" name="quantity" step="{{ $product->unit_type === 'decimal' ? '0.1' : '1' }}" min="0.01" required
                                                   class="w-full mt-1 mb-2 px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold dark:text-white">
                                            <button type="submit" class="w-full py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg">Simpan</button>
                                        </form>
                                    </details>

                                    <a href="{{ route('inventory.history', $product) }}" title="Riwayat"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </a>

                                    <a href="{{ route('inventory.edit', $product) }}" title="Edit"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>

                                    <form method="POST" action="{{ route('inventory.destroy', $product) }}"
                                          onsubmit="return confirm('Hapus produk \"{{ $product->name }}\"? Produk tidak akan muncul lagi di katalog kasir.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-slate-400 dark:text-slate-600 font-bold">
                                Belum ada produk. Klik "Tambah Produk" untuk mulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
