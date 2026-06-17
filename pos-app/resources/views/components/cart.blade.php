{{--
    Panel Keranjang (kanan) — versi REAKTIF (Alpine.js).
    Komponen ini TIDAK punya x-data sendiri; ia hidup di dalam scope
    x-data="posCart" yang dibuka di pos/index.blade.php, sehingga otomatis
    ikut ter-update saat produk ditambahkan dari grid katalog.
--}}
<aside class="w-[340px] h-full flex flex-col">

    {{-- Promo Card --}}
    <div class="bg-indigo-500 rounded-[28px] p-6 mb-6 relative overflow-hidden shadow-[0_10px_20px_rgba(99,102,241,0.2)]">
        <div class="relative z-10 w-2/3">
            <h3 class="text-white font-bold text-xl mb-1">Diskon 5%</h3>
            <p class="text-indigo-100 text-sm font-medium leading-snug">Untuk pembelian Kain Oscar &gt; 10m</p>
        </div>
        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/20 rounded-full blur-xl"></div>
    </div>

    {{-- Header Pesanan --}}
    <div class="flex justify-between items-end mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Pesanan
            <span class="text-sm font-bold text-slate-400" x-text="'(' + items.length + ')'"></span>
        </h2>
        <span class="text-sm font-bold text-red-400 hover:text-red-500 cursor-pointer"
              @click="clearCart()">Kosongkan</span>
    </div>

    {{-- Daftar Item di Keranjang --}}
    <div class="flex-1 overflow-y-auto flex flex-col gap-5 pb-4 border-b border-slate-100 mb-6">

        {{-- Keadaan kosong --}}
        <div x-show="items.length === 0" x-cloak
             class="flex-1 flex flex-col items-center justify-center text-center text-slate-300 py-10">
            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <p class="font-bold text-sm">Keranjang masih kosong</p>
            <p class="text-xs">Pilih bahan untuk memulai transaksi</p>
        </div>

        {{-- Item dinamis --}}
        <template x-for="item in items" :key="item.id">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex-shrink-0 flex items-center justify-center text-xs font-bold text-slate-400">IMG</div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-sm text-slate-900 leading-tight truncate" x-text="item.name"></h4>
                    <p class="text-xs font-semibold text-slate-400 mb-1">
                        <span x-text="formatRupiah(item.price)"></span> / <span x-text="item.unit"></span>
                    </p>
                    <p class="font-bold text-sm text-slate-900" x-text="formatRupiah(item.price * item.qty)"></p>
                </div>

                {{-- Input Kuantitas (mendukung desimal). step menyesuaikan tipe satuan. --}}
                <div class="flex items-center bg-[#F1F5F9] rounded-full p-1 border border-slate-200">
                    <button class="w-6 h-6 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-500 font-bold hover:text-red-500"
                            @click="decrement(item)">-</button>
                    <input type="number" min="0"
                           :step="item.unit_type === 'decimal' ? '0.1' : '1'"
                           :value="formatQty(item.qty)"
                           @input="updateQty(item, $event.target.value)"
                           class="w-12 bg-transparent text-center text-sm font-bold text-slate-800 focus:outline-none">
                    <button class="w-6 h-6 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-500 font-bold hover:text-indigo-500"
                            @click="increment(item)">+</button>
                </div>
            </div>
        </template>
    </div>

    {{-- Ringkasan Harga (real-time) --}}
    <div>
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-bold text-slate-400">Sub Total</span>
            <span class="font-bold text-slate-900" x-text="formatRupiah(subtotal)"></span>
        </div>
        <div class="flex justify-between items-center mb-6">
            <span class="text-sm font-bold text-slate-400">Pajak (11%)</span>
            <span class="font-bold text-slate-900" x-text="formatRupiah(tax)"></span>
        </div>

        <button @click="openCheckout()"
                :disabled="items.length === 0"
                class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-5 rounded-[24px] shadow-xl shadow-slate-900/20 transition-all flex justify-between items-center px-8 disabled:opacity-40 disabled:cursor-not-allowed">
            <span>Submit Order</span>
            <span class="text-lg" x-text="formatRupiah(total)"></span>
        </button>
    </div>
</aside>
