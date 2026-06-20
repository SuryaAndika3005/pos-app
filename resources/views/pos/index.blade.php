@extends('layouts.app')

@section('title', 'Terminal Kasir')
<?php $activeMenu = 'pos'; ?>

@section('content')

<div x-data="posCart" @keydown.window="handleKeydown" class="flex-1 h-full flex gap-4 min-w-0">

    {{-- ===================== AREA KATALOG ===================== --}}
    <main class="flex-1 h-full flex flex-col min-w-0">
        
        {{-- Header, Pencarian, dan Jam Digital --}}
        <header class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-[28px] font-black text-slate-900 dark:text-white tracking-tight leading-tight">Dinda Perabot.</h1>
                {{-- Jam Realtime --}}
                <p class="text-xs font-bold text-slate-400 mt-1" x-text="currentTime"></p>
            </div>
            <div class="flex gap-2 items-end">
                <form action="{{ route('pos.index') }}" method="GET" class="relative">
                    <svg class="w-5 h-5 absolute left-4 top-2.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="searchInput" name="q" value="{{ request('q') }}" placeholder="Cari bahan (Tekan '/')"
                           class="w-64 pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-transparent dark:border-slate-700 rounded-2xl focus:outline-none focus:border-indigo-500 font-semibold text-sm transition-all shadow-sm dark:text-white dark:placeholder-slate-500">
                </form>
            </div>
        </header>

        {{-- Filter Kategori (Pil Mendatar) --}}
        <div class="flex gap-2 mb-4 overflow-x-auto pb-1 hide-scrollbar">
            @foreach ($categories as $cat)
                <a href="{{ route('pos.index', $cat['value'] ? ['category' => $cat['value']] : []) }}" 
                   class="flex items-center gap-2 px-4 py-1.5 rounded-full cursor-pointer transition-all border {{ request('category') == $cat['value'] ? 'bg-slate-900 dark:bg-indigo-500 text-white border-slate-900 dark:border-indigo-500 shadow-md' : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-400 shadow-sm' }}">
                    <span class="text-sm">{{ $cat['icon'] }}</span>
                    <span class="text-xs font-bold whitespace-nowrap">{{ $cat['label'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="flex justify-between items-end mb-2 px-1">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Bahan Tersedia</h2>
            <span class="text-xs font-bold text-indigo-500 dark:text-indigo-400 cursor-pointer">Lihat Semua →</span>
        </div>

        {{-- Grid Produk --}}
        <div class="flex-1 overflow-y-auto pb-4 pr-2 hide-scrollbar">
            <div class="grid grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                @forelse ($products as $product)
                    <div class="bg-white dark:bg-slate-800 border {{ $product->stock <= $product->min_stock ? 'border-red-300 dark:border-red-500/50 bg-red-50/30 dark:bg-red-900/10' : 'border-slate-100 dark:border-slate-700' }} rounded-[20px] p-3 shadow-sm hover:shadow-md transition-all group flex flex-col">
                        <div class="w-full h-28 bg-slate-50 dark:bg-slate-700/50 rounded-[14px] mb-3 flex items-center justify-center relative overflow-hidden group-hover:bg-indigo-50/50 dark:group-hover:bg-slate-700 transition-colors">
                            @if ($product->stock <= $product->min_stock)
                                <div class="absolute top-2 right-2 bg-red-500 text-white text-[9px] font-bold px-2 py-0.5 rounded shadow-sm z-10 animate-pulse">Sisa {{ $product->stock }}</div>
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
                                    <p class="text-[9px] font-semibold text-slate-400 dark:text-slate-500 mb-0.5">Rp / {{ $product->unit }}</p>
                                    <p class="text-sm font-black text-slate-900 dark:text-white leading-none">{{ number_format($product->price, 0, ',', '.') }}</p>
                                </div>
                                <button type="button"
                                        data-id="{{ $product->id }}" data-sku="{{ $product->sku ?? $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}" data-unit="{{ $product->unit }}" data-unit-type="{{ $product->unit_type }}" data-stock="{{ $product->stock }}"
                                        @click="addItem($event.currentTarget.dataset)"
                                        {{ $product->stock <= 0 ? 'disabled' : '' }}
                                        class="w-8 h-8 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-[10px] flex items-center justify-center hover:bg-indigo-500 hover:text-white dark:hover:bg-indigo-500 transition-colors disabled:opacity-40 disabled:cursor-not-allowed shrink-0 active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-600 py-16">
                        <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <p class="font-bold text-lg">Bahan tidak ditemukan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </main>

    {{-- ===================== AREA KERANJANG ===================== --}}
    <x-cart />

    {{-- ===================== MODAL NUMPAD (BARU) ===================== --}}
    <div x-show="showNumpad" x-cloak class="absolute inset-0 z-[60] flex items-center justify-center bg-slate-900/40 dark:bg-black/60 backdrop-blur-sm rounded-[32px]" @keydown.escape.window="closeNumpad()">
        <div class="bg-white dark:bg-slate-800 w-[300px] rounded-[28px] p-6 shadow-2xl border dark:border-slate-700" @click.outside="closeNumpad()" x-transition.scale.origin.center>
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-slate-900 dark:text-white line-clamp-1" x-text="numpadTarget ? numpadTarget.name : ''"></h3>
                <button @click="closeNumpad()" class="text-slate-400 hover:text-red-500">✕</button>
            </div>
            
            {{-- Layar Kalkulator --}}
            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-3 mb-4 text-right text-3xl font-black text-indigo-500 tracking-wider overflow-hidden">
                <span x-text="numpadBuffer === '' ? '0' : numpadBuffer"></span>
                <span class="text-sm text-slate-400 ml-1" x-text="numpadTarget ? numpadTarget.unit : ''"></span>
            </div>

            {{-- Tombol Numpad --}}
            <div class="grid grid-cols-3 gap-2">
                <template x-for="n in ['7','8','9','4','5','6','1','2','3']" :key="n">
                    <button @click="pressNumpad(n)" class="py-3 bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white font-bold text-lg rounded-xl hover:bg-indigo-500 hover:text-white transition-colors active:scale-95" x-text="n"></button>
                </template>
                <button @click="pressNumpad('.')" class="py-3 bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white font-bold text-lg rounded-xl hover:bg-indigo-500 hover:text-white transition-colors active:scale-95" :disabled="numpadTarget && numpadTarget.unit_type !== 'decimal'">.</button>
                <button @click="pressNumpad('0')" class="py-3 bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white font-bold text-lg rounded-xl hover:bg-indigo-500 hover:text-white transition-colors active:scale-95">0</button>
                <button @click="pressNumpad('del')" class="py-3 bg-red-100 dark:bg-red-900/40 text-red-500 font-bold text-lg rounded-xl hover:bg-red-500 hover:text-white transition-colors active:scale-95">⌫</button>
            </div>

            <button @click="confirmNumpad()" class="w-full mt-4 py-3 bg-slate-900 dark:bg-indigo-500 text-white font-bold rounded-xl hover:bg-indigo-600 transition-colors shadow-md active:scale-95">Simpan Kuantitas</button>
        </div>
    </div>

    {{-- ===================== MODAL PEMBAYARAN ===================== --}}
    <div x-show="showModal" x-cloak class="absolute inset-0 z-50 flex items-center justify-center bg-slate-900/40 dark:bg-black/60 backdrop-blur-sm rounded-[32px]" @keydown.escape.window="showModal = false">
        <div class="bg-white dark:bg-slate-800 w-[420px] rounded-[28px] p-7 shadow-2xl border dark:border-slate-700" @click.outside="showModal = false" x-transition.scale.origin.center>

            <template x-if="!result">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Proses Pembayaran</h3>
                    <p class="text-xs font-semibold text-slate-400 mb-5">Selesaikan transaksi keranjang aktif.</p>

                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4 mb-4 border border-slate-100 dark:border-slate-700">
                        <div class="flex justify-between text-lg font-black text-slate-900 dark:text-white"><span>Total Tagihan</span><span x-text="formatRupiah(total)"></span></div>
                    </div>

                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 block">Metode Bayar</label>
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <template x-for="m in ['cash','qris','transfer','debit']" :key="m">
                            <button type="button" @click="paymentMethod = m" :class="paymentMethod === m ? 'bg-indigo-500 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300'" class="py-2 rounded-xl text-xs font-bold uppercase transition-all" x-text="m"></button>
                        </template>
                    </div>

                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 block">Uang Diterima</label>
                    <input type="number" x-model.number="paidAmount" min="0" class="w-full mb-2 px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-lg text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500">
                    
                    {{-- Quick Cash Buttons (BARU) --}}
                    <div class="flex gap-2 mb-4">
                        <button type="button" @click="paidAmount = Math.round(total)" class="flex-1 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-800 transition-colors border border-indigo-100 dark:border-indigo-800/50">Uang Pas</button>
                        <button type="button" @click="paidAmount = 50000" class="flex-1 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">50k</button>
                        <button type="button" @click="paidAmount = 100000" class="flex-1 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">100k</button>
                    </div>

                    <div class="flex justify-between text-sm font-bold mb-6 px-1" :class="paidAmount >= total ? 'text-emerald-500' : 'text-red-400'">
                        <span>Kembalian</span><span x-text="formatRupiah(Math.max(0, paidAmount - total))"></span>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="showModal = false" class="w-1/3 py-3 rounded-xl font-bold text-slate-500 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Batal</button>
                        <button type="button" @click="checkout()" :disabled="processing || paidAmount < total" class="w-2/3 py-3 rounded-xl font-bold text-white bg-slate-900 dark:bg-indigo-500 hover:bg-indigo-600 transition-colors disabled:opacity-40 flex items-center justify-center gap-2 shadow-md">
                            <svg x-show="processing" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v2a8 8 0 100 16v-2"></path></svg>
                            <span x-text="processing ? 'Memproses...' : 'Konfirmasi & Cetak'"></span>
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="result">
                {{-- Sukses State (Tetap) --}}
                <div class="text-center py-4">
                    <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center mx-auto mb-4"><svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg></div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Berhasil!</h3>
                    <p class="text-xs font-semibold text-slate-400 mb-4" x-text="result?.invoice_number"></p>
                    <div class="flex gap-2 mt-6">
                        <button type="button" @click="printReceipt()" class="flex-1 py-3 rounded-xl font-bold text-white bg-indigo-500 hover:bg-indigo-600 transition-colors text-sm shadow-md">Cetak Struk</button>
                        <button type="button" @click="closeModal()" class="flex-1 py-3 rounded-xl font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 transition-colors text-sm">Transaksi Baru</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Area Struk Kertas (Tersembunyi) --}}
    <div id="receipt-print" class="hidden" x-show="result">
        {{-- Sama seperti sebelumnya --}}
        <div style="font-family: monospace; padding: 10px; max-width: 300px; color: black; background: white;">
            <h2 style="text-align:center; margin:0; font-size: 16px;">Dinda Perabot</h2>
            <p style="text-align:center; font-size:10px; margin-bottom:10px;">{{ now()->format('d/m/Y H:i') }} | Kasir: {{ Auth::user()->name ?? 'Utama' }}</p>
            <div style="border-top: 1px dashed black; margin-bottom: 5px;"></div>
            <p style="font-size:11px; margin: 2px 0;">No: <span x-text="result?.invoice_number"></span></p>
            <div style="border-top: 1px dashed black; margin-bottom: 5px;"></div>
            <template x-for="line in (result?.transaction?.items || [])" :key="line.id">
                <div style="font-size:11px; margin-bottom:6px;">
                    <div x-text="line.product_name" style="font-weight: bold;"></div>
                    <div style="display:flex; justify-content:space-between;">
                        <span><span x-text="formatQty(line.quantity)"></span><span x-text="line.unit"></span> x <span x-text="formatRupiah(line.price)"></span></span>
                        <span x-text="formatRupiah(line.subtotal)"></span>
                    </div>
                </div>
            </template>
            <div style="border-top: 1px dashed black; margin: 5px 0;"></div>
            <div style="display:flex; justify-content:space-between; font-size:11px;"><span>Subtotal</span><span x-text="formatRupiah(result?.transaction?.subtotal)"></span></div>
            <div style="display:flex; justify-content:space-between; font-size:11px;"><span>PPN 11%</span><span x-text="formatRupiah(result?.transaction?.tax)"></span></div>
            <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:bold; margin-top:5px;"><span>TOTAL</span><span x-text="formatRupiah(result?.transaction?.total)"></span></div>
            <div style="display:flex; justify-content:space-between; font-size:11px; margin-top:5px;"><span>Tunai/Bayar</span><span x-text="formatRupiah(result?.transaction?.paid_amount)"></span></div>
            <div style="display:flex; justify-content:space-between; font-size:11px;"><span>Kembali</span><span x-text="formatRupiah(result?.transaction?.change_amount)"></span></div>
            <div style="border-top: 1px dashed black; margin: 10px 0;"></div>
            <p style="text-align:center; font-size:10px;">Terima kasih atas kunjungannya</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('posCart', () => ({
            carts: [{ id: 1, name: 'Antrean 1', items: [] }],
            activeCartIndex: 0,
            
            taxRate: 0.11,
            showModal: false, processing: false, result: null,
            paymentMethod: 'cash', paidAmount: 0,
            barcodeBuffer: '', barcodeTimer: null,
            
            // Variabel Live Jam & Numpad
            currentTime: '',
            showNumpad: false, numpadTarget: null, numpadBuffer: '',

            init() {
                this.updateTime();
                setInterval(() => this.updateTime(), 1000);
            },
            updateTime() {
                const now = new Date();
                this.currentTime = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + ' - ' + now.toLocaleTimeString('id-ID');
            },

            get activeCart() { return this.carts[this.activeCartIndex]; },
            get items() { return this.activeCart ? this.activeCart.items : []; },
            set items(val) { if(this.activeCart) this.activeCart.items = val; },

            clean(n) { return Math.round((Number(n) + Number.EPSILON) * 1000) / 1000; },
            get subtotal() { return this.items.reduce((s, i) => s + i.price * i.qty, 0); },
            get tax()      { return this.subtotal * this.taxRate; },
            get total()    { return this.subtotal + this.tax; },
            formatRupiah(n) { return new Intl.NumberFormat('id-ID').format(Math.round(Number(n) || 0)); },
            formatQty(q) { return parseFloat(Number(q).toFixed(3)).toString(); },

            beep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator(); const gain = ctx.createGain();
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.type = 'sine'; osc.frequency.value = 800;
                    gain.gain.setValueAtTime(0.05, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.1);
                    osc.start(); osc.stop(ctx.currentTime + 0.1);
                } catch(e) {}
            },

            // --- FUNGSI NUMPAD VIRTUAL ---
            openNumpad(item) {
                this.numpadTarget = item;
                this.numpadBuffer = ''; // Mulai dari kosong agar kasir langsung ketik angka baru
                this.showNumpad = true;
            },
            closeNumpad() {
                this.showNumpad = false;
                setTimeout(() => { this.numpadTarget = null; this.numpadBuffer = ''; }, 200);
            },
            pressNumpad(val) {
                this.beep();
                if (val === 'del') {
                    this.numpadBuffer = this.numpadBuffer.slice(0, -1);
                } else if (val === '.') {
                    if (!this.numpadBuffer.includes('.')) this.numpadBuffer += val;
                } else {
                    if (this.numpadBuffer === '0') this.numpadBuffer = val;
                    else this.numpadBuffer += val;
                }
            },
            confirmNumpad() {
                if (this.numpadTarget && this.numpadBuffer !== '') {
                    this.updateQty(this.numpadTarget, this.numpadBuffer);
                }
                this.closeNumpad();
            },

            // Manajemen Antrean
            newCart() {
                const nextId = this.carts.length ? Math.max(...this.carts.map(c => c.id)) + 1 : 1;
                this.carts.push({ id: nextId, name: 'Antrean ' + nextId, items: [] });
                this.activeCartIndex = this.carts.length - 1;
                window.dispatchEvent(new CustomEvent('toast', { detail: { msg: 'Antrean baru dibuka', type: 'info' } }));
            },
            closeCart(idx) {
                this.carts.splice(idx, 1);
                if (this.carts.length === 0) this.newCart();
                if (this.activeCartIndex >= this.carts.length) this.activeCartIndex = this.carts.length - 1;
            },

            // Operasi Item
            addItem(data) {
                this.beep(); 
                const id = parseInt(data.id); const stock = parseFloat(data.stock);
                const isDecimal = data.unitType === 'decimal'; const stepAdd = isDecimal ? 0.5 : 1; 
                const found = this.items.find(i => i.id === id);
                
                if (found) {
                    const next = this.clean(found.qty + stepAdd);
                    found.qty = next > stock ? stock : next;
                } else {
                    this.items.push({ id, name: data.name, price: parseFloat(data.price), unit: data.unit, unit_type: data.unitType, stock, qty: isDecimal ? 0.5 : 1 });
                }
                window.dispatchEvent(new CustomEvent('toast', { detail: { msg: `${data.name} ditambahkan`, type: 'success' } }));
            },
            increment(item) { this.beep(); const step = item.unit_type === 'decimal' ? 0.5 : 1; item.qty = this.clean(item.qty + step) > item.stock ? item.stock : this.clean(item.qty + step); },
            decrement(item) { const step = item.unit_type === 'decimal' ? 0.5 : 1; const next = this.clean(item.qty - step); if (next <= 0) { this.removeItem(item.id); return; } item.qty = next; },
            updateQty(item, value) { let q = parseFloat(value); if (isNaN(q) || q < 0) q = 0; if (q > item.stock) { q = item.stock; window.dispatchEvent(new CustomEvent('toast', { detail: { msg: `Stok tidak cukup! Maks: ${item.stock}`, type: 'error' } })); } item.qty = this.clean(q); if (item.qty === 0) this.removeItem(item.id); },
            removeItem(id) { this.items = this.items.filter(i => i.id !== id); },
            clearCart()    { this.items = []; },

            openCheckout() {
                if (this.items.length === 0) { window.dispatchEvent(new CustomEvent('toast', { detail: { msg: 'Keranjang antrean ini kosong!', type: 'error' } })); return; }
                this.result = null; this.paidAmount = Math.round(this.total); this.showModal = true;
            },

            handleKeydown(e) {
                if (this.showNumpad) {
                    // Jika Numpad Terbuka, izinkan ketik angka dari keyboard fisik
                    if (/[0-9\.]/.test(e.key)) { this.pressNumpad(e.key); }
                    else if (e.key === 'Backspace') { this.pressNumpad('del'); }
                    else if (e.key === 'Enter') { this.confirmNumpad(); }
                    return;
                }
                
                if (e.key === 'Escape') { this.showModal = false; return; }
                if (e.key === 'F2') { e.preventDefault(); this.openCheckout(); return; }
                if (e.key === '/' && e.target.tagName !== 'INPUT') { e.preventDefault(); document.getElementById('searchInput').focus(); return; }
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

                if (e.key.length === 1) { 
                    this.barcodeBuffer += e.key; 
                } else if (e.key === 'Enter' && this.barcodeBuffer.length > 2) {
                    const btn = document.querySelector(`button[data-sku="${this.barcodeBuffer}"]`);
                    if (btn) btn.click(); else window.dispatchEvent(new CustomEvent('toast', { detail: { msg: 'Barcode tidak ditemukan', type: 'error' } }));
                    this.barcodeBuffer = '';
                }
                clearTimeout(this.barcodeTimer); this.barcodeTimer = setTimeout(() => { this.barcodeBuffer = ''; }, 50);
            },

            async checkout() {
                if (this.processing) return;
                this.processing = true;
                try {
                    const res = await fetch('{{ route('pos.checkout') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ payment_method: this.paymentMethod, paid_amount: this.paidAmount, items: this.items.map(i => ({ id: i.id, qty: i.qty })) }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) throw new Error(data.message || 'Gagal diproses.');
                    this.result = data; 
                } catch (err) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { msg: err.message, type: 'error' } }));
                } finally { this.processing = false; }
            },
            printReceipt() { window.print(); },
            closeModal() { this.showModal = false; this.result = null; this.clearCart(); },
        }));
    });
</script>
@endpush