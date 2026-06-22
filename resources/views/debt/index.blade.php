@extends('layouts.app')
@section('title', 'Kelola Utang')
<?php $activeMenu = 'debt'; ?>

@section('content')
<main class="flex-1 h-full flex flex-col min-w-0">

    <header class="flex justify-between items-center mb-5">
        <div>
            <h1 class="text-[28px] font-black text-slate-900 dark:text-white tracking-tight leading-tight">Utang.</h1>
            <p class="text-xs font-bold text-slate-400 mt-1">Monitor dan kelola piutang dagang semua pelanggan.</p>
        </div>
    </header>

    {{-- Kartu Ringkasan --}}
    <div class="grid grid-cols-4 gap-4 mb-5">
        <div class="bg-white dark:bg-slate-800 border border-red-100 dark:border-red-800/50 rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Total Piutang Aktif</p>
            <p class="text-xl font-black text-red-500">Rp {{ number_format($summary['total_active'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Belum Bayar</p>
            <p class="text-xl font-black text-slate-900 dark:text-white">Rp {{ number_format($summary['total_open'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-amber-100 dark:border-amber-800/50 rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Cicilan</p>
            <p class="text-xl font-black text-amber-500">Rp {{ number_format($summary['total_partial'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 border {{ $summary['count_overdue'] > 0 ? 'border-red-200 dark:border-red-800/50' : 'border-slate-100 dark:border-slate-700' }} rounded-[20px] p-4 shadow-sm">
            <p class="text-xs font-bold text-slate-400 mb-1">Jatuh Tempo</p>
            <p class="text-xl font-black {{ $summary['count_overdue'] > 0 ? 'text-red-500 animate-pulse' : 'text-slate-900 dark:text-white' }}">{{ $summary['count_overdue'] }} tagihan</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('debt.index') }}" class="flex gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama pelanggan..."
               class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="status" onchange="this.form.submit()"
                class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none">
            <option value="">Aktif (default)</option>
            <option value="open"    @selected(request('status') === 'open')>Belum Bayar</option>
            <option value="partial" @selected(request('status') === 'partial')>Cicilan</option>
            <option value="paid"    @selected(request('status') === 'paid')>Lunas</option>
        </select>
        <button type="submit" class="px-5 py-2.5 bg-slate-900 dark:bg-indigo-500 text-white font-bold text-sm rounded-xl">Cari</button>
        @if (request()->anyFilled(['q', 'status']))
            <a href="{{ route('debt.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-500 font-bold text-sm rounded-xl">Reset</a>
        @endif
    </form>

    {{-- Tabel Utang --}}
    <div class="flex-1 overflow-y-auto hide-scrollbar">
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/40 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3">Pelanggan</th>
                        <th class="px-5 py-3">Invoice</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Tenggat</th>
                        <th class="px-5 py-3 text-right">Utang Awal</th>
                        <th class="px-5 py-3 text-right">Sisa</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @forelse ($debts as $debt)
                        <tr class="{{ $debt->isOverdue() ? 'bg-red-50/30 dark:bg-red-900/10' : 'hover:bg-slate-50/50 dark:hover:bg-slate-700/20' }} transition-colors">
                            <td class="px-5 py-3">
                                <a href="{{ route('customer.show', $debt->customer) }}" class="font-bold text-slate-800 dark:text-white hover:text-indigo-500 transition-colors">
                                    {{ $debt->customer->name }}
                                </a>
                            </td>
                            <td class="px-5 py-3 font-bold text-indigo-500 text-xs">
                                {{ $debt->transaction?->invoice_number ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-slate-500 font-semibold whitespace-nowrap">{{ $debt->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                @if ($debt->due_date)
                                    <span class="font-semibold {{ $debt->isOverdue() ? 'text-red-500 font-bold' : 'text-slate-500' }}">
                                        {{ $debt->due_date->format('d M Y') }}
                                        @if ($debt->isOverdue()) <span class="block text-[10px]">{{ $debt->due_date->diffForHumans() }}</span> @endif
                                    </span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-600 dark:text-slate-400">Rp {{ number_format($debt->original_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-black {{ $debt->status === 'paid' ? 'text-emerald-500' : 'text-red-500' }}">
                                Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $badge = ['open' => ['bg-red-50 dark:bg-red-900/30 text-red-500','Belum Bayar'], 'partial' => ['bg-amber-50 dark:bg-amber-900/30 text-amber-500','Cicilan'], 'paid' => ['bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500','Lunas']][$debt->status] @endphp
                                <span class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $badge[0] }}">{{ $badge[1] }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('debt.show', $debt) }}" class="text-xs font-bold text-indigo-500 hover:text-indigo-600">Detail →</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-16 text-center text-slate-400 font-bold">Tidak ada catatan utang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $debts->links() }}</div>
    </div>
</main>
@endsection
