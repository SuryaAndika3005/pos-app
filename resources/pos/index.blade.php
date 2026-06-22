@extends('layouts.app')
@section('title', 'Terminal Kasir')
<?php $activeMenu = 'pos'; ?>

@section('content')
<div x-data="posCart" @keydown.window="handleKeydown" class="flex-1 h-full flex gap-4 min-w-0">

    {{-- ====== AREA KIRI: KATALOG ====== --}}
    <main class="flex-1 h-full flex flex-col min-w-0">

        <header class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-[28px] font-black text-slate-900 dark:text-white tracking-tight leading-tight">Dinda Perabot.</h1>
                <p class="text-xs font-bold text-slate-400 mt-1" x-text="currentTime"></p>
            </div>
            <div class="flex gap-2">
                <form action="{{ route('pos.index') }}" method="GET" class="relative">
                    <svg class="w-5 h-5 absolute left-4 top-2.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="searchInput" name="q" value="{{ request('q') }}" placeholder="Cari bahan (Tekan '/')"
                           class="w-64 pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-transparent dark:border-slate-700 rounded-2xl focus:outline-none focus:border-indigo-500 font-semibold text-sm transition-all shadow-sm dark:text-white dark:placeholder-slate-500">
                </form>
            </div>
        </header>

        {{-- Filter Kategori --}}
        <div class="flex gap-2 mb-4 overflow-x-auto pb-1 hide-scrollbar">
            @foreach ($categories as $cat)
                <a href="{{ route('pos.index', $cat['value'] ? ['category' => $cat['value']] : []) }}"
                   class="flex items-center gap-2 px-4 py-1.5 rounded-full cursor-pointer transition-all border {{ request('category') == $cat['value'] ? 'bg-slate-900 dark:bg-indigo-500 text-white border-slate-900 dark:border-indigo-500 shadow-md' : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-400 shadow-sm' }}">
                    <span class="text-sm">{{ $cat['icon'] }}</span>
                    <span class="text-xs font-bold whitespace-nowrap">{{ $cat['label'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Panel Antrean Aktif --}}
        @if ($queue->count())
        <div class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-2xl p-3">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <h3 class="text-xs font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider">Sedang Disiapkan ({{ $queue->count() }})</h3>
            </div>
            <div class="flex gap-2 overflow-x-auto hide-scrollbar pb-1">
                @foreach ($queue as $q)
                <div class="shrink-0 bg-white dark:bg-slate-800 rounded-xl border border-amber-200 dark:border-amber-700/50 p-2.5 min-w-[160px] shadow-sm">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-[10px] font-black text-amber-600 dark:text-amber-400">{{ $q->invoice_number }}</p>
                        <span class="text-[9px] font-bold text-slate-400">{{ $q->created_at->format('H:i') }}</span>
                    </div>
                    <p class="text-xs font-bold text-slate-800 dark:text-white truncate mb-1">{{ $q->customer_name ?? 'Tanpa nama' }}</p>
                    <p class="text-[10px] text-slate-400 font-semibold mb-2">{{ $q->items->count() }} item · Rp {{ number_format($q->total, 0, ',', '.') }}</p>
                    <button onclick="completeQueue({{ $q->id }}, '{{ $q->invoice_number }}', this)"
                            class="w-full py-1 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold rounded-lg transition-colors">
                        ✓ Barang Diserahkan
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="flex justify-between items-end mb-2 px-1">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Bahan Tersedia</h2>
            <a href="{{ route('pos.index') }}" class="text-xs font-bold text-indigo-500 hover:text-indigo-600">Lihat Semua →</a>
        </div>

        {{-- Grid Produk --}}
        <div class="flex-1 overflow-y-auto pb-4 pr-2 hide-scrollbar">
            <div class="grid grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                @forelse ($products as $product)
                    <div class="bg-white dark:bg-slate-800 border {{ $product->stock <= $product->min_stock ? 'border-red-300 dark:border-red-500/50' : 'border-slate-100 dark:border-slate-700' }} rounded-[20px] p-3 shadow-sm hover:shadow-md transition-all group flex flex-col">
                        <div class="w-full h-28 bg-slate-50 dark:bg-slate-700/50 rounded-[14px] mb-3 flex items-center justify-center relative overflow-hidden">
                            @if ($product->stock <= $product->min_stock)
                                <div class="absolute top-2 right-2 bg-red-500 text-white text-[9px] font-bold px-2 py-0.5 rounded z-10 animate-pulse">Sisa {{ $product->stock }}</div>
                            @endif
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <span class="text-3xl opacity-50">{{ $product->category === 'Busa' ? '🧽' : ($product->category === 'Kain' ? '🧵' : '📦') }}</span>
                            @endif
                        </div>
                        <div class="flex-1 flex flex-col px-1">
                            <h3 class="font-bold text-[13px] text-slate-800 dark:text-white leading-tight mb-1 line-clamp-2">{{ $product->name }}</h3>
                            <p class="text-[10px] font-medium text-slate-400 dark:text-slate-500 mb-2 line-clamp-1">{{ $product->description }}</p>
                            <div class="flex justify-between items-end mt-auto pt-2 border-t border-slate-50 dark:border-slate-700/50">
                                <div>
                                    <p class="text-[9px] font-semibold text-slate-400 mb-0.5">Rp / {{ $product->unit }}</p>
                                    <p class="text-sm font-black text-slate-900 dark:text-white leading-none">{{ number_format($product->price, 0, ',', '.') }}</p>
                                </div>
                                <button type="button"
                                        data-id="{{ $product->id }}" data-sku="{{ $product->sku ?? $product->id }}"
                                        data-name="{{ $product->name }}" data-price="{{ $product->price }}"
                                        data-unit="{{ $product->unit }}" data-unit-type="{{ $product->unit_type }}"
                                        data-stock="{{ $product->stock }}"
                                        @click="addItem($event.currentTarget.dataset)"
                                        {{ $product->stock <= 0 ? 'disabled' : '' }}
                                        class="w-8 h-8 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-[10px] flex items-center justify-center hover:bg-indigo-500 hover:text-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed shrink-0 active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center text-slate-400 py-16">
                        <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <p class="font-bold text-lg">Bahan tidak ditemukan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </main>

    {{-- ====== AREA KANAN: KERANJANG ====== --}}
    <x-cart />

    {{-- ====== MODAL NUMPAD ====== --}}
    <div x-show="showNumpad" x-cloak class="absolute inset-0 z-[60] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm rounded-[32px]">
        <div class="bg-white dark:bg-slate-800 w-[300px] rounded-[28px] p-6 shadow-2xl border dark:border-slate-700" @click.outside="closeNumpad()" x-transition.scale.origin.center>
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-slate-900 dark:text-white line-clamp-1" x-text="numpadTarget ? numpadTarget.name : ''"></h3>
                <button @click="closeNumpad()" class="text-slate-400 hover:text-red-500">✕</button>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-3 mb-4 text-right text-3xl font-black text-indigo-500 tracking-wider">
                <span x-text="numpadBuffer === '' ? '0' : numpadBuffer"></span>
                <span class="text-sm text-slate-400 ml-1" x-text="numpadTarget ? numpadTarget.unit : ''"></span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <template x-for="n in ['7','8','9','4','5','6','1','2','3']" :key="n">
                    <button @click="pressNumpad(n)" class="py-3 bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white font-bold text-lg rounded-xl hover:bg-indigo-500 hover:text-white transition-colors active:scale-95" x-text="n"></button>
                </template>
                <button @click="pressNumpad('.')" class="py-3 bg-slate-100 dark:bg-slate-700 font-bold text-lg rounded-xl hover:bg-indigo-500 hover:text-white transition-colors" :class="numpadTarget?.unit_type !== 'decimal' ? 'opacity-30 cursor-not-allowed text-slate-400' : 'text-slate-800 dark:text-white'">.</button>
                <button @click="pressNumpad('0')" class="py-3 bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white font-bold text-lg rounded-xl hover:bg-indigo-500 hover:text-white transition-colors active:scale-95">0</button>
                <button @click="pressNumpad('del')" class="py-3 bg-red-100 dark:bg-red-900/40 text-red-500 font-bold text-lg rounded-xl hover:bg-red-500 hover:text-white transition-colors active:scale-95">⌫</button>
            </div>
            <button @click="confirmNumpad()" class="w-full mt-4 py-3 bg-slate-900 dark:bg-indigo-500 text-white font-bold rounded-xl hover:bg-indigo-600 transition-colors shadow-md">Simpan Kuantitas</button>
        </div>
    </div>

    {{-- ====== MODAL CHECKOUT ====== --}}
    <div x-show="showModal" x-cloak class="absolute inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm rounded-[32px]" @keydown.escape.window="showModal = false">
        <div class="bg-white dark:bg-slate-800 w-[440px] rounded-[28px] p-7 shadow-2xl border dark:border-slate-700 max-h-[90vh] overflow-y-auto hide-scrollbar" @click.outside="showModal = false" x-transition.scale.origin.center>

            <template x-if="!result">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Proses Pembayaran</h3>
                    <p class="text-xs font-semibold text-slate-400 mb-4">Selesaikan transaksi keranjang aktif.</p>

                    {{-- Nama pelanggan (sinkron) --}}
                    <div class="flex items-center gap-2 mb-4 px-3 py-2.5 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <input type="text" x-model="activeCart.customerName"
                               placeholder="Nama pelanggan (wajib jika kurang bayar)"
                               class="flex-1 bg-transparent text-sm font-semibold text-slate-800 dark:text-white placeholder-slate-300 focus:outline-none">
                    </div>

                    {{-- Total --}}
                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4 mb-4 border border-slate-100 dark:border-slate-700">
                        <div class="flex justify-between text-lg font-black text-slate-900 dark:text-white"><span>Total Tagihan</span><span x-text="formatRupiah(total)"></span></div>
                    </div>

                    {{-- Metode Bayar --}}
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 block">Metode Bayar</label>
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <template x-for="m in ['cash','qris','transfer','debit']" :key="m">
                            <button type="button" @click="paymentMethod = m"
                                    :class="paymentMethod === m ? 'bg-indigo-500 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300'"
                                    class="py-2 rounded-xl text-xs font-bold uppercase transition-all" x-text="m"></button>
                        </template>
                    </div>

                    {{-- Uang Diterima --}}
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 block">Uang Diterima</label>
                    <input type="number" x-model.number="paidAmount" min="0"
                           class="w-full mb-2 px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-lg text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500">
                    <div class="flex gap-2 mb-4">
                        <button type="button" @click="paidAmount = Math.round(total)" class="flex-1 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-bold hover:bg-indigo-100 transition-colors border border-indigo-100 dark:border-indigo-800/50">Uang Pas</button>
                        <button type="button" @click="paidAmount = 50000"  class="flex-1 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">50k</button>
                        <button type="button" @click="paidAmount = 100000" class="flex-1 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">100k</button>
                        <button type="button" @click="paidAmount = 0"      class="flex-1 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">0</button>
                    </div>

                    {{-- Kembalian / Kurang Bayar --}}
                    <div class="flex justify-between text-sm font-bold mb-1 px-1" :class="paidAmount >= total ? 'text-emerald-500' : 'text-red-400'">
                        <span x-text="paidAmount >= total ? 'Kembalian' : 'Kurang Bayar (masuk utang)'"></span>
                        <span x-text="formatRupiah(Math.abs(paidAmount - total))"></span>
                    </div>

                    {{-- Peringatan kurang bayar --}}
                    <div x-show="paidAmount < total && paidAmount >= 0" x-cloak
                         class="mb-4 px-3 py-2 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/50 rounded-xl text-xs font-semibold text-red-600 dark:text-red-400">
                        ⚠️ Selisih Rp <span x-text="formatRupiah(total - paidAmount)"></span> akan otomatis dicatat sebagai utang pelanggan.
                        Pastikan nama pelanggan sudah diisi.
                    </div>

                    {{-- Tenggat utang (tampil kalau kurang bayar) --}}
                    <div x-show="paidAmount < total" x-cloak class="mb-4">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 block">Tenggat Pelunasan Utang (opsional)</label>
                        <input type="date" x-model="dueDate"
                               :min="new Date(Date.now() + 86400000).toISOString().split('T')[0]"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div class="flex gap-3 mt-2">
                        <button type="button" @click="showModal = false" class="w-1/3 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 transition-colors">Batal</button>
                        <button type="button" @click="checkout()"
                                :disabled="processing || (paidAmount < total && !activeCart.customerName)"
                                class="w-2/3 py-3 rounded-xl font-bold text-white bg-slate-900 dark:bg-indigo-500 hover:bg-indigo-600 transition-colors disabled:opacity-40 flex items-center justify-center gap-2 shadow-md">
                            <svg x-show="processing" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v2a8 8 0 100 16v-2"></path></svg>
                            <span x-text="processing ? 'Memproses...' : (paidAmount < total ? 'Catat + Utang' : 'Konfirmasi & Cetak')"></span>
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="result">
                <div class="text-center py-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                         :class="result.has_debt ? 'bg-amber-100 dark:bg-amber-900/40' : 'bg-emerald-100 dark:bg-emerald-900/50'">
                        <svg x-show="!result.has_debt" class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                        <svg x-show="result.has_debt" class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1" x-text="result.has_debt ? 'Transaksi + Utang Tercatat' : 'Berhasil!'"></h3>
                    <p class="text-xs font-semibold text-slate-400 mb-1" x-text="result.invoice_number"></p>
                    <p x-show="result.transaction?.customer_name" class="text-xs font-bold text-indigo-500 mb-1" x-text="'Pelanggan: ' + (result.transaction?.customer_name ?? '')"></p>
                    <p x-show="result.has_debt" class="text-xs font-bold text-amber-600 dark:text-amber-400 mb-4" x-text="'Utang: Rp ' + formatRupiah(result.debt_amount)"></p>
                    <div class="flex gap-2 mt-6">
                        <button type="button" @click="printReceipt()" class="flex-1 py-3 rounded-xl font-bold text-white bg-indigo-500 hover:bg-indigo-600 transition-colors text-sm shadow-md">Cetak Struk</button>
                        <button type="button" @click="closeModal()" class="flex-1 py-3 rounded-xl font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 transition-colors text-sm">Transaksi Baru</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Struk --}}
    <div id="receipt-print" class="hidden" x-show="result">
        <div style="font-family: monospace; padding: 10px; max-width: 300px; color: black; background: white;">
            <h2 style="text-align:center; margin:0; font-size: 16px;">Dinda Perabot</h2>
            <p style="text-align:center; font-size:10px; margin-bottom:10px;">{{ now()->format('d/m/Y H:i') }} | Kasir: {{ Auth::user()->name ?? '-' }}</p>
            <div style="border-top:1px dashed black; margin-bottom:5px;"></div>
            <p style="font-size:11px; margin:2px 0;">No: <span x-text="result?.invoice_number"></span></p>
            <p x-show="result?.transaction?.customer_name" style="font-size:11px; margin:2px 0;">Pelanggan: <span x-text="result?.transaction?.customer_name"></span></p>
            <div style="border-top:1px dashed black; margin-bottom:5px;"></div>
            <template x-for="line in (result?.transaction?.items || [])" :key="line.id">
                <div style="font-size:11px; margin-bottom:6px;">
                    <div x-text="line.product_name" style="font-weight:bold;"></div>
                    <div style="display:flex; justify-content:space-between;">
                        <span><span x-text="formatQty(line.quantity)"></span><span x-text="line.unit"></span> x <span x-text="formatRupiah(line.price)"></span></span>
                        <span x-text="formatRupiah(line.subtotal)"></span>
                    </div>
                </div>
            </template>
            <div style="border-top:1px dashed black; margin:5px 0;"></div>
            <div style="display:flex; justify-content:space-between; font-size:11px;"><span>Subtotal</span><span x-text="formatRupiah(result?.transaction?.subtotal)"></span></div>
            <div style="display:flex; justify-content:space-between; font-size:11px;"><span>PPN 11%</span><span x-text="formatRupiah(result?.transaction?.tax)"></span></div>
            <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:bold; margin-top:5px;"><span>TOTAL</span><span x-text="formatRupiah(result?.transaction?.total)"></span></div>
            <div style="display:flex; justify-content:space-between; font-size:11px; margin-top:5px;"><span>Bayar</span><span x-text="formatRupiah(result?.transaction?.paid_amount)"></span></div>
            <div x-show="result?.has_debt" style="display:flex; justify-content:space-between; font-size:11px; color: red;"><span>Sisa Utang</span><span x-text="formatRupiah(result?.debt_amount)"></span></div>
            <div x-show="!result?.has_debt" style="display:flex; justify-content:space-between; font-size:11px;"><span>Kembali</span><span x-text="formatRupiah(result?.transaction?.change_amount)"></span></div>
            <div style="border-top:1px dashed black; margin:10px 0;"></div>
            <p style="text-align:center; font-size:10px;">Terima kasih atas kunjungannya</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const COMPLETE_URL = '{{ url("/pos/queue") }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const CART_KEY = 'pos_carts_v1';

