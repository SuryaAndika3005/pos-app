<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Ringkasan & riwayat transaksi.
     * Filter tanggal default: 7 hari terakhir (termasuk hari ini).
     */
    public function index(Request $request): View
    {
        $startDate = $request->filled('start')
            ? Carbon::parse($request->string('start'))->startOfDay()
            : now()->subDays(6)->startOfDay();

        $endDate = $request->filled('end')
            ? Carbon::parse($request->string('end'))->endOfDay()
            : now()->endOfDay();

        $baseQuery = Transaction::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid');

        // Ringkasan kartu atas.
        $summary = [
            'total_revenue'      => (clone $baseQuery)->sum('total'),
            'total_transactions' => (clone $baseQuery)->count(),
            'total_tax'          => (clone $baseQuery)->sum('tax'),
            'avg_transaction'    => (clone $baseQuery)->avg('total') ?? 0,
        ];

        // Data grafik: total penjualan per hari dalam rentang yang dipilih.
        $dailySales = (clone $baseQuery)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Susun semua tanggal dalam rentang agar grafik tidak bolong di hari tanpa transaksi.
        $chartLabels = [];
        $chartValues = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $key = $cursor->format('Y-m-d');
            $chartLabels[] = $cursor->format('d M');
            $chartValues[] = (float) ($dailySales[$key]->total ?? 0);
            $cursor->addDay();
        }

        // Produk terlaris dalam rentang waktu ini.
        $topProducts = \App\Models\TransactionItem::query()
            ->whereHas('transaction', fn ($q) => $q
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'paid'))
            ->selectRaw('product_name, unit, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_name', 'unit')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Riwayat transaksi (tabel, dengan paginasi).
        $transactions = (clone $baseQuery)
            ->with('user')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('report.index', [
            'summary'      => $summary,
            'chartLabels'  => $chartLabels,
            'chartValues'  => $chartValues,
            'topProducts'  => $topProducts,
            'transactions' => $transactions,
            'startDate'    => $startDate->format('Y-m-d'),
            'endDate'      => $endDate->format('Y-m-d'),
        ]);
    }

    /**
     * Detail satu transaksi (untuk lihat ulang struk).
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load('items', 'user');

        return view('report.show', compact('transaction'));
    }
}
