@extends('layouts.app')

@section('title', 'Riwayat Stok')
<?php $activeMenu = 'inventory'; ?>

@section('content')
<main class="flex-1 h-full flex flex-col min-w-0">

    <header class="flex items-center gap-3 mb-6">
        <a href="{{ route('inventory.index') }}"
           class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 hover:text-indigo-500 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Riwayat Stok</h1>
            <p class="text-xs font-bold text-slate-400">{{ $product->name }} — saat ini {{ rtrim(rtrim(number_format($product->stock, 2, '.', ''), '0'), '.') }} {{ $product->unit }}</p>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto hide-scrollbar">
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] overflow-hidden shadow-sm max-w-4xl">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/40 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Tipe</th>
                        <th class="px-5 py-3">Sumber</th>
                        <th class="px-5 py-3 text-right">Jumlah</th>
                        <th class="px-5 py-3 text-right">Stok Sebelum → Sesudah</th>
                        <th class="px-5 py-3">Oleh</th>
                        <th class="px-5 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400 font-semibold whitespace-nowrap">{{ $m->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $typeLabel = ['in' => 'Masuk', 'out' => 'Keluar', 'adjustment' => 'Koreksi'][$m->type];
                                    $typeColor = ['in' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500', 'out' => 'bg-red-50 dark:bg-red-900/30 text-red-500', 'adjustment' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-500'][$m->type];
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $typeColor }}">{{ $typeLabel }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400 font-semibold capitalize">{{ $m->source }}</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-800 dark:text-white">
                                {{ $m->type === 'out' ? '-' : '+' }}{{ rtrim(rtrim(number_format($m->quantity, 2, '.', ''), '0'), '.') }} {{ $product->unit }}
                            </td>
                            <td class="px-5 py-3 text-right text-slate-500 dark:text-slate-400 font-semibold">
                                {{ rtrim(rtrim(number_format($m->stock_before, 2, '.', ''), '0'), '.') }} → {{ rtrim(rtrim(number_format($m->stock_after, 2, '.', ''), '0'), '.') }}
                            </td>
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400 font-semibold">{{ $m->user->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-400 text-[12px]">{{ $m->note ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-slate-400 dark:text-slate-600 font-bold">
                                Belum ada riwayat pergerakan untuk produk ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 max-w-4xl">
            {{ $movements->links() }}
        </div>
    </div>
</main>
@endsection
