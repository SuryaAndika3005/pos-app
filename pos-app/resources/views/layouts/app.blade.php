<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS & ERP Modern') - BuildPro</title>

    {{-- CSRF token untuk request AJAX/fetch --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Catatan produksi: untuk rilis, pasang Tailwind & Alpine via Vite (npm),
         bukan CDN, agar ukuran asset jauh lebih kecil. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js untuk state management keranjang (reaktif, tanpa reload) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Font Quicksand untuk nuansa Soft UI yang bulat & lembut --}}
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Quicksand', sans-serif; background-color: #E8F0FE; }
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        /* Sembunyikan elemen Alpine sebelum siap, hindari flicker */
        [x-cloak] { display: none !important; }

        /* Area struk hanya tampil saat mencetak */
        @media print {
            body * { visibility: hidden; }
            #receipt-print, #receipt-print * { visibility: visible; }
            #receipt-print { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>

    @stack('styles')
</head>
<body class="h-screen w-screen p-4 lg:p-8 flex items-center justify-center antialiased select-none overflow-hidden text-slate-800">

    {{-- CONTAINER APLIKASI UTAMA (Membulat seperti iPad App) --}}
    <div class="bg-white w-full h-full max-w-[1600px] rounded-[40px] shadow-[0_20px_60px_rgba(0,0,0,0.05)] flex p-6 gap-8 relative overflow-hidden">

        {{-- Sidebar global --}}
        <x-sidebar :active="$activeMenu ?? 'pos'" />

        {{-- Konten tiap halaman --}}
        @yield('content')

    </div>

    @stack('scripts')
</body>
</html>
