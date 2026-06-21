@extends('layouts.app')

@section('title', 'Tambah Produk')
<?php $activeMenu = 'inventory'; ?>

@section('content')
<main class="flex-1 h-full flex flex-col min-w-0">

    <header class="flex items-center gap-3 mb-6">
        <a href="{{ route('inventory.index') }}"
           class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 hover:text-indigo-500 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Tambah Produk</h1>
            <p class="text-xs font-bold text-slate-400">Lengkapi data bahan baru untuk gudang.</p>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto hide-scrollbar">
        <form method="POST" action="{{ route('inventory.store') }}" enctype="multipart/form-data"
              class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-6 shadow-sm max-w-3xl">
            @csrf

            @include('inventory._form')

            <div class="flex gap-3 mt-7">
                <a href="{{ route('inventory.index') }}"
                   class="flex-1 text-center py-3.5 rounded-2xl font-bold text-slate-500 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="flex-[2] py-3.5 rounded-2xl font-bold text-white bg-slate-900 dark:bg-indigo-500 hover:bg-indigo-600 transition-colors shadow-lg">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
