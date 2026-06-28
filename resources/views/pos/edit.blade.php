@extends('layouts.app')
@section('title', 'Edit Nota — ' . $transaction->invoice_number)
<?php $activeMenu = 'pos'; ?>

@section('content')
<main class="flex-1 h-full flex flex-col min-w-0" x-data="editNota()" @keydown.window="handleKeydown($event)">

    <header class="flex items-center gap-3 mb-5">
        <a href="{{ route('report.show', $transaction) }}"
           class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 hover:text-indigo-500 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Edit Nota</h1>
            <p class="text-xs font-bold text-slate-400">{{ $transaction->invoice_number }} · {{ $transaction->created_at->format('d M Y, H:i') }}</p>
        </div>
        <div class="ml-auto">
            <span class="inline-block px-3 py-1 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold">
                ⚠️ Perubahan akan menyesuaikan stok secara otomatis
            </span>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto hide-scrollbar">
        <div class="grid grid-cols-3 gap-5 max-w-6xl">

            {{-- Kolom Kiri: Katalog Produk --}}
            <div class="col-span-2 flex flex-col gap-4">
                {{-- Pencarian Produk --}}
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-4 shadow-sm">
                    <input type="text" x-model="search" placeholder="Cari produk untuk ditambahkan..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm dark:text-white focus:outline-none focus:border-indigo-500">
                </div>

                {{-- Daftar Produk --}}
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-4 shadow-sm">
                    <h3 class="font-bold text-slate-800 dark:text-white mb-3 text-sm">Tambah / Ganti Produk</h3>
                    <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto">
                        @foreach ($products as $product)
                        <button @click="addItem({{ json_encode([
                            'id'       => $product->id,
                            'name'     => $product->name,
                            'price'    => (float) $product->price,
                            'unit'     => $product->unit,
                            'unitType' => $product->unit_type,
                            'stock'    => (float) $product->stock,
                        ]) }})"
                                x-show="search === '' || '{{ strtolower($product->name) }}'.includes(search.toLowerCase())"
                                class="text-left p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all">
                            <p class="font-bold text-xs text-slate-800 dark:text-white truncate">{{ $product->name }}</p>
                            <p class="text-[10px] text-slate-400 font-semibold mt-0.5">
                                Rp {{ number_format($product->price, 0, ',', '.') }} / {{ $product->unit }}
                                <span class="{{ $product->isLowStock() ? 'text-red-500' : 'text-emerald-500' }}">
                                    · stok {{ rtrim(rtrim(number_format($product->stock, 2, '.', ''), '0'), '.') }}
                                </span>
                            </p>
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Item yang ada di nota saat ini --}}
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-4 shadow-sm">
                    <h3 class="font-bold text-slate-800 dark:text-white mb-3 text-sm">Item dalam Nota</h3>
                    <template x-for="item in items" :key="item.id">
                        <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 dark:border-slate-700/50 last:border-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 dark:text-white truncate" x-text="item.name"></p>
                                <p class="text-xs text-slate-400 font-semibold" x-text="'Rp ' + formatRupiah(item.price) + ' / ' + item.unit"></p>
                            </div>
                            <div class="flex items-center bg-slate-50 dark:bg-slate-900 rounded-lg p-0.5 border border-slate-200 dark:border-slate-600">
                                <button @click="decrement(item)" class="w-7 h-7 rounded flex items-center justify-center text-slate-500 font-bold hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-700">-</button>
                                <input type="text" :value="formatQty(item.qty)"
                                       @change="updateQty(item, $event.target.value)"
                                       class="w-12 bg-transparent text-center text-sm font-bold text-slate-800 dark:text-white focus:outline-none">
                                <button @click="increment(item)" class="w-7 h-7 rounded flex items-center justify-center text-slate-500 font-bold hover:text-indigo-500 hover:bg-slate-100 dark:hover:bg-slate-700">+</button>
                            </div>
                            <p class="text-sm font-black text-indigo-500 w-24 text-right" x-text="'Rp ' + formatRupiah(item.price * item.qty)"></p>
                            <button @click="removeItem(item.id)" class="text-slate-300 hover:text-red-500 transition-colors">✕</button>
                        </div>
                    </template>
                    <template x-if="items.length === 0">
                        <p class="text-center text-sm text-slate-400 font-semibold py-4">Belum ada item. Tambahkan dari katalog di atas.</p>
                    </template>
                </div>
            </div>

            {{-- Kolom Kanan: Form & Ringkasan --}}
            <div class="flex flex-col gap-4">

                {{-- Pelanggan --}}
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-4 shadow-sm" x-data="{ open: false, suggestions: [] }">
                    <h3 class="font-bold text-slate-800 dark:text-white mb-3 text-sm">Pelanggan</h3>
                    <input type="text" x-model="customerName"
                           @input.debounce.300ms="customerId = null; searchCustomer($event.target.value, suggestions, () => open = suggestions.length > 0)"
                           @focus="if(suggestions.length) open = true"
                           @blur.debounce.200ms="open = false"
                           placeholder="Nama pelanggan (opsional)"
                           class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold dark:text-white focus:outline-none focus:border-indigo-500">
                    <div x-show="open" x-cloak class="mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg overflow-hidden z-20 relative">
                        <template x-for="s in suggestions" :key="s.id">
                            <div @click="customerName = s.name; customerId = s.id; open = false"
                                 class="px-3 py-2 hover:bg-indigo-50 dark:hover:bg-slate-700 cursor-pointer">
                                <p class="text-xs font-bold text-slate-800 dark:text-white" x-text="s.name"></p>
                                <p class="text-[10px] text-slate-400" x-text="s.phone ?? ''"></p>
                            </div>
                        </template>
                    </div>
                    <p x-show="customerId" class="text-[10px] text-indigo-500 font-bold mt-1">✓ Terikat ke akun pelanggan terdaftar</p>
                </div>

                {{-- Pembayaran --}}
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-4 shadow-sm">
                    <h3 class="font-bold text-slate-800 dark:text-white mb-3 text-sm">Pembayaran</h3>

                    <div class="mb-3">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Metode</label>
                        <select x-model="paymentMethod" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold dark:text-white focus:outline-none focus:border-indigo-500">
                            <option value="cash">Cash</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer</option>
                            <option value="debit">Debit</option>
                        </select>
                    </div>

                    {{-- FIX: Input paid amount dengan format titik otomatis --}}
                    <div class="mb-3">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Jumlah Dibayar (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                            <input type="text" x-model="paidAmountDisplay"
                                   @input="onPaidInput($event)"
                                   @focus="$event.target.select()"
                                   placeholder="0"
                                   class="w-full pl-9 pr-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5">Total: Rp <span x-text="formatRupiah(total)"></span></p>
                    </div>

                    <div class="mb-3">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Catatan</label>
                        <input type="text" x-model="note" placeholder="Catatan opsional"
                               class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold dark:text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                {{-- Ringkasan Total --}}
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-[24px] p-4 shadow-sm">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-400 font-semibold">Subtotal</span>
                        <span class="font-bold text-slate-800 dark:text-white" x-text="'Rp ' + formatRupiah(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-400 font-semibold">PPN 11%</span>
                        <span class="font-bold text-slate-800 dark:text-white" x-text="'Rp ' + formatRupiah(tax)"></span>
                    </div>
                    <div class="flex justify-between text-base pt-2 border-t border-slate-100 dark:border-slate-700">
                        <span class="font-bold text-slate-700 dark:text-white">Total</span>
                        <span class="font-black text-indigo-500" x-text="'Rp ' + formatRupiah(total)"></span>
                    </div>
                    <div x-show="paidAmount >= total" class="flex justify-between text-sm mt-1">
                        <span class="text-slate-400 font-semibold">Kembalian</span>
                        <span class="font-bold text-emerald-500" x-text="'Rp ' + formatRupiah(paidAmount - total)"></span>
                    </div>
                    <div x-show="paidAmount < total && paidAmount > 0" class="flex justify-between text-sm mt-1">
                        <span class="text-amber-500 font-semibold">Kurang Bayar</span>
                        <span class="font-bold text-amber-500" x-text="'Rp ' + formatRupiah(total - paidAmount)"></span>
                    </div>
                </div>

                {{-- Tombol Simpan --}}
                <button @click="saveChanges()"
                        :disabled="processing || items.length === 0"
                        class="w-full py-4 rounded-[20px] font-bold text-white bg-slate-900 dark:bg-indigo-500 hover:bg-indigo-600 transition-all shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg x-show="processing" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v2a8 8 0 100 16v-2"></path>
                    </svg>
                    <span x-text="processing ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                </button>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const UPDATE_URL = '{{ route("pos.update", $transaction) }}';
const REDIRECT_URL = '{{ route("report.show", $transaction) }}';

// Data awal dari transaksi yang sedang diedit
const INITIAL_ITEMS = @json($transaction->items->map(fn($i) => [
    'id'        => $i->product_id,
    'name'      => $i->product_name,
    'price'     => (float) $i->price,
    'unit'      => $i->unit,
    'unit_type' => 'integer', // akan diupdate dari produk jika ada
    'stock'     => 999, // placeholder; validasi stok ada di backend
    'qty'       => (float) $i->quantity,
]));

document.addEventListener('alpine:init', () => {
    Alpine.data('editNota', () => ({
        items:          JSON.parse(JSON.stringify(INITIAL_ITEMS)),
        search:         '',
        customerName:   '{{ $transaction->customer_name ?? "" }}',
        customerId:     {{ $transaction->customer_id ?? 'null' }},
        paymentMethod:  '{{ $transaction->payment_method }}',
        paidAmount:     {{ (float) $transaction->paid_amount }},
        paidAmountDisplay: '{{ number_format((float) $transaction->paid_amount, 0, ",", ".") }}',
        note:           '{{ $transaction->note ?? "" }}',
        processing:     false,
        taxRate:        0.11,

        get subtotal() { return this.items.reduce((s, i) => s + i.price * i.qty, 0); },
        get tax()      { return this.subtotal * this.taxRate; },
        get total()    { return this.subtotal + this.tax; },

        formatRupiah(n) { return new Intl.NumberFormat('id-ID').format(Math.round(Number(n) || 0)); },
        formatQty(q)    { return parseFloat(Number(q).toFixed(3)).toString(); },
        clean(n)        { return Math.round((Number(n) + Number.EPSILON) * 1000) / 1000; },

        // FIX: Format input paid amount dengan titik pemisah ribuan otomatis
        onPaidInput(e) {
            // Ambil angka saja
            let raw = e.target.value.replace(/\D/g, '');
            if (raw === '') { this.paidAmount = 0; this.paidAmountDisplay = ''; return; }
            let num = parseInt(raw, 10);
            this.paidAmount = num;
            // Format dengan titik pemisah ribuan
            this.paidAmountDisplay = new Intl.NumberFormat('id-ID').format(num);
            // Kembalikan cursor ke akhir
            this.$nextTick(() => {
                e.target.setSelectionRange(e.target.value.length, e.target.value.length);
            });
        },

        addItem(data) {
            const id = parseInt(data.id);
            const found = this.items.find(i => i.id === id);
            const step = data.unitType === 'decimal' ? 0.5 : 1;
            if (found) {
                found.qty = this.clean(found.qty + step);
            } else {
                this.items.push({
                    id,
                    name: data.name,
                    price: parseFloat(data.price),
                    unit: data.unit,
                    unit_type: data.unitType,
                    stock: parseFloat(data.stock),
                    qty: data.unitType === 'decimal' ? 0.5 : 1,
                });
            }
        },
        increment(item) {
            const s = item.unit_type === 'decimal' ? 0.5 : 1;
            item.qty = this.clean(item.qty + s);
        },
        decrement(item) {
            const s = item.unit_type === 'decimal' ? 0.5 : 1;
            const n = this.clean(item.qty - s);
            if (n <= 0) { this.removeItem(item.id); return; }
            item.qty = n;
        },
        updateQty(item, v) {
            let q = parseFloat(v);
            if (isNaN(q) || q <= 0) { this.removeItem(item.id); return; }
            item.qty = this.clean(q);
        },
        removeItem(id) { this.items = this.items.filter(i => i.id !== id); },

        async searchCustomer(q, suggestions, cb) {
            if (!q || q.length < 2) { suggestions.length = 0; return; }
            try {
                const res = await fetch(`{{ route('customer.search') }}?q=${encodeURIComponent(q)}`, { headers: { Accept: 'application/json' } });
                const data = await res.json();
                suggestions.splice(0, suggestions.length, ...data);
                cb();
            } catch {}
        },

        handleKeydown(e) {
            if (e.key === 'Escape') window.history.back();
        },

        async saveChanges() {
            if (this.processing) return;
            if (!this.items.length) {
                alert('Nota tidak boleh kosong!');
                return;
            }
            this.processing = true;
            try {
                const res = await fetch(UPDATE_URL, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({
                        items:          this.items.map(i => ({ id: i.id, qty: i.qty })),
                        customer_name:  this.customerName || null,
                        customer_id:    this.customerId || null,
                        payment_method: this.paymentMethod,
                        paid_amount:    this.paidAmount,
                        note:           this.note || null,
                    }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Gagal menyimpan.');
                window.location.href = REDIRECT_URL + '?edited=1';
            } catch (err) {
                alert('Error: ' + err.message);
            } finally {
                this.processing = false;
            }
        },
    }));
});
</script>
@endpush