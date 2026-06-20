<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS & ERP Modern') - BuildPro</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Konfigurasi Tailwind agar Dark Mode menggunakan class (dikendalikan Alpine) --}}
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Quicksand', sans-serif; }
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        [x-cloak] { display: none !important; }

        @media print {
            body * { visibility: hidden; }
            #receipt-print, #receipt-print * { visibility: visible; }
            #receipt-print { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
    @stack('styles')
</head>

{{-- Menyematkan state globalApp dan mendeteksi Dark Mode di <body> --}}
<body x-data="globalApp()" :class="{ 'dark': darkMode }" class="h-screen w-screen p-4 lg:p-6 flex items-center justify-center antialiased select-none overflow-hidden bg-[#E8F0FE] dark:bg-slate-900 transition-colors duration-300 text-slate-800 dark:text-slate-100">

    <div x-data="{ sidebarOpen: false }" class="bg-white dark:bg-slate-800 w-full h-full max-w-[1600px] rounded-[32px] shadow-[0_20px_60px_rgba(0,0,0,0.05)] flex p-5 gap-6 relative overflow-hidden transition-all duration-300 border border-transparent dark:border-slate-700">

        <x-sidebar :active="$activeMenu ?? 'pos'" />

        @yield('content')

    </div>

    {{-- Wadah HTML untuk Toast Notification (Notifikasi Melayang) --}}
    <div class="fixed top-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" x-transition.duration.300ms
                 class="px-5 py-3 rounded-2xl shadow-xl text-sm font-bold text-white pointer-events-auto flex items-center gap-3 backdrop-blur-md"
                 :class="toast.type === 'error' ? 'bg-red-500/90' : (toast.type === 'info' ? 'bg-indigo-500/90' : 'bg-emerald-500/90')">
                <span x-text="toast.msg"></span>
            </div>
        </template>
    </div>

    {{-- Script Global untuk mengelola Tema dan Toast --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('globalApp', () => ({
                darkMode: localStorage.getItem('theme') === 'dark',
                toasts: [],
                init() {
                    // Pantau perubahan tema dan simpan ke LocalStorage browser
                    this.$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'));
                    // Dengarkan event 'toast' dari komponen mana saja
                    window.addEventListener('toast', (e) => this.addToast(e.detail.msg, e.detail.type));
                },
                addToast(msg, type = 'success') {
                    const id = Date.now();
                    this.toasts.push({ id, msg, type });
                    // Hapus otomatis setelah 3 detik
                    setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3000);
                }
            }));
        });
    </script>
    
    @stack('scripts')
</body>
</html>