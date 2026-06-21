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
            <p class="text-xs font-bold text-slate-400">{{ $transaction->created_at->format('d M Y, H:i') }} · Kasir: {{ $transaction->user->name ?? '—' }}</p>
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

            {{-- Ringkasan pembayaran --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-5 shadow-sm h-fit">
                <h3 class="font-bold text-slate-800 dark:text-white mb-4">Pembayaran</h3>
                <div class="flex flex-col gap-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-400 font-semibold">Subtotal</span><span class="font-bold text-slate-800 dark:text-white">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400 font-semibold">Pajak (11%)</span><span class="font-bold text-slate-800 dark:text-white">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between pt-2 border-t border-slate-100 dark:border-slate-700"><span class="text-slate-600 dark:text-slate-300 font-bold">Total</span><span class="font-black text-indigo-500">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400 font-semibold">Metode</span><span class="font-bold text-slate-800 dark:text-white uppercase">{{ $transaction->payment_method }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400 font-semibold">Dibayar</span><span class="font-bold text-slate-800 dark:text-white">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400 font-semibold">Kembalian</span><span class="font-bold text-slate-800 dark:text-white">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
