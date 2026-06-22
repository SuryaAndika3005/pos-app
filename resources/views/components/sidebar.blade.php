@props(['active' => 'pos'])

<aside :class="sidebarOpen ? 'w-[220px]' : 'w-[80px]'" class="h-full flex flex-col justify-between py-2 transition-all duration-300 relative shrink-0 border-r border-slate-100 dark:border-slate-700 pr-4">
    
    <button @click="sidebarOpen = !sidebarOpen" class="absolute -right-3 top-12 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 shadow-sm w-7 h-7 rounded-full flex items-center justify-center text-slate-500 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 z-10">
        <svg x-show="!sidebarOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <svg x-show="sidebarOpen" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
    </button>

    <div>
        {{-- Profil --}}
        <div class="flex flex-col items-center mb-6 mt-2">
            <div class="bg-indigo-100 dark:bg-indigo-900 overflow-hidden flex items-center justify-center transition-all duration-300"
                 :class="sidebarOpen ? 'w-16 h-16 rounded-2xl mb-3' : 'w-12 h-12 rounded-xl mb-0'">
                <img src="https://api.dicebear.com/7.x/notionists/svg?seed={{ urlencode(Auth::user()->name ?? 'Kasir') }}" alt="Kasir" class="w-full h-full object-cover">
            </div>
            <div x-show="sidebarOpen" x-cloak class="text-center overflow-hidden">
                <h3 class="font-bold text-base text-slate-800 dark:text-white whitespace-nowrap">{{ Auth::user()->name ?? 'Tamu' }}</h3>
                <p class="text-xs font-semibold text-slate-400">{{ Auth::user()?->isAdmin() ? 'Admin' : 'Kasir' }}</p>
            </div>
        </div>

        <nav class="flex flex-col gap-1.5 px-1">
            {{-- POS --}}
            <a href="{{ route('pos.index') }}"
               class="flex items-center gap-3 p-3 rounded-xl transition-all {{ $active === 'pos' ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-slate-700' }}">
                <div class="w-6 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                </div>
                <span x-show="sidebarOpen" x-cloak class="font-bold whitespace-nowrap text-sm">Kasir POS</span>
            </a>

            @if (Auth::user()?->isAdmin())
            {{-- Gudang --}}
            <a href="{{ route('inventory.index') }}"
               class="flex items-center gap-3 p-3 rounded-xl transition-all {{ $active === 'inventory' ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-slate-700' }}">
                <div class="w-6 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <span x-show="sidebarOpen" x-cloak class="font-bold whitespace-nowrap text-sm">Gudang</span>
            </a>

            {{-- Laporan --}}
            <a href="{{ route('report.index') }}"
               class="flex items-center gap-3 p-3 rounded-xl transition-all {{ $active === 'report' ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-slate-700' }}">
                <div class="w-6 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span x-show="sidebarOpen" x-cloak class="font-bold whitespace-nowrap text-sm">Laporan</span>
            </a>

            {{-- Pelanggan --}}
            <a href="{{ route('customer.index') }}"
               class="flex items-center gap-3 p-3 rounded-xl transition-all {{ $active === 'customer' ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-slate-700' }}">
                <div class="w-6 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <span x-show="sidebarOpen" x-cloak class="font-bold whitespace-nowrap text-sm">Pelanggan</span>
            </a>

            {{-- Utang --}}
            <a href="{{ route('debt.index') }}"
               class="flex items-center gap-3 p-3 rounded-xl transition-all {{ $active === 'debt' ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-slate-700' }}">
                <div class="w-6 flex items-center justify-center shrink-0 relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span x-show="sidebarOpen" x-cloak class="font-bold whitespace-nowrap text-sm">Utang</span>
            </a>
            @endif
        </nav>
    </div>

    <div>
        <form method="POST" action="{{ route('logout') }}" class="mb-2">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl transition-all text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                <div class="w-6 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </div>
                <span x-show="sidebarOpen" x-cloak class="font-bold whitespace-nowrap text-sm">Keluar</span>
            </button>
        </form>

        <button @click="darkMode = !darkMode" class="w-full flex items-center gap-3 p-3 rounded-xl transition-all text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-slate-700 mb-3">
            <div class="w-6 flex items-center justify-center shrink-0">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </div>
            <span x-show="sidebarOpen" x-cloak class="font-bold whitespace-nowrap text-sm" x-text="darkMode ? 'Mode Terang' : 'Mode Gelap'"></span>
        </button>
    </div>
</aside>
