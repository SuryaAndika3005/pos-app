@extends('layouts.app')

@section('title', 'Laporan')
<?php $activeMenu = 'report'; ?>

@section('content')
<main class="flex-1 h-full flex flex-col min-w-0">

    <header class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-[28px] font-black text-slate-900 dark:text-white tracking-tight leading-tight">Laporan.</h1>
            <p class="text-xs font-bold text-slate-400 mt-1">Ringkasan penjualan dan riwayat transaksi.</p>
        </div>

        {{-- Filter rentang tanggal --}}
        <form method="GET" action="{{ route('report.index') }}" class="flex items-center gap-2">
            <input type="date" name="start" value="{{ $startDate }}"
                   class="px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold dark:text-white">
            <span class="text-slate-400 font-bold text-sm">—</span>
            <input type="date" name="end" value="{{ $endDate }}"
                   class="px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold dark:text-white">
            <button type="submit" class="px-5 py-2.5 bg-slate-900 dark:bg-indigo-500 text-white font-bold text-sm rounded-xl">Tampilkan</button>
        </form>
    </header>

    <div class="flex-1 overflow-y-auto hide-scrollbar pr-1">

        {{-- Kartu Ringkasan --}}
        <div class="grid grid-cols-4 gap-4 mb-5">
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm">
                <p class="text-xs font-bold text-slate-400 mb-1">Total Pendapatan</p>
                <p class="text-xl font-black text-slate-900 dark:text-white">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm">
                <p class="text-xs font-bold text-slate-400 mb-1">Jumlah Transaksi</p>
                <p class="text-xl font-black text-slate-900 dark:text-white">{{ $summary['total_transactions'] }}</p>
            </div>
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm">
                <p class="text-xs font-bold text-slate-400 mb-1">Total Pajak</p>
                <p class="text-xl font-black text-slate-900 dark:text-white">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[20px] p-4 shadow-sm">
                <p class="text-xs font-bold text-slate-400 mb-1">Rata-rata / Transaksi</p>
                <p class="text-xl font-black text-slate-900 dark:text-white">Rp {{ number_format($summary['avg_transaction'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-5 mb-5">
            {{-- Grafik Penjualan Harian --}}
            <div class="col-span-2 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-5 shadow-sm">
                <h3 class="font-bold text-slate-800 dark:text-white mb-4">Penjualan Harian</h3>
                <div class="h-56">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            {{-- Produk Terlaris --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-5 shadow-sm">
                <h3 class="font-bold text-slate-800 dark:text-white mb-4">Produk Terlaris</h3>
                <div class="flex flex-col gap-3">
                    @forelse ($topProducts as $i => $p)
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 text-[11px] font-black flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $p->product_name }}</p>
                                <p class="text-[10px] text-slate-400 font-semibold">{{ rtrim(rtrim(number_format($p->total_qty, 2, '.', ''), '0'), '.') }} {{ $p->unit }} terjual</p>
                            </div>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 shrink-0">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 font-semibold">Belum ada penjualan di rentang ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tabel Riwayat Transaksi --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/40 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3">No. Invoice</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Kasir</th>
                        <th class="px-5 py-3">Metode</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @forelse ($transactions as $trx)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="px-5 py-3 font-bold text-indigo-500">{{ $trx->invoice_number }}</td>
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400 font-semibold whitespace-nowrap">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400 font-semibold">{{ $trx->user->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 text-[11px] font-bold uppercase">{{ $trx->payment_method }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-slate-800 dark:text-white">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('report.show', $trx) }}" class="text-xs font-bold text-indigo-500 hover:text-indigo-600">Lihat Detail →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-slate-400 dark:text-slate-600 font-bold">
                                Tidak ada transaksi pada rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    // Render setelah DOM siap. Karena ini halaman full-load (bukan SPA),
    // cukup DOMContentLoaded biasa.
    document.addEventListener('DOMContentLoaded', () => {
        const isDark = document.documentElement.classList.contains('dark');
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Penjualan',
                    data: @json($chartValues),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.1)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#6366f1',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: isDark ? '#94a3b8' : '#64748b',
                            callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v),
                        },
                        grid: { color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' },
                    },
                    x: {
                        ticks: { color: isDark ? '#94a3b8' : '#64748b' },
                        grid: { display: false },
                    },
                },
            },
        });
    });
</script>
@endpush
