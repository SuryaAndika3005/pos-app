@extends('layouts.app')
@section('title', 'Direktori Pelanggan')
<?php $activeMenu = 'customer'; ?>

@section('content')
<main class="flex-1 h-full flex flex-col min-w-0" x-data="{ showAdd: false }">

    <header class="flex justify-between items-center mb-5">
        <div>
            <h1 class="text-[28px] font-black text-slate-900 dark:text-white tracking-tight leading-tight">Pelanggan.</h1>
            <p class="text-xs font-bold text-slate-400 mt-1">Direktori, riwayat belanja, dan kelola utang.</p>
        </div>
        <button @click="showAdd = true"
                class="flex items-center gap-2 bg-slate-900 dark:bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-sm px-5 py-3 rounded-2xl shadow-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            Tambah Pelanggan
        </button>
    </header>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 text-emerald-600 dark:text-emerald-400 text-sm font-bold">
            {{ session('status') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('customer.index') }}" class="flex gap-2 mb-5">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau nomor telp..."
               class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="px-5 py-2.5 bg-slate-900 dark:bg-indigo-500 text-white font-bold text-sm rounded-xl">Cari</button>
        @if (request('q'))
            <a href="{{ route('customer.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-500 font-bold text-sm rounded-xl">Reset</a>
        @endif
    </form>

    {{-- Tabel Pelanggan --}}
    <div class="flex-1 overflow-y-auto hide-scrollbar">
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/40 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">No. Telp</th>
                        <th class="px-5 py-3 text-center">Transaksi</th>
                        <th class="px-5 py-3 text-right">Total Belanja</th>
                        <th class="px-5 py-3 text-right">Sisa Utang</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @forelse ($customers as $c)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="px-5 py-3">
                                <p class="font-bold text-slate-800 dark:text-white">{{ $c->name }}</p>
                                @if ($c->notes)
                                    <p class="text-[11px] text-slate-400 font-semibold truncate max-w-[180px]">{{ $c->notes }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400 font-semibold">{{ $c->phone ?? '—' }}</td>
                            <td class="px-5 py-3 text-center font-bold text-slate-800 dark:text-white">{{ $c->transactions_count }}</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-800 dark:text-white">Rp {{ number_format($c->total_spent, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right">
                                @if ($c->total_debt > 0)
                                    <span class="font-black text-red-500">Rp {{ number_format($c->total_debt, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-emerald-500 font-bold text-xs">Lunas</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('customer.show', $c) }}"
                                   class="text-xs font-bold text-indigo-500 hover:text-indigo-600">Lihat Riwayat →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-slate-400 font-bold">
                                Belum ada pelanggan terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $customers->links() }}</div>
    </div>

    {{-- Modal Tambah Pelanggan --}}
    <div x-show="showAdd" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 w-[420px] rounded-[28px] p-7 shadow-2xl border dark:border-slate-700" @click.outside="showAdd = false" x-transition.scale.origin.center>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-5">Tambah Pelanggan Baru</h3>
            <form method="POST" action="{{ route('customer.store') }}" class="flex flex-col gap-4">
                @csrf
                <div>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Nama*</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">No. Telp*</label>
                    <input type="text" name="phone" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Alamat</label>
                    <input type="text" name="address" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Catatan Internal</label>
                    <textarea name="notes" rows="2" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none focus:border-indigo-500"></textarea>
                </div>
                <div class="flex gap-3 mt-2">
                    <button type="button" @click="showAdd = false" class="flex-1 py-3 rounded-xl font-bold text-slate-500 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 transition-colors">Batal</button>
                    <button type="submit" class="flex-[2] py-3 rounded-xl font-bold text-white bg-slate-900 dark:bg-indigo-500 hover:bg-indigo-600 transition-colors shadow-md">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection
