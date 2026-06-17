@extends('layouts.app')

@section('title', 'Kasir POS')
@php
    $activeMenu = 'pos';
@endphp

@section('content')

<div x-data="posCart" class="flex-1 h-full flex gap-8">

    {{-- AREA TENGAH --}}
    <main class="flex-1 h-full flex flex-col">
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-slate-900 leading-tight">Welcome to <br> BuildPro.</h1>
            </div>
            <div class="flex gap-4">
                <form action="{{ route('pos.index') }}" method="GET" class="relative">
                    <svg class="w-5 h-5 absolute left-4 top-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari bahan..."
                           class="w-64 pl-12 pr-4 py-3 bg-slate-50 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm transition-all shadow-sm">
                </form>
                <button type="button" class="w-12 h-12 bg-slate-900 text-white rounded-2xl flex items-center justify-center shadow-lg hover:bg-indigo-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </button>
            </div>
        </header>

        {{-- Kategori --}}
        <div class="flex gap-4 mb-10 overflow-x-auto pb-4">
            @php
                $activeCat = request('category');
            @endphp
            
            @foreach ($categories as $cat)
                @php
                    $isActive = ($activeCat == $cat['value']);
                    $routeUrl = route('pos.index', array_filter(['category' => $cat['value']]));
                @endphp
                <a href="{{ $routeUrl }}"
                   class="w-[88px] h-32 rounded-full flex flex-col items-center justify-center gap-3 cursor-pointer hover:-translate-y-1 transition-transform {{ $isActive ? 'bg-indigo-500 shadow-lg' : 'bg-white shadow-sm border border-slate-100' }}">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl {{ $isActive ? 'bg-white shadow-inner' : 'bg-slate-50' }}">
                        {{ $cat['icon'] }}
                    </div>
                    <span class="text-sm font-bold {{ $isActive ? 'text-white' : 'text-slate-400' }}">
                        {{ $cat['label'] }}
                    </span>
                </a>
            @endforeach
        </div>

        <div class="flex justify-between items-end mb-4">
            <h2 class="text-xl font-bold text-slate-900">Bahan Tersedia</h2>
            <span class="text-sm font-bold text-indigo-500 cursor-pointer">Lihat Semua →</span>
        </div>

        {{-- Grid produk dinamis --}}
        <div class="flex-1 overflow-y-auto pt-10 pb-4">
            <div class="grid grid-cols-2 xl:grid-cols-3 gap-6 gap-y-16">
                @forelse ($products as $product)
                    @php
                        $isLowStock = $product->stock <= $product->min_stock;
                    @endphp
                    
                    <div class="bg-[#F8FAFC] rounded-[32px] p-5 pt-16 relative shadow-sm hover:shadow-md transition-shadow group {{ $isLowStock ? 'border border-red-100' : '' }}">
                        @if ($isLowStock)
                            <div class="absolute top-4 right-4 w-3 h-3 bg-red-500 rounded-full animate-ping"
                                 title="Stok menipis: {{ $product->stock }} {{ $product->unit }}"></div>
                        @endif

                        <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-32 h-32 bg-slate-200 rounded-full border-4 border-white shadow-xl flex items-center justify-center text-slate-400 font-bold group-hover:scale-105 transition-transform overflow-hidden">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-3xl">{{ $product->category === 'Busa' ? '🧽' : ($product->category === 'Kain' ? '🧵' : '📦') }}</span>
                            @endif
                        </div>

                        <h3 class="font-bold text-lg text-slate-900 text-center mb-1">{{ $product->name }}</h3>
                        <p class="text-[11px] font-semibold text-slate-400 text-center mb-4 leading-relaxed">{{ $product->description }}</p>

                        <div class="flex justify-between items-center mt-auto">
                            <p class="text-xl font-black text-slate-900">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                                <span class="text-xs text-slate-400 font-bold">/ {{ $product->unit }}</span>
                            </p>

                            {{-- Tombol tambah --}}
                            <button type="button"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->price }}"
                                    data-unit="{{ $product->unit }}"
                                    data-unit-type="{{ $product->unit_type }}"
                                    data-stock="{{ $product->stock }}"
                                    @click="addItem($event.currentTarget.dataset)"
                                    {{ $product->stock <= 0 ? 'disabled' : '' }}
                                    class="w-10 h-10 bg-slate-900 text-white rounded-full flex items-center justify-center shadow-md hover:bg-indigo-500 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-slate-300 py-16 font-bold">Tidak ada produk yang cocok.</div>
                @endforelse
            </div>
        </div>
    </main>

    {{-- AREA KANAN (Keranjang) --}}
    <x-cart />

    {{-- MODAL CHECKOUT --}}
    <div x-show="showModal" x-cloak
         class="absolute inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm rounded-[40px]"
         @keydown.escape.window="showModal = false">
        <div class="bg-white w-[420px] rounded-[32px] p-8 shadow-2xl"
             @click.outside="showModal = false"
             x-transition.scale.origin.center>

            {{-- STATE 1: Pembayaran --}}
            <template x-if="!result">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-1">Pembayaran</h3>
                    <p class="text-sm font-semibold text-slate-400 mb-6">Periksa kembali sebelum konfirmasi.</p>

                    <div class="bg-slate-50 rounded-2xl p-5 mb-5 space-y-2">
                        <div class="flex justify-between text-sm font-bold text-slate-500"><span>Subtotal</span><span x-text="formatRupiah(subtotal)"></span></div>
                        <div class="flex justify-between text-sm font-bold text-slate-500"><span>Pajak (11%)</span><span x-text="formatRupiah(tax)"></span></div>
                        <div class="flex justify-between text-lg font-black text-slate-900 pt-2 border-t border-slate-200"><span>Total</span><span x-text="formatRupiah(total)"></span></div>
                    </div>

                    <label class="text-xs font-bold text-slate-400">Metode Bayar</label>
                    <div class="flex gap-2 mt-2 mb-4">
                        <template x-for="m in ['cash','qris','transfer','debit']" :key="m">
                            <button type="button" @click="paymentMethod = m"
                                    :class="paymentMethod === m ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-500'"
                                    class="flex-1 py-2 rounded-xl text-xs font-bold uppercase transition-colors" x-text="m"></button>
                        </template>
                    </div>

                    <label class="text-xs font-bold text-slate-400">Uang Diterima</label>
                    <input type="number" x-model.number="paidAmount" min="0"
                           class="w-full mt-2 mb-2 px-4 py-3 bg-slate-50 rounded-2xl font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="flex justify-between text-sm font-bold mb-6"
                         :class="paidAmount >= total ? 'text-emerald-500' : 'text-red-400'">
                        <span>Kembalian</span>
                        <span x-text="formatRupiah(Math.max(0, paidAmount - total))"></span>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="showModal = false"
                                class="flex-1 py-4 rounded-2xl font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 transition-colors">Batal</button>
                        <button type="button" @click="checkout()"
                                :disabled="processing || paidAmount < total"
                                class="flex-[2] py-4 rounded-2xl font-bold text-white bg-slate-900 hover:bg-indigo-600 transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg x-show="processing" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v2a8 8 0 100 16v-2"></path></svg>
                            <span x-text="processing ? 'Memproses...' : 'Konfirmasi & Cetak'"></span>
                        </button>
                    </div>
                </div>
            </template>

            {{-- STATE 2: Sukses --}}
            <template x-if="result">
                <div class="text-center">
                    <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-1">Transaksi Berhasil</h3>
                    <p class="text-sm font-semibold text-slate-400 mb-1">No. Invoice</p>
                    <p class="text-lg font-black text-indigo-600 mb-6" x-text="result?.invoice_number"></p>
                    <div class="flex gap-3">
                        <button type="button" @click="printReceipt()"
                                class="flex-1 py-4 rounded-2xl font-bold text-white bg-slate-900 hover:bg-indigo-600 transition-colors">Cetak Struk</button>
                        <button type="button" @click="closeModal()"
                                class="flex-1 py-4 rounded-2xl font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 transition-colors">Transaksi Baru</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Area struk untuk dicetak (tersembunyi di layar, tampil saat print) --}}
    <div id="receipt-print" class="hidden" x-show="result">
        <div style="font-family: monospace; padding: 20px; max-width: 300px;">
            <h2 style="text-align:center; margin:0;">BuildPro</h2>
            <p style="text-align:center; font-size:12px;">Toko Bahan Perabot</p>
            <hr>
            <p style="font-size:12px;">No: <span x-text="result?.invoice_number"></span></p>
            <hr>
            <template x-for="line in (result?.transaction?.items || [])" :key="line.id">
                <div style="font-size:12px; margin-bottom:4px;">
                    <div x-text="line.product_name"></div>
                    <div style="display:flex; justify-content:space-between;">
                        <span><span x-text="formatQty(line.quantity)"></span> <span x-text="line.unit"></span> x <span x-text="formatRupiah(line.price)"></span></span>
                        <span x-text="formatRupiah(line.subtotal)"></span>
                    </div>
                </div>
            </template>
            <hr>
            <div style="display:flex; justify-content:space-between; font-size:12px;"><span>Subtotal</span><span x-text="formatRupiah(result?.transaction?.subtotal)"></span></div>
            <div style="display:flex; justify-content:space-between; font-size:12px;"><span>Pajak</span><span x-text="formatRupiah(result?.transaction?.tax)"></span></div>
            <div style="display:flex; justify-content:space-between; font-weight:bold;"><span>TOTAL</span><span x-text="formatRupiah(result?.transaction?.total)"></span></div>
            <p style="text-align:center; font-size:11px; margin-top:12px;">Terima kasih telah berbelanja</p>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('posCart', () => ({
            items: [],
            taxRate: 0.11,
            showModal: false,
            processing: false,
            result: null,
            paymentMethod: 'cash',
            paidAmount: 0,

            clean(n) { return Math.round((Number(n) + Number.EPSILON) * 1000) / 1000; },

            get subtotal() { return this.items.reduce((s, i) => s + i.price * i.qty, 0); },
            get tax()      { return this.subtotal * this.taxRate; },
            get total()    { return this.subtotal + this.tax; },

            formatRupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(n) || 0)); },
            formatQty(q) { return parseFloat(Number(q).toFixed(3)).toString(); },

            addItem(data) {
                const id = parseInt(data.id);
                const stock = parseFloat(data.stock);
                const isDecimal = data.unitType === 'decimal';
                const stepAdd = isDecimal ? 0.5 : 1; 

                const found = this.items.find(i => i.id === id);
                if (found) {
                    const next = this.clean(found.qty + stepAdd);
                    found.qty = next > stock ? stock : next;
                    return;
                }
                this.items.push({
                    id,
                    name: data.name,
                    price: parseFloat(data.price),
                    unit: data.unit,
                    unit_type: data.unitType,
                    stock,
                    qty: isDecimal ? 0.5 : 1,
                });
            },
            increment(item) {
                const step = item.unit_type === 'decimal' ? 0.5 : 1;
                const next = this.clean(item.qty + step);
                item.qty = next > item.stock ? item.stock : next;
            },
            decrement(item) {
                const step = item.unit_type === 'decimal' ? 0.5 : 1;
                const next = this.clean(item.qty - step);
                if (next <= 0) { this.removeItem(item.id); return; }
                item.qty = next;
            },
            updateQty(item, value) {
                let q = parseFloat(value);
                if (isNaN(q) || q < 0) q = 0;
                if (q > item.stock) q = item.stock;
                item.qty = this.clean(q);
                if (item.qty === 0) this.removeItem(item.id);
            },
            removeItem(id) { this.items = this.items.filter(i => i.id !== id); },
            clearCart()    { this.items = []; },

            openCheckout() {
                if (this.items.length === 0) return;
                this.result = null;
                this.paidAmount = Math.round(this.total); 
                this.showModal = true;
            },
            async checkout() {
                if (this.processing) return;
                this.processing = true;
                try {
                    const res = await fetch('{{ route('pos.checkout') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            payment_method: this.paymentMethod,
                            paid_amount: this.paidAmount,
                            items: this.items.map(i => ({ id: i.id, qty: i.qty })),
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Gagal memproses transaksi.');
                    }
                    this.result = data; 
                } catch (err) {
                    alert(err.message);
                } finally {
                    this.processing = false;
                }
            },
            printReceipt() { window.print(); },
            closeModal() {
                this.showModal = false;
                this.result = null;
                this.clearCart(); 
            },
        }));
    });
</script>
@endpush