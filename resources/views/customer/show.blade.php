@extends('layouts.app')
@section('title', $customer->name)
<?php $activeMenu = 'customer'; ?>

@section('content')
<main class="flex-1 h-full flex flex-col min-w-0" x-data="{ tab: 'history', showPayAll: false }">

    {{-- Header --}}
    <header class="flex items-center gap-3 mb-5">
        <a href="{{ route('customer.index') }}"
           class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 hover:text-indigo-500 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ $customer->name }}</h1>
            <p class="text-xs font-bold text-slate-400">{{ $customer->phone ?? 'Tanpa no. telp' }} @if($customer->address) · {{ $customer->address }} @endif</p>
        </div>
        @if ($customer->total_debt > 0)
            <button @click="showPayAll = true"
                    class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white font-bold text-sm px-5 py-3 rounded-2xl shadow-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Lunasi Semua Utang
            </button>
        @endif
    </header>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 text-emerald-600 dark:text-emerald-400 text-sm font-bold">
            {{ session('status') }}
        </div>
    @endif

    {{-- Kartu Ringkasan --}}
    <div class="grid grid-cols-4 gap-4 mb-5">
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Total Belanja</p>
            <p class="text-xl font-black text-slate-900 dark:text-white">Rp {{ number_format($summary['total_spent'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Jumlah Transaksi</p>
            <p class="text-xl font-black text-slate-900 dark:text-white">{{ $summary['total_transactions'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border {{ $summary['total_debt'] > 0 ? 'border-red-200 dark:border-red-800/50' : 'border-slate-100 dark:border-slate-700' }} rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Sisa Utang</p>
            <p class="text-xl font-black {{ $summary['total_debt'] > 0 ? 'text-red-500' : 'text-emerald-500' }}">
                {{ $summary['total_debt'] > 0 ? 'Rp ' . number_format($summary['total_debt'], 0, ',', '.') : 'Lunas' }}
            </p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Kunjungan Terakhir</p>
            <p class="text-sm font-black text-slate-900 dark:text-white">{{ $summary['last_visit'] ? \Carbon\Carbon::parse($summary['last_visit'])->format('d M Y') : '—' }}</p>
        </div>
    </div>

    {{-- Tab --}}
    <div class="flex gap-1 mb-4 bg-slate-100 dark:bg-slate-700/50 rounded-xl p-1 w-fit">
        <button @click="tab = 'history'" :class="tab === 'history' ? 'bg-white dark:bg-slate-800 shadow-sm text-slate-800 dark:text-white' : 'text-slate-400 hover:text-slate-600'"
                class="px-4 py-2 rounded-lg text-xs font-bold transition-all">Riwayat Belanja</button>
        <button @click="tab = 'debt'" :class="tab === 'debt' ? 'bg-white dark:bg-slate-800 shadow-sm text-slate-800 dark:text-white' : 'text-slate-400 hover:text-slate-600'"
                class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5">
            Utang
            @if ($customer->total_debt > 0)
                <span class="bg-red-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full">{{ $debts->whereIn('status', ['open','partial'])->count() }}</span>
            @endif
        </button>
    </div>

    <div class="flex-1 overflow-y-auto hide-scrollbar">

        {{-- Tab: Riwayat Belanja --}}
        <div x-show="tab === 'history'">
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] overflow-hidden shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/40 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Item</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Bayar</th>
                            <th class="px-5 py-3 text-center">Utang</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                        @forelse ($transactions as $trx)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                                <td class="px-5 py-3 font-bold text-indigo-500">{{ $trx->invoice_number }}</td>
                                <td class="px-5 py-3 text-slate-500 font-semibold whitespace-nowrap">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-3 text-slate-500 font-semibold">
                                    {{ $trx->items->count() }} item
                                    <span class="text-[10px] text-slate-400 block truncate max-w-[150px]">{{ $trx->items->pluck('product_name')->join(', ') }}</span>
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-slate-800 dark:text-white">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-bold text-slate-800 dark:text-white">Rp {{ number_format($trx->paid_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-center">
                                    @if ($trx->debt && $trx->debt->status !== 'paid')
                                        <span class="inline-block px-2 py-1 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-500 text-[11px] font-bold">
                                            Rp {{ number_format($trx->debt->remaining_amount, 0, ',', '.') }}
                                        </span>
                                    @elseif ($trx->debt && $trx->debt->status === 'paid')
                                        <span class="inline-block px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 text-[11px] font-bold">Lunas</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('report.show', $trx) }}" class="text-xs font-bold text-indigo-500 hover:text-indigo-600">Detail →</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400 font-bold">Belum ada riwayat transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $transactions->links() }}</div>
        </div>

        {{-- Tab: Utang --}}
        <div x-show="tab === 'debt'" x-cloak>
            @forelse ($debts as $debt)
                <div class="bg-white dark:bg-slate-800 border {{ $debt->status === 'paid' ? 'border-slate-100 dark:border-slate-700' : ($debt->isOverdue() ? 'border-red-300 dark:border-red-700/50' : 'border-amber-200 dark:border-amber-700/50') }} rounded-[20px] p-5 shadow-sm mb-3"
                     x-data="{ showPay: false }">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                @if ($debt->status === 'paid')
                                    <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-[10px] font-black rounded-lg">LUNAS</span>
                                @elseif ($debt->status === 'partial')
                                    <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[10px] font-black rounded-lg">CICILAN</span>
                                @else
                                    <span class="px-2 py-0.5 bg-red-50 dark:bg-red-900/30 text-red-500 text-[10px] font-black rounded-lg">BELUM BAYAR</span>
                                @endif
                                @if ($debt->isOverdue())
                                    <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/40 text-red-600 text-[10px] font-black rounded-lg animate-pulse">JATUH TEMPO</span>
                                @endif
                            </div>
                            <p class="font-bold text-sm text-slate-800 dark:text-white">{{ $debt->transaction?->invoice_number ?? 'Manual' }}</p>
                            <p class="text-[11px] text-slate-400 font-semibold">{{ $debt->created_at->format('d M Y') }}
                                @if ($debt->due_date) · Tenggat: <span class="{{ $debt->isOverdue() ? 'text-red-500' : '' }}">{{ $debt->due_date->format('d M Y') }}</span> @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-slate-400">Sisa</p>
                            <p class="text-xl font-black {{ $debt->status === 'paid' ? 'text-emerald-500' : 'text-red-500' }}">
                                Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] text-slate-400 font-semibold">dari Rp {{ number_format($debt->original_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    @if ($debt->original_amount > 0)
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 mb-3">
                            <div class="h-1.5 rounded-full {{ $debt->status === 'paid' ? 'bg-emerald-500' : 'bg-amber-500' }}"
                                 style="width: {{ min(100, round(($debt->paid_amount / $debt->original_amount) * 100)) }}%"></div>
                        </div>
                        <p class="text-[10px] text-slate-400 font-semibold mb-3">
                            Terbayar {{ round(($debt->paid_amount / $debt->original_amount) * 100) }}% · Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}
                        </p>
                    @endif

                    {{-- Riwayat Pembayaran Cicilan --}}
                    @if ($debt->payments->count())
                        <div class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-3 mb-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Riwayat Pembayaran</p>
                            @foreach ($debt->payments as $pay)
                                <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-700/50 last:border-0">
                                    <div>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($pay->amount, 0, ',', '.') }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $pay->created_at->format('d M Y, H:i') }} · {{ strtoupper($pay->method) }}</p>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400">{{ $pay->receivedBy->name ?? '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Tombol Bayar (hanya jika belum lunas) --}}
                    @if ($debt->status !== 'paid')
                        <button @click="showPay = !showPay" class="text-xs font-bold text-indigo-500 hover:text-indigo-600 transition-colors">
                            <span x-text="showPay ? '▲ Tutup' : '▼ Terima Pembayaran'"></span>
                        </button>

                        <div x-show="showPay" x-cloak x-transition class="mt-3">
                            <form method="POST" action="{{ route('debt.pay', $debt) }}" class="flex flex-col gap-3">
                                @csrf
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Jumlah Bayar</label>
                                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $debt->remaining_amount }}"
                                               value="{{ $debt->remaining_amount }}" required
                                               class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Metode</label>
                                        <select name="method" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                                            <option value="cash">Cash</option>
                                            <option value="qris">QRIS</option>
                                            <option value="transfer">Transfer</option>
                                            <option value="debit">Debit</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="text" name="note" placeholder="Catatan (opsional)"
                                       class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                                <button type="submit" class="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm rounded-xl transition-colors shadow-md">
                                    Simpan Pembayaran
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-12 text-center shadow-sm">
                    <p class="font-bold text-slate-400 dark:text-slate-600">Tidak ada catatan utang untuk pelanggan ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal Lunasi Semua --}}
    <div x-show="showPayAll" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 w-[380px] rounded-[28px] p-7 shadow-2xl border dark:border-slate-700" @click.outside="showPayAll = false" x-transition.scale.origin.center>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Lunasi Semua Utang</h3>
            <p class="text-xs text-slate-400 font-semibold mb-4">
                Total yang akan dilunasi: <span class="font-black text-red-500">Rp {{ number_format($customer->total_debt, 0, ',', '.') }}</span>
            </p>
            <form method="POST" action="{{ route('customer.pay-all', $customer) }}" class="flex flex-col gap-3">
                @csrf
                <div>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Metode Pembayaran</label>
                    <select name="method" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                        <option value="cash">Cash</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer</option>
                        <option value="debit">Debit</option>
                    </select>
                </div>
                <input type="text" name="note" placeholder="Catatan pelunasan (opsional)"
                       class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                <div class="flex gap-3 mt-2">
                    <button type="button" @click="showPayAll = false" class="flex-1 py-3 rounded-xl font-bold text-slate-500 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 transition-colors">Batal</button>
                    <button type="submit" class="flex-[2] py-3 rounded-xl font-bold text-white bg-emerald-500 hover:bg-emerald-600 transition-colors shadow-md">Konfirmasi Pelunasan</button>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection
