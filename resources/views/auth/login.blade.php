<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Dinda Perabot POS</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Quicksand', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ darkMode: localStorage.getItem('theme') === 'dark', showPassword: false }"
      :class="{ 'dark': darkMode }"
      class="h-screen w-screen flex items-center justify-center antialiased bg-[#E8F0FE] dark:bg-slate-900 transition-colors duration-300 p-4">

    <div class="w-full max-w-[400px] bg-white dark:bg-slate-800 rounded-[32px] shadow-[0_20px_60px_rgba(0,0,0,0.08)] p-8 sm:p-10 border border-transparent dark:border-slate-700 relative">

        {{-- Toggle dark mode kecil di pojok --}}
        <button type="button" @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
                class="absolute top-5 right-5 w-9 h-9 rounded-full flex items-center justify-center text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-slate-700 transition-colors">
            <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
        </button>

        {{-- Logo / Branding --}}
        <div class="flex flex-col items-center mb-8">
            <div class="w-16 h-16 rounded-3xl bg-indigo-500 shadow-lg shadow-indigo-500/30 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Dinda Perabot POS</h1>
            <p class="text-sm font-semibold text-slate-400 dark:text-slate-500 mt-1">Masuk untuk mulai berjualan</p>
        </div>

        {{-- Pesan error global (kredensial salah, dll) --}}
        @if ($errors->any())
            <div class="mb-5 px-4 py-3 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/50 text-red-600 dark:text-red-400 text-sm font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Pesan status, mis. setelah logout --}}
        @if (session('status'))
            <div class="mb-5 px-4 py-3 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="email" class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="nama@tokoperabot.com"
                       class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm text-slate-800 dark:text-white transition-all">
            </div>

            <div>
                <label for="password" class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Kata Sandi</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                           placeholder="••••••••"
                           class="w-full px-4 py-3 pr-12 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-sm text-slate-800 dark:text-white transition-all">
                    <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-500">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>
                    </button>
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer select-none mt-1">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded accent-indigo-500">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Ingat saya di perangkat ini</span>
            </label>

            <button type="submit"
                    class="w-full mt-3 bg-slate-900 hover:bg-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-slate-900/20 dark:shadow-indigo-900/30 transition-all">
                Masuk
            </button>
        </form>
    </div>
</body>
</html>
