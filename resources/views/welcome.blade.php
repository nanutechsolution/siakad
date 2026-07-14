<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIAKAD | Universitas Stella Maris Sumba</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="icon" href="{{ asset('favicons/logo-unmaris.svg') }}" type="image/svg+xml">
    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        unmaris: {
                            // Menggunakan warna biru dongker (Navy) yang senada dengan warna logo
                            900: '#1e1b4b',
                            800: '#312e81',
                            600: '#4f46e5',
                            100: '#e0e7ff',
                            50: '#f8fafc',
                        },
                        unmaris_yellow: {
                            // Warna kuning dari logo untuk aksen jika diperlukan
                            400: '#facc15',
                            500: '#eab308',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 antialiased min-h-screen flex flex-col relative overflow-x-hidden">

    <!-- Background Ornaments (Subtle & Professional) -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-slate-200/50 blur-3xl"></div>
        <div class="absolute top-[60%] -left-[10%] w-[40%] h-[40%] rounded-full bg-indigo-100/30 blur-3xl"></div>
    </div>

    <!-- Top Navigation Bar -->
    <nav class="relative z-10 w-full bg-white border-b border-gray-200 py-4 px-6 sm:px-12 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
            <!-- Menampilkan Logo Asli -->
            <img src="{{ asset('images/logo-unmaris.png') }}" alt="Logo Universitas Stella Maris Sumba" class="w-12 h-12 object-contain drop-shadow-sm">

            <div>
                <h1 class="text-sm font-bold text-unmaris-900 tracking-wide uppercase">Univ. Stella Maris Sumba</h1>
                <p class="text-xs text-slate-500 font-medium">Sistem Informasi Akademik</p>
            </div>
        </div>
        <div class="hidden sm:block text-sm text-slate-500 font-medium">
            Portal Layanan Terpadu
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 flex-grow flex flex-col items-center justify-center px-4 sm:px-6 py-12">

        <div class="text-center max-w-3xl mx-auto mb-14">
            <!-- Menampilkan Logo di Tengah, ukuran diperbesar -->
            <img src="{{ asset('images/logo-unmaris.png') }}" alt="SIAKAD Unmaris" class="w-32 h-32 mx-auto mb-6 object-contain drop-shadow-md">

            <h2 class="text-3xl md:text-5xl font-extrabold text-unmaris-900 tracking-tight mb-4">
                Selamat Datang di SIAKAD
            </h2>
            <p class="text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto">
                Fasilitas layanan akademik digital terintegrasi untuk civitas akademika <strong class="text-unmaris-900">Universitas Stella Maris Sumba (UNMARIS)</strong>. Silakan masuk melalui portal yang sesuai dengan otoritas Anda.
            </p>
        </div>

        <!-- Portal Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-5xl mb-12">

            <!-- Mahasiswa -->
            <a href="/mahasiswa" class="group relative bg-white rounded-xl border border-gray-200 p-8 shadow-sm hover:shadow-xl hover:border-unmaris-600 hover:ring-1 hover:ring-unmaris-600 transition-all duration-300 flex flex-col h-full">
                <div class="mb-6 inline-flex items-center justify-center w-14 h-14 rounded-lg bg-unmaris-100 text-unmaris-800 group-hover:bg-unmaris-800 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-unmaris-900 mb-3">Portal Mahasiswa</h3>
                <p class="text-sm text-slate-600 flex-grow mb-6 leading-relaxed">
                    Akses layanan akademik mahasiswa termasuk Kartu Rencana Studi (KRS), transkrip nilai, dan jadwal perkuliahan.
                </p>
                <div class="flex items-center text-sm font-semibold text-unmaris-800 group-hover:text-unmaris-600 transition-colors">
                    Masuk Portal
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </a>

            <!-- Dosen -->
            <a href="/dosen" class="group relative bg-white rounded-xl border border-gray-200 p-8 shadow-sm hover:shadow-xl hover:border-unmaris-600 hover:ring-1 hover:ring-unmaris-600 transition-all duration-300 flex flex-col h-full">
                <div class="mb-6 inline-flex items-center justify-center w-14 h-14 rounded-lg bg-unmaris-100 text-unmaris-800 group-hover:bg-unmaris-800 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-unmaris-900 mb-3">Portal Dosen</h3>
                <p class="text-sm text-slate-600 flex-grow mb-6 leading-relaxed">
                    Sistem manajemen kegiatan belajar mengajar, input nilai mahasiswa, absensi, dan bimbingan akademik.
                </p>
                <div class="flex items-center text-sm font-semibold text-unmaris-800 group-hover:text-unmaris-600 transition-colors">
                    Masuk Portal
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </a>

            <!-- Admin -->
            <a href="/admin" class="group relative bg-white rounded-xl border border-gray-200 p-8 shadow-sm hover:shadow-xl hover:border-unmaris-600 hover:ring-1 hover:ring-unmaris-600 transition-all duration-300 flex flex-col h-full">
                <div class="mb-6 inline-flex items-center justify-center w-14 h-14 rounded-lg bg-unmaris-100 text-unmaris-800 group-hover:bg-unmaris-800 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-unmaris-900 mb-3">Portal Administrator</h3>
                <p class="text-sm text-slate-600 flex-grow mb-6 leading-relaxed">
                    Pusat kendali master data, pengaturan periode semester, manajemen pengguna, dan laporan operasional.
                </p>
                <div class="flex items-center text-sm font-semibold text-unmaris-800 group-hover:text-unmaris-600 transition-colors">
                    Masuk Portal
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </a>

        </div>
    </main>

    <!-- Professional Footer Section -->
    <footer class="relative z-10 border-t border-gray-200 bg-white pt-12 pb-6">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">

            <!-- Footer Links Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <!-- Kolom 1: Info Kampus -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/logo-unmaris.png') }}" alt="Logo Unmaris" class="w-10 h-10 object-contain">
                        <span class="font-bold text-unmaris-900">Univ. Stella Maris Sumba</span>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">
                        Menyelenggarakan pendidikan tinggi yang unggul, berkarakter, dan berdaya saing untuk memajukan potensi Sumba dan Indonesia.
                    </p>
                    <div class="text-sm text-slate-600 space-y-2">
                        <p class="flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0 text-unmaris-900 mt-0.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>Kampus Utama UNMARIS<br>Tambolaka, Sumba Barat Daya<br>Nusa Tenggara Timur</span>
                        </p>
                    </div>
                </div>

                <!-- Kolom 2: Aplikasi Lainnya -->
                <div>
                    <h4 class="font-bold text-unmaris-900 mb-4 tracking-wide">Layanan Akademik</h4>
                    <ul class="space-y-3 text-sm text-slate-600">
                        <li>
                            <a href="unmarssumba.ac.id" class="hover:text-unmaris-600 hover:underline transition-colors flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-unmaris-500"></span> Website Resmi UNMARIS
                            </a>
                        </li>
                        <li>
                            <a href="pmbunmarssumba.ac.id" class="hover:text-unmaris-600 hover:underline transition-colors flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-unmaris-500"></span> Penerimaan Mahasiswa Baru (PMB)
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Kolom 3: Bantuan & Kontak -->
                <div>
                    <h4 class="font-bold text-unmaris-900 mb-4 tracking-wide">Pusat Bantuan SIAKAD</h4>
                    <ul class="space-y-3 text-sm text-slate-600">
                        <li>
                            <a href="#" class="hover:text-unmaris-600 hover:underline transition-colors flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                helpdesk@unmaris.ac.id
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-unmaris-600 hover:underline transition-colors flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.896-1.596-5.48-4.08-7.074-6.996l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                                IT Support: (0387) 123456
                            </a>
                        </li>
                        <li class="pt-2">
                            <p class="text-xs italic text-slate-500">Jam Operasional Layanan IT:<br>Senin - Jumat, 08:00 - 16:00 WITA</p>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="border-t border-gray-200 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-slate-600 text-center md:text-left">
                    &copy; {{ date('Y') }} <strong>Universitas Stella Maris Sumba</strong>. Hak Cipta Dilindungi.
                </p>
                <div class="flex items-center gap-2">
                    <p class="text-xs text-slate-600 bg-slate-100 px-3 py-1.5 rounded-full border border-gray-200">
                        Dikembangkan oleh <span class="font-semibold text-unmaris-600">BTSI</span> <span class="font-semibold text-yellow-500">UNMARIS</span>
                    </p>
                </div>
            </div>

        </div>
    </footer>

</body>

</html>