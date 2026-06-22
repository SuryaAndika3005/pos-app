@extends('layouts.app')
@section('title', 'Detail Utang')
<?php $activeMenu = 'debt'; ?>

@section('content')
<main class="flex-1 h-full flex flex-col min-w-0">

    <header class="flex items-center gap-3 mb-6">
        <a href="{{ route('debt.index') }}"
           class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 hover:text-indigo-500 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">
                Utang {{ $debt->customer->name }}
            </h1>
            <p class="text-xs font-bold text-slate-400">
                {{ $debt->transaction?->invoice_number ?? 'Manual' }} · {{ $debt->created_at->format('d M Y, H:i') }}
                @if ($debt->due_date) · Tenggat: <span class="{{ $debt->isOverdue() ? 'text-red-500' : '' }}">{{ $debt->due_date->format('d M Y') }}</span> @endif
            </p>
        </div>
    </header>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 text-emerald-600 text-sm font-bold">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex-1 overflow-y-auto hide-scrollbar">
        <div class="grid grid-cols-3 gap-5 max-w-5xl">

            {{-- Kolom kiri: info utang + riwayat pembayaran --}}
            <div class="col-span-2 flex flex-col gap-4">

                {{-- Ringkasan Utang --}}
                <div class="bg-white dark:bg-slate-800 border {{ $debt->isOverdue() ? 'border-red-200 dark:border-red-700/50' : 'border-slate-100 dark:border-slate-700' }} rounded-[24px] p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-xs font-bold text-slate-400 mb-1">Sisa Utang</p>
                            <p class="text-3xl font-black {{ $debt->status === 'paid' ? 'text-emerald-500' : 'text-red-500' }}">
                                Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400 font-semibold">dari Rp {{ number_format($debt->original_amount, 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-400 font-semibold">Terbayar: Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Progress --}}
                    @if ($debt->original_amount > 0)
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 mb-2">
                            <div class="h-2 rounded-full {{ $debt->status === 'paid' ? 'bg-emerald-500' : 'bg-amber-500' }} transition-all"
                                 style="width: {{ min(100, round(($debt->paid_amount / $debt->original_amount) * 100)) }}%"></div>
                        </div>
                        <p class="text-xs text-slate-400 font-semibold">{{ round(($debt->paid_amount / $debt->original_amount) * 100) }}% terbayar</p>
                    @endif
                </div>

                {{-- Riwayat Pembayaran --}}
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-5 shadow-sm">
                    <h3 class="font-bold text-slate-800 dark:text-white mb-4">Riwayat Pembayaran</h3>
                    @if ($debt->payments->count())
                        <div class="flex flex-col divide-y divide-slate-50 dark:divide-slate-700/50">
                            @foreach ($debt->payments as $pay)
                                <div class="flex justify-between items-center py-3">
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-white">Rp {{ number_format($pay->amount, 0, ',', '.') }}</p>
                                        <p class="text-xs text-slate-400 font-semibold">
                                            {{ $pay->created_at->format('d M Y, H:i') }}
                                            · {{ strtoupper($pay->method) }}
                                            · {{ $pay->receivedBy->name ?? '—' }}
                                        </p>
                                        @if ($pay->note)
                                            <p class="text-[11px] text-slate-400 italic mt-0.5">{{ $pay->note }}</p>
                                        @endif
                                    </div>
                                    <span class="text-xs font-bold text-emerald-500">✓</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 font-semibold py-4 text-center">Belum ada pembayaran.</p>
                    @endif
                </div>

                {{-- Item Belanja Terkait --}}
                @if ($debt->transaction?->items->count())
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-5 shadow-sm">
                    <h3 class="font-bold text-slate-800 dark:text-white mb-4">Belanja Terkait ({{ $debt->transaction->invoice_number }})</h3>
                    <div class="flex flex-col divide-y divide-slate-50 dark:divide-slate-700/50">
                        @foreach ($debt->transaction->items as $item)
                            <div class="flex justify-between items-center py-2.5">
                                <div>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $item->product_name }}</p>
                                    <p class="text-xs text-slate-400 font-semibold">
                                        {{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }} {{ $item->unit }} × Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </p>
                                </div>
                                <p class="text-sm font-bold text-slate-800 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Kolom kanan: form bayar --}}
            <div class="flex flex-col gap-4">
                @if ($debt->status !== 'paid')
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-5 shadow-sm">
                    <h3 class="font-bold text-slate-800 dark:text-white mb-4">Terima Pembayaran</h3>
                    <form method="POST" action="{{ route('debt.pay', $debt) }}" class="flex flex-col gap-3">
                        @csrf
                        <div>
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Jumlah (Rp)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" max="{{ $debt->remaining_amount }}"
                                   value="{{ $debt->remaining_amount }}" required
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-base dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Metode</label>
                            <select name="method" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                                <option value="cash">Cash</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer</option>
                                <option value="debit">Debit</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Catatan</label>
                            <input type="text" name="note" placeholder="Opsional"
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <button type="submit" class="w-full py-3.5 rounded-2xl font-bold text-white bg-emerald-500 hover:bg-emerald-600 transition-colors shadow-lg mt-2">
                            Simpan Pembayaran
                        </button>
                        {{-- Shortcut: Lunasi Penuh --}}
                        @if ($debt->status === 'partial')
                            <button type="button" onclick="this.previousElementSibling.previousElementSibling.previousElementSibling.previousElementSibling.querySelector('input').value='{{ $debt->remaining_amount }}'; this.closest('form').submit();"
                                    class="w-full py-2.5 rounded-xl font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 transition-colors text-sm border border-emerald-100 dark:border-emerald-800/50">
                                Lunasi Sekarang (Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }})
                            </button>
                        @endif
                    </form>
                </div>
                @else
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 rounded-[24px] p-5 shadow-sm text-center">
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <p class="font-black text-emerald-600 dark:text-emerald-400">Utang Sudah Lunas!</p>
                    <p class="text-xs text-slate-400 font-semibold mt-1">{{ $debt->updated_at->format('d M Y') }}</p>
                </div>
                @endif

                {{-- Link ke Profil Pelanggan --}}
                <a href="{{ route('customer.show', $debt->customer) }}"
                   class="block bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm hover:border-indigo-300 transition-colors text-center">
                    <p class="text-xs font-bold text-slate-400 mb-1">Pelanggan</p>
                    <p class="font-bold text-slate-800 dark:text-white">{{ $debt->customer->name }}</p>
                    <p class="text-xs text-indigo-500 font-semibold mt-1">Lihat Semua Riwayat →</p>
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
