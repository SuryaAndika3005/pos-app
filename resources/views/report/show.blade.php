@extends('layouts.app')

@section('title', 'Detail Transaksi')
<?php $activeMenu = 'report'; ?>

@section('content')
<main class="flex-1 h-full flex flex-col min-w-0">

    <header class="flex items-center gap-3 mb-6">
        <a href="{{ route('report.index') }}"
           class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 hover:text-indigo-500 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $transaction->invoice_number }}</h1>
            <p class="text-xs font-bold text-slate-400">
                {{ $transaction->created_at->format('d M Y, H:i') }}
                · Kasir: {{ $transaction->user->name ?? '—' }}
                @if ($transaction->customer_name)
                    · <span class="text-indigo-500">Pelanggan: {{ $transaction->customer_name }}</span>
                @endif
            </p>
        </div>

<div class="ml-auto flex items-center gap-2">
    @if ($transaction->status !== 'void' && Auth::user()?->isAdmin())
    <a href="{{ route('pos.edit', $transaction) }}"
       class="px-4 py-2.5 rounded-xl font-bold text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-100 dark:border-amber-800/50 transition-colors flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Edit Nota
    </a>
    @endif

    {{-- Tombol Cetak (jika sudah ada, biarkan) --}}
    <button onclick="window.print()"
            class="px-4 py-2.5 rounded-xl font-bold text-sm text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        Cetak
    </button>
</div>
    </header>

    <div class="flex-1 overflow-y-auto hide-scrollbar">
        <div class="grid grid-cols-3 gap-5 max-w-4xl">

            {{-- Daftar item --}}
            <div class="col-span-2 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-5 shadow-sm">
                <h3 class="font-bold text-slate-800 dark:text-white mb-4">Rincian Item</h3>
                <div class="flex flex-col divide-y divide-slate-50 dark:divide-slate-700/50">
                    @foreach ($transaction->items as $item)
                        <div class="flex justify-between items-center py-3">
                            <div>
                                <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $item->product_name }}</p>
                                <p class="text-xs text-slate-400 font-semibold">
                                    {{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }} {{ $item->unit }}
                                    × Rp {{ number_format($item->price, 0, ',', '.') }}
                                </p>
                            </div>
                            <p class="text-sm font-black text-slate-800 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kolom kanan: info pelanggan + pembayaran --}}
            <div class="flex flex-col gap-4">

                {{-- Info Pelanggan (hanya tampil jika ada nama) --}}
                @if ($transaction->customer_name)
                <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50 rounded-[24px] p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <h3 class="font-bold text-indigo-800 dark:text-indigo-300 text-xs uppercase tracking-wider">Pelanggan</h3>
                    </div>
                    <p class="font-black text-slate-900 dark:text-white text-base">{{ $transaction->customer_name }}</p>
                </div>
                @endif

                {{-- Ringkasan pembayaran --}}
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-5 shadow-sm h-fit">
                    <h3 class="font-bold text-slate-800 dark:text-white mb-4">Pembayaran</h3>
                    <div class="flex flex-col gap-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-semibold">Subtotal</span>
                            <span class="font-bold text-slate-800 dark:text-white">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-semibold">Pajak (11%)</span>
                            <span class="font-bold text-slate-800 dark:text-white">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-slate-100 dark:border-slate-700">
                            <span class="text-slate-600 dark:text-slate-300 font-bold">Total</span>
                            <span class="font-black text-indigo-500">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-semibold">Metode</span>
                            <span class="font-bold text-slate-800 dark:text-white uppercase">{{ $transaction->payment_method }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-semibold">Dibayar</span>
                            <span class="font-bold text-slate-800 dark:text-white">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-semibold">Kembalian</span>
                            <span class="font-bold text-slate-800 dark:text-white">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
@endsection
