<div class="relative overflow-hidden bg-gradient-to-br from-primary-600 to-primary-900 rounded-3xl shadow-lg text-white mb-6">
    <!-- Elemen Dekorasi Latar Belakang -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-40 h-40 bg-white opacity-5 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>

    <div class="relative z-10 p-5 sm:p-7 md:p-8">
        <!-- Header Ringkasan -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 sm:mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight drop-shadow-sm">Ringkasan Pengisian KRS</h2>
                <p class="text-primary-100 text-sm sm:text-base font-medium mt-1 flex items-center gap-1.5 opacity-90">
                    <x-heroicon-o-calendar-days class="w-5 h-5"/> Tahun Akademik {{ $activeTa->nama_tahun ?? '-' }}
                </p>
            </div>

            <!-- Badge Status -->
            <div class="px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl flex items-center gap-3 w-full sm:w-auto shadow-sm">
                <div class="text-primary-100 text-[10px] sm:text-xs font-bold uppercase tracking-wider">Status Formulir</div>
                <div class="text-xs sm:text-sm font-black bg-white text-primary-800 px-2.5 py-1 rounded-md shadow-sm">
                    {{ $statusKrs ?? 'DRAFT' }}
                </div>
            </div>
        </div>

        <!-- Grid Statistik -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
            <!-- Box 1 -->
            <div class="p-4 bg-white/10 hover:bg-white/15 transition-colors rounded-2xl border border-white/10">
                <div class="text-[10px] sm:text-xs font-bold text-primary-200 uppercase tracking-widest mb-1">Semester Anda</div>
                <div class="text-xl sm:text-3xl font-black">{{ $semesterMhs ?? '-' }}</div>
            </div>

            <!-- Box 2 -->
            <div class="p-4 bg-white/10 hover:bg-white/15 transition-colors rounded-2xl border border-white/10">
                @if(($modeKrs ?? 'PAKET') === 'PAKET')
                    <div class="text-[10px] sm:text-xs font-bold text-primary-200 uppercase tracking-widest mb-1">Skema KRS</div>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="inline-flex items-center gap-1 text-xs sm:text-sm font-black bg-white/90 text-primary-800 px-2.5 py-1 rounded-md">
                            <x-heroicon-o-lock-closed class="w-3.5 h-3.5"/> PAKET
                        </span>
                    </div>
                @else
                    <div class="text-[10px] sm:text-xs font-bold text-primary-200 uppercase tracking-widest mb-1">IP Semester Lalu</div>
                    <div class="text-xl sm:text-3xl font-black">{{ number_format($ips ?? 0, 2) }}</div>
                @endif
            </div>

            <!-- Box 3 -->
            <div class="p-4 bg-white/10 hover:bg-white/15 transition-colors rounded-2xl border border-white/10">
                <div class="text-[10px] sm:text-xs font-bold text-primary-200 uppercase tracking-widest mb-1">
                    {{ ($modeKrs ?? 'PAKET') === 'PAKET' ? 'Total SKS Paket' : 'Batas Maksimal (IPS)' }}
                </div>
                <div class="text-xl sm:text-3xl font-black">{{ $maxSks ?? '-' }} <span class="text-sm font-medium text-primary-200">SKS</span></div>
            </div>

            <!-- Box 4 (Highlight) -->
            <div class="p-4 bg-white hover:bg-gray-50 transition-colors rounded-2xl shadow-[0_0_20px_rgba(255,255,255,0.15)] text-primary-900 flex flex-col justify-center transform scale-100 lg:scale-105 origin-bottom">
                <div class="text-[10px] sm:text-xs font-bold text-primary-500 uppercase tracking-widest mb-1">Total Diambil</div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl sm:text-4xl font-black text-primary-700 leading-none">{{ $totalSks }}</span>
                    <span class="text-sm sm:text-base font-bold text-primary-400">SKS</span>
                </div>
                <div class="text-xs font-bold text-primary-400 mt-1">
                    {{ $totalMk }} Mata Kuliah Dipilih
                </div>
            </div>
        </div>
    </div>
</div>