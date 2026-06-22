{{-- Panel Keranjang — scope x-data="posCart" dari pos/index.blade.php --}}
<aside class="w-[340px] h-full flex flex-col shrink-0 pl-2">

    {{-- Tab Antrean --}}
    <div class="flex items-center gap-2 mb-3 overflow-x-auto hide-scrollbar pb-1">
        <template x-for="(cart, index) in carts" :key="cart.id">
            <div class="relative flex items-center shrink-0">
                <div x-show="editingCartIndex !== index"
                     @click="activeCartIndex = index"
                     class="px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap cursor-pointer transition-all flex items-center gap-2 border"
                     :class="activeCartIndex === index ? 'bg-indigo-500 text-white border-indigo-500 shadow-md' : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'">
                    <span x-text="cart.name" @dblclick.stop="startRename(index)" title="Klik dua kali untuk ganti nama" class="select-none"></span>
                    <button @click.stop="closeCart(index)" x-show="carts.length > 1" class="hover:text-red-300 ml-1">✕</button>
                </div>
                <div x-show="editingCartIndex === index" x-cloak>
                    <input type="text" x-model="editingName" x-ref="renameInput"
                           @keydown.enter="commitRename()" @keydown.escape="editingCartIndex = null" @blur="commitRename()"
                           class="w-28 px-2 py-1.5 rounded-xl text-xs font-bold border-2 border-indigo-500 bg-white dark:bg-slate-800 dark:text-white focus:outline-none shadow-md">
                </div>
            </div>
        </template>
        <button @click="newCart()" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:text-indigo-500 font-bold shadow-sm shrink-0">+</button>
    </div>

    {{-- Input Pelanggan dengan Autocomplete --}}
    <div class="mb-3 relative" x-data="{ open: false, suggestions: [] }">
        <div class="flex items-center gap-1.5 mb-1 px-1">
            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Pelanggan</span>
            <span x-show="activeCart.customerId" class="ml-auto text-[9px] font-bold text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded">Terdaftar</span>
        </div>
        <input type="text"
               x-model="activeCart.customerName"
               @input.debounce.300ms="searchCustomer($event.target.value, suggestions, () => open = suggestions.length > 0)"
               @focus="if(suggestions.length) open = true"
               @blur.debounce.200ms="open = false"
               placeholder="Ketik nama / telp pelanggan..."
               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-300 dark:placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition-colors">

        {{-- Badge utang pelanggan terpilih --}}
        <div x-show="activeCart.customerDebt > 0" x-cloak
             class="mt-1 px-2 py-1 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/50 text-xs font-bold text-red-600 dark:text-red-400 flex items-center gap-1.5">
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span>Sisa utang: Rp <span x-text="formatRupiah(activeCart.customerDebt)"></span></span>
        </div>

        {{-- Dropdown Autocomplete --}}
        <div x-show="open" x-cloak
             class="absolute z-30 left-0 right-0 top-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl overflow-hidden">
            <template x-for="s in suggestions" :key="s.id">
                <div @click="selectCustomer(s); open = false"
                     class="px-3 py-2.5 hover:bg-indigo-50 dark:hover:bg-slate-700 cursor-pointer flex justify-between items-center">
                    <div>
                        <p class="text-xs font-bold text-slate-800 dark:text-white" x-text="s.name"></p>
                        <p class="text-[10px] text-slate-400 font-semibold" x-text="s.phone ?? 'Tanpa no. telp'"></p>
                    </div>
                    <span x-show="s.total_debt > 0"
                          class="text-[10px] font-bold text-red-500 bg-red-50 dark:bg-red-900/20 px-1.5 py-0.5 rounded"
                          x-text="'Utang: ' + formatRupiah(s.total_debt)"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Daftar Item --}}
    <div class="flex-1 overflow-y-auto flex flex-col gap-3 pb-4 border-b border-slate-100 dark:border-slate-700 mb-4 hide-scrollbar">
        <template x-for="item in items" :key="item.id">
            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-2xl border border-transparent dark:border-slate-700 flex flex-col gap-2">
                <div class="flex justify-between items-start">
                    <h4 class="font-bold text-[13px] text-slate-900 dark:text-white leading-tight line-clamp-2" x-text="item.name"></h4>
                    <button class="text-slate-300 dark:text-slate-500 hover:text-red-500 shrink-0 ml-2" @click="removeItem(item.id)">✕</button>
                </div>
                <p class="text-[11px] font-semibold text-slate-400"><span x-text="formatRupiah(item.price)"></span> / <span x-text="item.unit"></span></p>
                <div class="flex justify-between items-center mt-1">
                    <div class="flex items-center bg-white dark:bg-slate-800 rounded-lg p-0.5 border border-slate-200 dark:border-slate-600">
                        <button class="w-6 h-6 rounded flex items-center justify-center text-slate-500 font-bold hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-700" @click="decrement(item)">-</button>
                        <input type="text" readonly :value="formatQty(item.qty)" @click="openNumpad(item)"
                               class="w-10 bg-transparent text-center text-sm font-bold text-slate-800 dark:text-white focus:outline-none cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 rounded transition-colors">
                        <button class="w-6 h-6 rounded flex items-center justify-center text-slate-500 font-bold hover:text-indigo-500 hover:bg-slate-100 dark:hover:bg-slate-700" @click="increment(item)">+</button>
                    </div>
                    <p class="font-black text-sm text-indigo-500" x-text="formatRupiah(item.price * item.qty)"></p>
                </div>
            </div>
        </template>
        <template x-if="items.length === 0">
            <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-600 py-8">
                <svg class="w-14 h-14 mb-2 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p class="font-bold text-sm">Keranjang kosong</p>
            </div>
        </template>
    </div>

    {{-- Ringkasan --}}
    <div>
        <div class="flex justify-between items-center mb-1 text-sm font-bold">
            <span class="text-slate-400">Sub Total</span>
            <span class="text-slate-900 dark:text-white" x-text="formatRupiah(subtotal)"></span>
        </div>
        <div class="flex justify-between items-center mb-5 text-sm font-bold">
            <span class="text-slate-400">PPN (11%)</span>
            <span class="text-slate-900 dark:text-white" x-text="formatRupiah(tax)"></span>
        </div>
        <button @click="openCheckout()" :disabled="items.length === 0"
                class="w-full bg-slate-900 dark:bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-4 rounded-[20px] shadow-xl transition-all flex justify-between items-center px-6 disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="text-sm">Checkout [F2]</span>
            <span class="text-xl" x-text="formatRupiah(total)"></span>
        </button>
    </div>
</aside>