async function completeQueue(id, invoice, btn) {
    btn.disabled = true;
    btn.textContent = '...';
    try {
        const res = await fetch(`${COMPLETE_URL}/${id}/complete`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            window.dispatchEvent(new CustomEvent('toast', { detail: { msg: data.message, type: 'success' } }));
            btn.closest('.shrink-0').remove();
        } else {
            btn.disabled = false; btn.textContent = '✓ Barang Diserahkan';
        }
    } catch { btn.disabled = false; btn.textContent = '✓ Barang Diserahkan'; }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('posCart', () => ({
        carts: [{ id: 1, name: 'Antrean 1', customerName: '', customerId: null, customerDebt: 0, items: [] }],
        activeCartIndex: 0,
        editingCartIndex: null,
        editingName: '',

        taxRate: 0.11,
        showModal: false, processing: false, result: null,
        paymentMethod: 'cash', paidAmount: 0, dueDate: '',
        barcodeBuffer: '', barcodeTimer: null,
        currentTime: '',
        showNumpad: false, numpadTarget: null, numpadBuffer: '',

        // ---- PERSISTENSI KERANJANG ----
        // Keranjang disimpan ke sessionStorage agar tidak hilang saat
        // filter kategori / pencarian menyebabkan page reload.
        saveState() {
            try {
                sessionStorage.setItem(CART_KEY, JSON.stringify({
                    carts: this.carts,
                    activeCartIndex: this.activeCartIndex,
                }));
            } catch (e) {}
        },
        loadState() {
            try {
                const raw = sessionStorage.getItem(CART_KEY);
                if (!raw) return;
                const saved = JSON.parse(raw);
                // Validasi minimal: harus array dengan setidaknya satu item
                if (Array.isArray(saved.carts) && saved.carts.length > 0) {
                    // Pastikan setiap cart punya semua field yang dibutuhkan
                    this.carts = saved.carts.map(c => ({
                        id:           c.id            ?? 1,
                        name:         c.name          ?? 'Antrean 1',
                        customerName: c.customerName  ?? '',
                        customerId:   c.customerId    ?? null,
                        customerDebt: c.customerDebt  ?? 0,
                        items:        Array.isArray(c.items) ? c.items : [],
                    }));
                    const idx = saved.activeCartIndex ?? 0;
                    this.activeCartIndex = idx < this.carts.length ? idx : 0;
                }
            } catch (e) {
                // Session storage corrupt / invalid — biarkan state default
            }
        },

        init() {
            // Restore keranjang dari session sebelum apapun
            this.loadState();

            this.updateTime();
            setInterval(() => this.updateTime(), 1000);

            // Pantau perubahan carts dan activeCartIndex, simpan otomatis
            this.$watch('carts', () => this.saveState(), { deep: true });
            this.$watch('activeCartIndex', () => this.saveState());
        },
        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + ' — ' + now.toLocaleTimeString('id-ID');
        },

        get activeCart() { return this.carts[this.activeCartIndex]; },
        get items()      { return this.activeCart?.items ?? []; },
        set items(val)   { if (this.activeCart) this.activeCart.items = val; },

        // ---- RENAME ----
        startRename(i) {
            this.activeCartIndex = i; this.editingCartIndex = i;
            this.editingName = this.carts[i].name;
            this.$nextTick(() => { const el = this.$refs.renameInput; if (el) { el.focus(); el.select(); } });
        },
        commitRename() {
            if (this.editingCartIndex !== null) {
                const t = this.editingName.trim(); if (t) this.carts[this.editingCartIndex].name = t;
            }
            this.editingCartIndex = null; this.editingName = '';
        },

        // ---- AUTOCOMPLETE ----
        async searchCustomer(q, suggestions, cb) {
            if (!q || q.length < 2) { suggestions.length = 0; return; }
            try {
                const res = await fetch(`{{ route('customer.search') }}?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                suggestions.splice(0, suggestions.length, ...data);
                cb();
            } catch {}
        },
        selectCustomer(s) {
            this.activeCart.customerName  = s.name;
            this.activeCart.customerId    = s.id;
            this.activeCart.customerDebt  = parseFloat(s.total_debt) || 0;
        },

        // ---- HELPERS ----
        clean(n)          { return Math.round((Number(n) + Number.EPSILON) * 1000) / 1000; },
        get subtotal()    { return this.items.reduce((s, i) => s + i.price * i.qty, 0); },
        get tax()         { return this.subtotal * this.taxRate; },
        get total()       { return this.subtotal + this.tax; },
        formatRupiah(n)   { return new Intl.NumberFormat('id-ID').format(Math.round(Number(n) || 0)); },
        formatQty(q)      { return parseFloat(Number(q).toFixed(3)).toString(); },
        beep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator(); const g = ctx.createGain();
                osc.connect(g); g.connect(ctx.destination);
                osc.type = 'sine'; osc.frequency.value = 800;
                g.gain.setValueAtTime(0.05, ctx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.1);
                osc.start(); osc.stop(ctx.currentTime + 0.1);
            } catch {}
        },

        // ---- NUMPAD ----
        openNumpad(item)  { this.numpadTarget = item; this.numpadBuffer = ''; this.showNumpad = true; },
        closeNumpad()     { this.showNumpad = false; setTimeout(() => { this.numpadTarget = null; this.numpadBuffer = ''; }, 200); },
        pressNumpad(val)  {
            this.beep();
            if (val === 'del') this.numpadBuffer = this.numpadBuffer.slice(0, -1);
            else if (val === '.') { if (!this.numpadBuffer.includes('.') && this.numpadTarget?.unit_type === 'decimal') this.numpadBuffer += '.'; }
            else this.numpadBuffer = this.numpadBuffer === '0' ? val : this.numpadBuffer + val;
        },
        confirmNumpad()   { if (this.numpadTarget && this.numpadBuffer !== '') this.updateQty(this.numpadTarget, this.numpadBuffer); this.closeNumpad(); },

        // ---- ANTREAN ----
        newCart() {
            const id = this.carts.length ? Math.max(...this.carts.map(c => c.id)) + 1 : 1;
            this.carts.push({ id, name: 'Antrean ' + id, customerName: '', customerId: null, customerDebt: 0, items: [] });
            this.activeCartIndex = this.carts.length - 1;
            window.dispatchEvent(new CustomEvent('toast', { detail: { msg: 'Antrean baru dibuka', type: 'info' } }));
        },
        closeCart(idx) {
            this.carts.splice(idx, 1);
            if (!this.carts.length) this.newCart();
            if (this.activeCartIndex >= this.carts.length) this.activeCartIndex = this.carts.length - 1;
        },

        // ---- ITEM ----
        addItem(data) {
            this.beep();
            const id = parseInt(data.id); const stock = parseFloat(data.stock);
            const dec = data.unitType === 'decimal'; const step = dec ? 0.5 : 1;
            const found = this.items.find(i => i.id === id);
            if (found) { const n = this.clean(found.qty + step); found.qty = n > stock ? stock : n; }
            else this.items.push({ id, name: data.name, price: parseFloat(data.price), unit: data.unit, unit_type: data.unitType, stock, qty: dec ? 0.5 : 1 });
            window.dispatchEvent(new CustomEvent('toast', { detail: { msg: `${data.name} ditambahkan`, type: 'success' } }));
        },
        increment(item) { this.beep(); const s = item.unit_type === 'decimal' ? 0.5 : 1; item.qty = this.clean(item.qty + s) > item.stock ? item.stock : this.clean(item.qty + s); },
        decrement(item) { const s = item.unit_type === 'decimal' ? 0.5 : 1; const n = this.clean(item.qty - s); if (n <= 0) { this.removeItem(item.id); return; } item.qty = n; },
        updateQty(item, v) {
            let q = parseFloat(v); if (isNaN(q) || q < 0) q = 0;
            if (q > item.stock) { q = item.stock; window.dispatchEvent(new CustomEvent('toast', { detail: { msg: `Stok maks: ${item.stock}`, type: 'error' } })); }
            item.qty = this.clean(q); if (!item.qty) this.removeItem(item.id);
        },
        removeItem(id) { this.items = this.items.filter(i => i.id !== id); },
        clearCart() {
            // Hapus isi antrean aktif saja; antrean lain tetap ada
            if (this.activeCart) {
                this.activeCart.items        = [];
                this.activeCart.customerName = '';
                this.activeCart.customerId   = null;
                this.activeCart.customerDebt = 0;
            }
            // saveState() otomatis terpanggil via $watch
        },

        openCheckout() {
            if (!this.items.length) { window.dispatchEvent(new CustomEvent('toast', { detail: { msg: 'Keranjang kosong!', type: 'error' } })); return; }
            this.result = null; this.paidAmount = Math.round(this.total); this.dueDate = ''; this.showModal = true;
        },

        handleKeydown(e) {
            if (this.editingCartIndex !== null) return;
            if (this.showNumpad) {
                if (/[0-9.]/.test(e.key)) this.pressNumpad(e.key);
                else if (e.key === 'Backspace') this.pressNumpad('del');
                else if (e.key === 'Enter') this.confirmNumpad();
                return;
            }
            if (e.key === 'Escape') { this.showModal = false; return; }
            if (e.key === 'F2') { e.preventDefault(); this.openCheckout(); return; }
            if (e.key === '/' && e.target.tagName !== 'INPUT') { e.preventDefault(); document.getElementById('searchInput').focus(); return; }
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (e.key.length === 1) this.barcodeBuffer += e.key;
            else if (e.key === 'Enter' && this.barcodeBuffer.length > 2) {
                const btn = document.querySelector(`button[data-sku="${this.barcodeBuffer}"]`);
                if (btn) btn.click(); else window.dispatchEvent(new CustomEvent('toast', { detail: { msg: 'Barcode tidak ditemukan', type: 'error' } }));
                this.barcodeBuffer = '';
            }
            clearTimeout(this.barcodeTimer); this.barcodeTimer = setTimeout(() => { this.barcodeBuffer = ''; }, 50);
        },

        async checkout() {
            if (this.processing) return;
            // Validasi lokal: kurang bayar wajib ada nama
            if (this.paidAmount < this.total && !this.activeCart.customerName?.trim()) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { msg: 'Nama pelanggan wajib diisi jika kurang bayar!', type: 'error' } }));
                return;
            }
            this.processing = true;
            try {
                const res = await fetch('{{ route("pos.checkout") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({
                        customer_name:  this.activeCart.customerName || null,
                        customer_id:    this.activeCart.customerId || null,
                        payment_method: this.paymentMethod,
                        paid_amount:    this.paidAmount,
                        due_date:       this.dueDate || null,
                        items: this.items.map(i => ({ id: i.id, qty: i.qty })),
                    }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Gagal diproses.');
                this.result = data;
            } catch (err) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { msg: err.message, type: 'error' } }));
            } finally { this.processing = false; }
        },
        printReceipt() { window.print(); },
        closeModal()   { this.showModal = false; this.result = null; this.clearCart(); },
    }));
});
</script>
@endpush
