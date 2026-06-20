{{--
    Panel Keranjang (kanan) — versi REAKTIF (Alpine.js).
    Komponen ini TIDAK punya x-data sendiri; ia hidup di dalam scope
    x-data="posCart" yang dibuka di pos/index.blade.php.
--}}
<aside class="w-[340px] h-full flex flex-col shrink-0 pl-2">
    
    {{-- Fitur Hold (Tab Antrean Pelanggan) --}}
    <div class="flex items-center gap-2 mb-4 overflow-x-auto hide-scrollbar pb-1">
        <template x-for="(cart, index) in carts" :key="cart.id">
            <div @click="activeCartIndex = index"
                 class="px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap cursor-pointer transition-all flex items-center gap-2 border"
                 :class="activeCartIndex === index ? 'bg-indigo-500 text-white border-indigo-500 shadow-md' : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'">
                <span x-text="cart.name"></span>
                {{-- Tombol Hapus Antrean (Hanya tampil jika ada lebih dari 1 antrean) --}}
                <button @click.stop="closeCart(index)" x-show="carts.length > 1" class="hover:text-red-300 dark:hover:text-red-400 ml-1">✕</button>
            </div>
        </template>
        {{-- Tombol Tambah Antrean Baru --}}
        <button @click="newCart()" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:text-indigo-500 font-bold shadow-sm">+</button>
    </div>

    {{-- Daftar Item di Keranjang (Disesuaikan untuk Mode Kompak & Dark Mode) --}}
    <div class="flex-1 overflow-y-auto flex flex-col gap-3 pb-4 border-b border-slate-100 dark:border-slate-700 mb-6 hide-scrollbar">
        <template x-for="item in items" :key="item.id">
            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-2xl border border-transparent dark:border-slate-700 flex flex-col gap-2">
                <div class="flex justify-between items-start">
                    <h4 class="font-bold text-[13px] text-slate-900 dark:text-white leading-tight line-clamp-2" x-text="item.name"></h4>
                    <button class="text-slate-300 dark:text-slate-500 hover:text-red-500 shrink-0 ml-2" @click="removeItem(item.id)">✕</button>
                </div>
                <p class="text-[11px] font-semibold text-slate-400">
                    <span x-text="formatRupiah(item.price)"></span> / <span x-text="item.unit"></span>
                </p>
                <div class="flex justify-between items-center mt-1">
                    <div class="flex items-center bg-white dark:bg-slate-800 rounded-lg p-0.5 border border-slate-200 dark:border-slate-600">
                        <button class="w-6 h-6 rounded flex items-center justify-center text-slate-500 dark:text-slate-300 font-bold hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-700" @click="decrement(item)">-</button>
                        
                        {{-- Input diganti menjadi READONLY dan memicu Numpad saat diklik --}}
                        <input type="text" readonly :value="formatQty(item.qty)" @click="openNumpad(item)" 
                               class="w-10 bg-transparent text-center text-sm font-bold text-slate-800 dark:text-white focus:outline-none cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 rounded transition-colors">
                        
                        <button class="w-6 h-6 rounded flex items-center justify-center text-slate-500 dark:text-slate-300 font-bold hover:text-indigo-500 hover:bg-slate-100 dark:hover:bg-slate-700" @click="increment(item)">+</button>
                    </div>
                    <p class="font-black text-sm text-indigo-500 dark:text-indigo-400" x-text="formatRupiah(item.price * item.qty)"></p>
                </div>
            </div>
        </template>
        
        {{-- Keadaan Kosong --}}
        <template x-if="items.length === 0">
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 dark:text-slate-600 opacity-80 h-full">
                <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p class="font-bold text-sm">Keranjang kosong</p>
            </div>
        </template>
    </div>

    {{-- Ringkasan Bayar --}}
    <div>
        <div class="flex justify-between items-center mb-1 text-sm font-bold">
            <span class="text-slate-400">Sub Total</span>
            <span class="text-slate-900 dark:text-white" x-text="formatRupiah(subtotal)"></span>
        </div>
        <div class="flex justify-between items-center mb-5 text-sm font-bold">
            <span class="text-slate-400">PPN (11%)</span>
            <span class="text-slate-900 dark:text-white" x-text="formatRupiah(tax)"></span>
        </div>
        {{-- Tombol Checkout memunculkan tooltip shortcut F2 --}}
        <button @click="openCheckout()" :disabled="items.length === 0" class="w-full bg-slate-900 dark:bg-indigo-500 hover:bg-indigo-600 dark:hover:bg-indigo-600 text-white font-bold py-4 rounded-[20px] shadow-xl transition-all flex justify-between items-center px-6 disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="text-sm">Checkout [F2]</span>
            <span class="text-xl" x-text="formatRupiah(total)"></span>
        </button>
    </div>
</aside>