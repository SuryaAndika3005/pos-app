{{--
    Komponen Sidebar (navigasi kiri).
    Dipakai sebagai: <x-sidebar :active="'pos'" />
    Prop $active menentukan menu mana yang di-highlight: 'pos' | 'inventory' | 'report'
--}}
@props(['active' => 'pos'])

<aside class="w-[220px] h-full flex flex-col justify-between py-4 pl-2">
    <div>
        {{-- Profil Kasir (idealnya diisi dari auth: Auth::user()) --}}
        <div class="flex flex-col items-center mb-10">
            <div class="w-20 h-20 rounded-3xl bg-indigo-100 mb-3 border-4 border-white shadow-lg overflow-hidden flex items-center justify-center">
                <img src="{{ $cashierAvatar ?? 'https://api.dicebear.com/7.x/notionists/svg?seed=Jane' }}"
                     alt="Kasir" class="w-full h-full object-cover">
            </div>
            <h3 class="font-bold text-lg text-slate-800">{{ $cashierName ?? 'Surya Andika' }}</h3>
            <p class="text-sm font-semibold text-slate-400">{{ $cashierRole ?? 'Kasir Utama' }}</p>
        </div>

        {{-- Menu Navigasi (Pill Shapes) --}}
        <nav class="flex flex-col gap-3">

            {{-- Kasir POS --}}
            <a href="{{ route('pos.index') }}"
               @class([
                   'flex items-center gap-4 px-6 py-4 rounded-2xl transition-all',
                   'bg-indigo-500 text-white shadow-[0_8px_20px_rgba(99,102,241,0.3)] hover:scale-105' => $active === 'pos',
                   'text-slate-400 hover:text-indigo-500 hover:bg-indigo-50' => $active !== 'pos',
               ])>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="font-bold">Kasir POS</span>
            </a>

            {{-- Gudang AI --}}
            <a href="{{-- route('inventory.index') --}}#"
               @class([
                   'flex items-center gap-4 px-6 py-4 rounded-2xl transition-all',
                   'bg-indigo-500 text-white shadow-[0_8px_20px_rgba(99,102,241,0.3)] hover:scale-105' => $active === 'inventory',
                   'text-slate-400 hover:text-indigo-500 hover:bg-indigo-50' => $active !== 'inventory',
               ])>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span class="font-bold">Gudang AI</span>
            </a>

            {{-- Laporan --}}
            <a href="{{-- route('report.index') --}}#"
               @class([
                   'flex items-center gap-4 px-6 py-4 rounded-2xl transition-all',
                   'bg-indigo-500 text-white shadow-[0_8px_20px_rgba(99,102,241,0.3)] hover:scale-105' => $active === 'report',
                   'text-slate-400 hover:text-indigo-500 hover:bg-indigo-50' => $active !== 'report',
               ])>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="font-bold">Laporan</span>
            </a>
        </nav>
    </div>

    {{-- Banner Ilustrasi Bawah --}}
    <div class="bg-indigo-50 rounded-3xl p-5 text-center relative overflow-hidden mt-8">
        <div class="w-12 h-12 bg-indigo-100 rounded-full absolute -top-4 -right-4"></div>
        <h4 class="font-bold text-indigo-900 mb-1 relative z-10">AI Prediksi Aktif!</h4>
        <p class="text-xs text-indigo-600 font-medium relative z-10">Stok dipantau otomatis.</p>
    </div>
</aside>
