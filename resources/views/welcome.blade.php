<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SIAKAD Gateway - UNMARIS</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicons/logo-unmaris.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            /* Mencegah scroll, memaksa single screen */
        }

        /* Animasi Logo Pop-in saat pertama kali load */
        @keyframes logoPop {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-logo-pop {
            animation: logoPop 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Animasi Staggered untuk Card */
        @keyframes fadeUpCard {
            0% {
                opacity: 0;
                transform: translateY(15px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-1 {
            animation: fadeUpCard 0.5s ease-out 0.2s forwards;
            opacity: 0;
        }

        .card-2 {
            animation: fadeUpCard 0.5s ease-out 0.3s forwards;
            opacity: 0;
        }

        .card-3 {
            animation: fadeUpCard 0.5s ease-out 0.4s forwards;
            opacity: 0;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 antialiased h-[100dvh] w-full flex flex-col relative selection:bg-yellow-500 selection:text-indigo-950">

    <!-- Efek Latar Belakang Enterprise (Subtle) -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute -top-[20%] -right-[10%] w-[50%] h-[50%] bg-indigo-900/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-[20%] -left-[10%] w-[50%] h-[50%] bg-yellow-500/5 rounded-full blur-3xl"></div>
    </div>

    <!-- HEADER (Status & Identitas Minimalis) -->
    <header class="w-full px-6 py-4 flex justify-between items-center z-10 shrink-0">
        <div class="flex items-center gap-2">
            <span class="font-bold text-indigo-950 text-sm tracking-wide">SIAKAD UNMARIS</span>
        </div>

        <!-- System Status -->
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <span class="text-xs font-semibold text-slate-600">Sistem Online</span>
        </div>
    </header>

    <!-- MAIN CONTENT (Gateway Selection) -->
    <main class="flex-1 flex flex-col items-center justify-center px-4 w-full max-w-4xl mx-auto z-10">

        <!-- Branding Area -->
        <div class="text-center mb-8 sm:mb-10 animate-logo-pop">
            <div class="inline-flex justify-center items-center bg-white p-4 rounded-3xl shadow-sm border border-slate-100 mb-6">
                <img src="{{ asset('images/logo-unmaris.png') }}" alt="Logo UNMARIS" class="h-16 sm:h-20 w-auto object-contain">
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-indigo-950 tracking-tight mb-2">
                Anda masuk sebagai siapa?
            </h1>
            <p class="text-sm sm:text-base text-slate-500 font-medium">
                Pilih peran Anda untuk masuk ke portal sistem
            </p>
        </div>

        <!-- Role Selection Cards -->
        <!-- Mobile: Stacked list | Desktop: Horizontal Cards -->
        <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">

            <!-- 1. Mahasiswa -->
            <a href="/mahasiswa" class="card-1 group relative bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-yellow-400 transition-all duration-200 flex flex-row md:flex-col items-center md:text-center gap-4 sm:gap-5 focus:outline-none focus:ring-4 focus:ring-yellow-400/20 active:scale-[0.98]">
                <div class="h-12 w-12 sm:h-16 sm:w-16 shrink-0 bg-yellow-50 rounded-xl sm:rounded-2xl flex items-center justify-center text-2xl sm:text-3xl group-hover:scale-110 transition-transform duration-300">
                    🎓
                </div>
                <div class="flex-1 md:w-full text-left md:text-center">
                    <h2 class="text-lg sm:text-xl font-bold text-indigo-950 mb-0.5 sm:mb-1">Mahasiswa</h2>
                    <p class="text-xs sm:text-sm text-slate-500 hidden sm:block">KRS, Jadwal, Nilai & Transkrip</p>
                </div>
                <!-- Panah indikator (Mobile only) -->
                <div class="md:hidden text-slate-300 group-hover:text-yellow-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            <!-- 2. Dosen -->
            <a href="/dosen" class="card-2 group relative bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-indigo-400 transition-all duration-200 flex flex-row md:flex-col items-center md:text-center gap-4 sm:gap-5 focus:outline-none focus:ring-4 focus:ring-indigo-400/20 active:scale-[0.98]">
                <div class="h-12 w-12 sm:h-16 sm:w-16 shrink-0 bg-indigo-50 rounded-xl sm:rounded-2xl flex items-center justify-center text-2xl sm:text-3xl group-hover:scale-110 transition-transform duration-300">
                    👨‍🏫
                </div>
                <div class="flex-1 md:w-full text-left md:text-center">
                    <h2 class="text-lg sm:text-xl font-bold text-indigo-950 mb-0.5 sm:mb-1">Dosen</h2>
                    <p class="text-xs sm:text-sm text-slate-500 hidden sm:block">Presensi, Penilaian & Bimbingan</p>
                </div>
                <div class="md:hidden text-slate-300 group-hover:text-indigo-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            <!-- 3. Administrator -->
            <a href="/admin" class="card-3 group relative bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-slate-400 transition-all duration-200 flex flex-row md:flex-col items-center md:text-center gap-4 sm:gap-5 focus:outline-none focus:ring-4 focus:ring-slate-400/20 active:scale-[0.98]">
                <div class="h-12 w-12 sm:h-16 sm:w-16 shrink-0 bg-slate-100 rounded-xl sm:rounded-2xl flex items-center justify-center text-2xl sm:text-3xl group-hover:scale-110 transition-transform duration-300">
                    ⚙️
                </div>
                <div class="flex-1 md:w-full text-left md:text-center">
                    <h2 class="text-lg sm:text-xl font-bold text-indigo-950 mb-0.5 sm:mb-1">Administrator</h2>
                    <p class="text-xs sm:text-sm text-slate-500 hidden sm:block">Manajemen Master Data & Sistem</p>
                </div>
                <div class="md:hidden text-slate-300 group-hover:text-slate-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

        </div>
    </main>

    <!-- FOOTER (Help & Copyright) -->
    <footer class="w-full p-4 sm:p-6 shrink-0 flex flex-col items-center justify-center gap-2 text-center z-10">
        <button type="button" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors flex items-center gap-1.5 bg-indigo-50 px-4 py-2 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Panduan Pemilihan Akun
        </button>
        <p class="text-xs text-slate-400 mt-2">
            &copy; 2026 Universitas Stella Maris Sumba.
        </p>
    </footer>

</body>

</html>