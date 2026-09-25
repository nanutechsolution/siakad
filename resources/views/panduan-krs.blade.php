<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Universitas Stella Maris Sumba">
    <meta name="description" content="Panduan resmi alur pengisian Kartu Rencana Studi (KRS) pada SIAKAD Universitas Stella Maris Sumba.">
    <meta name="robots" content="index, follow">

    <title>Panduan Pengisian KRS - SIAKAD UNMARIS</title>

    <link rel="canonical" href="{{ route('panduan.krs') }}">
    @include('components.pwa.head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.pwa.register')

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            * {
                animation-duration: 0.001ms !important;
                transition-duration: 0.001ms !important;
            }
        }

        @keyframes fadeUp {
            0% {
                opacity: 0;
                transform: translateY(16px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-anim>* {
            opacity: 0;
            animation: fadeUp 0.7s ease forwards;
        }

        .hero-anim>*:nth-child(1) {
            animation-delay: .05s;
        }

        .hero-anim>*:nth-child(2) {
            animation-delay: .15s;
        }

        .hero-anim>*:nth-child(3) {
            animation-delay: .25s;
        }

        .hero-anim>*:nth-child(4) {
            animation-delay: .35s;
        }

        .step-item {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity .6s ease, transform .6s ease;
        }

        .step-item.in-view {
            opacity: 1;
            transform: translateY(0);
        }

        .rail-fill {
            transition: height .15s ease-out;
        }

        .progress-fill {
            transition: width .08s linear;
        }

        .lightbox {
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s ease;
        }

        .lightbox.open {
            opacity: 1;
            pointer-events: auto;
        }

        .lightbox img {
            transform: scale(.94);
            transition: transform .25s ease;
        }

        .lightbox.open img {
            transform: scale(1);
        }

        .faq-a {
            max-height: 0;
            overflow: hidden;
            transition: max-height .3s ease;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 antialiased w-full selection:bg-yellow-500 selection:text-indigo-950">

    <!-- Reading progress bar -->
    <div class="fixed top-0 left-0 right-0 h-1 bg-slate-200/50 z-50">
        <div id="progressFill" class="progress-fill h-full w-0 bg-gradient-to-r from-yellow-500 to-yellow-300"></div>
    </div>

    <!-- Sticky step nav -->
    <nav id="stepnav"
        class="fixed top-1 left-0 right-0 z-40 bg-indigo-950/95 backdrop-blur flex items-center justify-center gap-1.5 px-3 py-2.5 -translate-y-24 transition-transform duration-300 overflow-x-auto">
        <button data-target="step-1"
            class="stepnav-btn flex-none text-xs font-semibold text-indigo-100 border border-white/20 px-3 py-1.5 rounded-full whitespace-nowrap hover:border-yellow-400 transition">1
            · Login</button>
        <button data-target="step-2"
            class="stepnav-btn flex-none text-xs font-semibold text-indigo-100 border border-white/20 px-3 py-1.5 rounded-full whitespace-nowrap hover:border-yellow-400 transition">2
            · Menu KRS</button>
        <button data-target="step-3"
            class="stepnav-btn flex-none text-xs font-semibold text-indigo-100 border border-white/20 px-3 py-1.5 rounded-full whitespace-nowrap hover:border-yellow-400 transition">3
            · Cek Mata Kuliah</button>
        <button data-target="step-4"
            class="stepnav-btn flex-none text-xs font-semibold text-indigo-100 border border-white/20 px-3 py-1.5 rounded-full whitespace-nowrap hover:border-yellow-400 transition">4
            · Ajukan</button>
        <button data-target="step-5"
            class="stepnav-btn flex-none text-xs font-semibold text-indigo-100 border border-white/20 px-3 py-1.5 rounded-full whitespace-nowrap hover:border-yellow-400 transition">5
            · Persetujuan</button>
    </nav>

    <!-- Hero -->
    <header class="relative overflow-hidden bg-gradient-to-br from-indigo-950 via-indigo-950 to-slate-900 text-white px-6 pt-20 pb-24 text-center">
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-yellow-400/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-16 w-64 h-64 bg-yellow-400/5 rounded-full blur-3xl"></div>

        <div class="relative hero-anim max-w-xl mx-auto">
            <a href="{{ url('/') }}"
                class="inline-flex items-center gap-2 bg-white/10 border border-white/20 px-4 py-1.5 rounded-full text-xs font-semibold text-yellow-300 mb-6">
                ← Kembali ke Portal Mahasiswa
            </a>
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight leading-tight">
                Alur Pengisian<br>Kartu Rencana Studi
            </h1>
            <p class="text-sm sm:text-base text-indigo-200 mt-4 leading-relaxed">
                Panduan resmi SIAKAD Universitas Stella Maris Sumba untuk Tahun Akademik Ganjil 2026/2027 — lima
                langkah, dari login sampai KRS disetujui dosen wali.
            </p>
            <a href="#step-1"
                class="inline-flex items-center gap-2 mt-8 bg-gradient-to-br from-yellow-400 to-yellow-500 text-indigo-950 font-bold text-sm px-6 py-3 rounded-xl shadow-lg shadow-yellow-500/20 hover:-translate-y-0.5 transition">
                Mulai Panduan →
            </a>
        </div>
    </header>

    <!-- Note callout -->
    <div class="max-w-xl mx-auto px-5 -mt-10 relative z-10">
        <div class="bg-white border border-slate-200 border-l-4 border-l-yellow-500 rounded-xl p-4 shadow-lg shadow-indigo-950/5 text-sm text-slate-600 leading-relaxed">
            <b class="text-slate-900">Catatan penting:</b> KRS Anda disiapkan otomatis oleh sistem sesuai kurikulum
            dan kelas. Anda tidak perlu memilih mata kuliah satu per satu — cukup periksa kebenarannya lalu ajukan ke
            dosen wali.
        </div>
    </div>

    <!-- Timeline -->
    <main class="max-w-xl mx-auto px-5 pt-14">
        <div id="timeline" class="relative">
            <div class="absolute left-[27px] top-1 bottom-1 w-0.5 bg-slate-200 rounded"></div>
            <div id="railFill" class="rail-fill absolute left-[27px] top-1 w-0.5 h-0 bg-gradient-to-b from-yellow-500 to-yellow-300 rounded"></div>

            <!-- Step 1 -->
            <div id="step-1" class="step-item relative pl-16 pb-14">
                <div class="step-node absolute left-1.5 top-0 w-11 h-11 rounded-full bg-white border-[3px] border-slate-200 flex items-center justify-center font-extrabold text-slate-400 transition-all">1</div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-start gap-5 shadow-sm">
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold uppercase tracking-wide text-yellow-600 mb-1.5">Langkah Pertama</div>
                        <h3 class="text-lg font-bold text-indigo-950 mb-1.5">Login &amp; Cek Dasbor</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">Masuk ke SIAKAD, lalu periksa data akun Anda
                            di halaman Dasbor: <b class="text-slate-700">kelas, angkatan, kurikulum,</b> dan
                            <b class="text-slate-700">dosen wali</b>.
                        </p>
                    </div>
                    <button type="button" class="shot-btn flex-none w-24 rounded-xl overflow-hidden border-[3px] border-indigo-950 shadow-lg cursor-zoom-in"
                        data-full="{{ asset('images/panduan-krs/step-1-dasbor.png') }}">
                        <img src="{{ asset('images/panduan-krs/step-1-dasbor.png') }}" alt="Tampilan Dasbor Mahasiswa" class="w-full block">
                    </button>
                </div>
            </div>

            <!-- Step 2 -->
            <div id="step-2" class="step-item relative pl-16 pb-14">
                <div class="step-node absolute left-1.5 top-0 w-11 h-11 rounded-full bg-white border-[3px] border-slate-200 flex items-center justify-center font-extrabold text-slate-400 transition-all">2</div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-start gap-5 shadow-sm">
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold uppercase tracking-wide text-yellow-600 mb-1.5">Langkah Kedua</div>
                        <h3 class="text-lg font-bold text-indigo-950 mb-1.5">Buka Menu KRS</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">Tekan ikon menu (≡) di pojok kiri atas, lalu
                            pilih <b class="text-slate-700">Kartu Rencana Studi → Isi KRS</b>.</p>
                    </div>
                    <button type="button" class="shot-btn flex-none w-24 rounded-xl overflow-hidden border-[3px] border-indigo-950 shadow-lg cursor-zoom-in"
                        data-full="{{ asset('images/panduan-krs/step-2-menu.png') }}">
                        <img src="{{ asset('images/panduan-krs/step-2-menu.png') }}" alt="Menu navigasi hamburger" class="w-full block">
                    </button>
                </div>
            </div>

            <!-- Step 3 -->
            <div id="step-3" class="step-item relative pl-16 pb-14">
                <div class="step-node absolute left-1.5 top-0 w-11 h-11 rounded-full bg-white border-[3px] border-slate-200 flex items-center justify-center font-extrabold text-slate-400 transition-all">3</div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-start gap-5 shadow-sm">
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold uppercase tracking-wide text-yellow-600 mb-1.5">Langkah Ketiga</div>
                        <h3 class="text-lg font-bold text-indigo-950 mb-1.5">Periksa Mata Kuliah</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">KRS sudah disiapkan otomatis sesuai
                            kurikulum. Periksa <b class="text-slate-700">mata kuliah, jadwal, dosen, dan ruang</b>
                            sebelum melanjutkan.</p>
                    </div>
                    <button type="button" class="shot-btn flex-none w-24 rounded-xl overflow-hidden border-[3px] border-indigo-950 shadow-lg cursor-zoom-in"
                        data-full="{{ asset('images/panduan-krs/step-3-mata-kuliah.png') }}">
                        <img src="{{ asset('images/panduan-krs/step-3-mata-kuliah.png') }}" alt="Ringkasan KRS otomatis" class="w-full block">
                    </button>
                </div>
            </div>

            <!-- Step 4 -->
            <div id="step-4" class="step-item relative pl-16 pb-14">
                <div class="step-node absolute left-1.5 top-0 w-11 h-11 rounded-full bg-white border-[3px] border-slate-200 flex items-center justify-center font-extrabold text-slate-400 transition-all">4</div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-start gap-5 shadow-sm">
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold uppercase tracking-wide text-yellow-600 mb-1.5">Langkah Keempat</div>
                        <h3 class="text-lg font-bold text-indigo-950 mb-1.5">Ajukan KRS</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">Tekan <b class="text-slate-700">Ajukan KRS
                                ke Dosen Wali</b>, lalu konfirmasi jumlah mata kuliah &amp; SKS dengan menekan
                            <b class="text-slate-700">Ya, Ajukan KRS</b>.
                        </p>
                    </div>
                    <button type="button" class="shot-btn flex-none w-24 rounded-xl overflow-hidden border-[3px] border-indigo-950 shadow-lg cursor-zoom-in"
                        data-full="{{ asset('images/panduan-krs/step-4-ajukan.png') }}">
                        <img src="{{ asset('images/panduan-krs/step-4-ajukan.png') }}" alt="Konfirmasi pengajuan KRS" class="w-full block">
                    </button>
                </div>
            </div>

            <!-- Step 5 -->
            <div id="step-5" class="step-item relative pl-16 pb-2">
                <div class="step-node absolute left-1.5 top-0 w-11 h-11 rounded-full bg-white border-[3px] border-slate-200 flex items-center justify-center font-extrabold text-slate-400 transition-all">5</div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-start gap-5 shadow-sm">
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold uppercase tracking-wide text-yellow-600 mb-1.5">Langkah Kelima</div>
                        <h3 class="text-lg font-bold text-indigo-950 mb-1.5">Menunggu Persetujuan</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">KRS berhasil diajukan dan akses sementara
                            terkunci. Pantau status persetujuan di menu <b class="text-slate-700">Riwayat KRS</b>.</p>
                    </div>
                    <button type="button" class="shot-btn flex-none w-24 rounded-xl overflow-hidden border-[3px] border-indigo-950 shadow-lg cursor-zoom-in"
                        data-full="{{ asset('images/panduan-krs/step-5-persetujuan.png') }}">
                        <img src="{{ asset('images/panduan-krs/step-5-persetujuan.png') }}" alt="Status menunggu persetujuan" class="w-full block">
                    </button>
                </div>
            </div>
        </div>

        <!-- Finale -->
        <div class="bg-gradient-to-br from-indigo-950 to-slate-900 text-white rounded-2xl p-8 text-center mt-2 mb-14 ml-16 shadow-xl">
            <div class="w-14 h-14 rounded-full bg-yellow-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-indigo-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-yellow-300 font-bold tracking-wide text-lg mb-1.5">KRS TERKIRIM</h3>
            <p class="text-indigo-200 text-sm max-w-sm mx-auto">Setelah disetujui Dosen Wali, KRS Anda resmi berlaku
                untuk semester ini. Anda akan melihat perubahan status di menu Riwayat KRS.</p>
        </div>
    </main>

    <!-- FAQ -->
    <section class="max-w-xl mx-auto px-5 pb-16">
        <div class="text-center mb-6">
            <div class="text-xs font-bold uppercase tracking-wide text-yellow-600">Pertanyaan Umum</div>
            <h2 class="text-2xl font-bold text-indigo-950 mt-1.5">Yang sering ditanyakan</h2>
        </div>

        <div class="faq-item border border-slate-200 rounded-xl mb-2.5 bg-white overflow-hidden">
            <button type="button" class="faq-q w-full text-left bg-none border-none px-4 py-3.5 text-sm font-semibold text-indigo-950 flex justify-between items-center gap-3">
                Apakah saya perlu memilih mata kuliah sendiri?
                <span class="plus text-yellow-600 flex-none transition-transform">+</span>
            </button>
            <div class="faq-a">
                <p class="px-4 pb-4 text-sm text-slate-500 leading-relaxed">Tidak. KRS Anda sudah disiapkan otomatis
                    oleh sistem berdasarkan kurikulum dan kelas yang Anda ikuti. Tugas Anda hanya memeriksa kebenaran
                    data sebelum mengajukan.</p>
            </div>
        </div>

        <div class="faq-item border border-slate-200 rounded-xl mb-2.5 bg-white overflow-hidden">
            <button type="button" class="faq-q w-full text-left bg-none border-none px-4 py-3.5 text-sm font-semibold text-indigo-950 flex justify-between items-center gap-3">
                Ke mana KRS saya diajukan?
                <span class="plus text-yellow-600 flex-none transition-transform">+</span>
            </button>
            <div class="faq-a">
                <p class="px-4 pb-4 text-sm text-slate-500 leading-relaxed">KRS diajukan ke Dosen Wali Anda untuk
                    diperiksa dan disetujui.</p>
            </div>
        </div>

        <div class="faq-item border border-slate-200 rounded-xl mb-2.5 bg-white overflow-hidden">
            <button type="button" class="faq-q w-full text-left bg-none border-none px-4 py-3.5 text-sm font-semibold text-indigo-950 flex justify-between items-center gap-3">
                Bagaimana cara mengecek status pengajuan?
                <span class="plus text-yellow-600 flex-none transition-transform">+</span>
            </button>
            <div class="faq-a">
                <p class="px-4 pb-4 text-sm text-slate-500 leading-relaxed">Buka menu Riwayat KRS untuk memantau
                    apakah pengajuan Anda sudah disetujui Dosen Wali.</p>
            </div>
        </div>
    </section>

    <footer class="text-center pb-10 px-5 text-xs text-slate-400">
        &copy; 2026 Universitas Stella Maris Sumba &middot; Panduan resmi Portal Mahasiswa SIAKAD
    </footer>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox fixed inset-0 z-50 bg-slate-950/90 flex items-center justify-center p-8">
        <button type="button" id="lightboxClose" class="absolute top-6 right-6 w-10 h-10 rounded-full bg-white/10 border border-white/30 text-white text-lg">✕</button>
        <img id="lightboxImg" src="" alt="Tampilan penuh screenshot" class="max-h-[88vh] max-w-[420px] w-full rounded-2xl border-4 border-white shadow-2xl">
    </div>

    <script>
        (function() {
            var progressFill = document.getElementById('progressFill');

            function updateProgress() {
                var doc = document.documentElement;
                var scrollTop = doc.scrollTop || document.body.scrollTop;
                var height = doc.scrollHeight - doc.clientHeight;
                var pct = height > 0 ? (scrollTop / height) * 100 : 0;
                progressFill.style.width = pct + '%';
            }

            var stepnav = document.getElementById('stepnav');
            var navButtons = Array.prototype.slice.call(stepnav.querySelectorAll('.stepnav-btn'));
            var heroHeight = document.querySelector('header').offsetHeight;

            navButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var target = document.getElementById(btn.getAttribute('data-target'));
                    if (target) target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                });
            });

            var timeline = document.getElementById('timeline');
            var railFill = document.getElementById('railFill');
            var stepItems = Array.prototype.slice.call(document.querySelectorAll('.step-item'));

            function updateRail() {
                var rect = timeline.getBoundingClientRect();
                var vh = window.innerHeight;
                var total = rect.height;
                var visibleProgress = (vh * 0.5 - rect.top);
                var pct = Math.max(0, Math.min(1, visibleProgress / total));
                railFill.style.height = (pct * 100) + '%';
            }

            var currentActive = null;

            function setActiveNav(id) {
                if (id === currentActive) return;
                currentActive = id;
                navButtons.forEach(function(btn) {
                    var active = btn.getAttribute('data-target') === id;
                    btn.classList.toggle('bg-yellow-400', active);
                    btn.classList.toggle('text-indigo-950', active);
                    btn.classList.toggle('border-yellow-400', active);
                    btn.classList.toggle('text-indigo-100', !active);
                });
            }

            if ('IntersectionObserver' in window) {
                var revealObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) entry.target.classList.add('in-view');
                    });
                }, {
                    threshold: 0.25
                });
                stepItems.forEach(function(el) {
                    revealObserver.observe(el);
                });

                var activeObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) setActiveNav(entry.target.id);
                    });
                }, {
                    threshold: 0,
                    rootMargin: '-45% 0px -45% 0px'
                });
                stepItems.forEach(function(el) {
                    activeObserver.observe(el);
                });
            } else {
                stepItems.forEach(function(el) {
                    el.classList.add('in-view');
                });
            }

            function onScroll() {
                updateProgress();
                updateRail();
                stepnav.classList.toggle('-translate-y-24', window.scrollY <= heroHeight * 0.6);
                stepnav.classList.toggle('translate-y-0', window.scrollY > heroHeight * 0.6);
            }
            window.addEventListener('scroll', onScroll, {
                passive: true
            });
            window.addEventListener('resize', onScroll);
            onScroll();

            var lightbox = document.getElementById('lightbox');
            var lightboxImg = document.getElementById('lightboxImg');
            var lightboxClose = document.getElementById('lightboxClose');

            document.querySelectorAll('.shot-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    lightboxImg.src = btn.getAttribute('data-full');
                    lightbox.classList.add('open');
                });
            });

            function closeLightbox() {
                lightbox.classList.remove('open');
            }
            lightboxClose.addEventListener('click', closeLightbox);
            lightbox.addEventListener('click', function(e) {
                if (e.target === lightbox) closeLightbox();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeLightbox();
            });

            document.querySelectorAll('.faq-item').forEach(function(item) {
                var q = item.querySelector('.faq-q');
                var a = item.querySelector('.faq-a');
                var plus = item.querySelector('.plus');
                q.addEventListener('click', function() {
                    var isOpen = a.style.maxHeight && a.style.maxHeight !== '0px';
                    document.querySelectorAll('.faq-item .faq-a').forEach(function(o) {
                        o.style.maxHeight = null;
                    });
                    document.querySelectorAll('.faq-item .plus').forEach(function(p) {
                        p.style.transform = 'rotate(0deg)';
                    });
                    if (!isOpen) {
                        a.style.maxHeight = a.scrollHeight + 'px';
                        plus.style.transform = 'rotate(45deg)';
                    }
                });
            });
        })();
    </script>

</body>

</html>