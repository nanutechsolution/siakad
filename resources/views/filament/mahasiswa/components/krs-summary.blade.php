<div class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-primary-600 to-primary-900 text-white shadow-lg">
    {{-- Dekorasi --}}
    <div class="pointer-events-none absolute -mr-20 -mt-20 right-0 top-0 h-64 w-64 rounded-full bg-white opacity-5 blur-3xl"></div>
    <div class="pointer-events-none absolute -ml-10 -mb-10 bottom-0 left-0 h-40 w-40 rounded-full bg-white opacity-5 blur-2xl"></div>

    <div class="relative z-10 p-5 sm:p-7 md:p-8">

        {{-- Header --}}
        <div class="mb-6 sm:mb-8">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15">
                    <x-heroicon-o-clipboard-document-check class="h-5 w-5" />
                </div>

                <div>
                    <h2 class="text-2xl font-black tracking-tight sm:text-3xl">
                        Ringkasan KRS
                    </h2>

                    <p class="mt-1 text-sm font-medium text-primary-100 sm:text-base">
                        @if(($modeKrs ?? 'PAKET') === 'PAKET')
                        KRS Anda sudah disiapkan berdasarkan kurikulum dan kelas.
                        @else
                        Pantau mata kuliah dan jumlah SKS yang Anda pilih.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">

            {{-- Semester --}}
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 transition-colors hover:bg-white/15">
                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-primary-200 sm:text-xs">
                    Semester
                </div>

                <div class="text-xl font-black sm:text-3xl">
                    {{ $semesterMhs ?? '-' }}
                </div>

                <div class="mt-1 text-xs font-medium text-primary-200">
                    Tahun Akademik
                </div>
            </div>


            {{-- Skema --}}
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 transition-colors hover:bg-white/15">

                @if(($modeKrs ?? 'PAKET') === 'PAKET')

                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-primary-200 sm:text-xs">
                    Skema KRS
                </div>

                <div class="mt-1.5">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/95 px-2.5 py-1.5 text-xs font-black text-primary-800 shadow-sm sm:text-sm">
                        <x-heroicon-o-lock-closed class="h-3.5 w-3.5" />
                        PAKET
                    </span>
                </div>

                <div class="mt-2 text-xs font-medium text-primary-200">
                    Disiapkan otomatis
                </div>

                @else

                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-primary-200 sm:text-xs">
                    IPS Semester Lalu
                </div>

                <div class="text-xl font-black sm:text-3xl">
                    {{ number_format($ips ?? 0, 2) }}
                </div>

                <div class="mt-1 text-xs font-medium text-primary-200">
                    Dasar batas SKS

                </div>

                @endif

            </div>


            {{-- Beban SKS --}}
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 transition-colors hover:bg-white/15">

                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-primary-200 sm:text-xs">
                    {{ ($modeKrs ?? 'PAKET') === 'PAKET'
                        ? 'Beban Semester'
                        : 'Batas Maksimal'
                    }}
                </div>

                <div class="text-xl font-black sm:text-3xl">
                    {{ $maxSks ?? '-' }}

                    <span class="text-sm font-medium text-primary-200">
                        SKS
                    </span>
                </div>

                @if(($modeKrs ?? 'PAKET') === 'PAKET')
                <div class="mt-1 text-xs font-medium text-primary-200">
                    Sesuai kurikulum
                </div>
                @else
                <div class="mt-1 text-xs font-medium text-primary-200">
                    Sesuai IPS
                </div>
                @endif

            </div>


            {{-- Total KRS --}}
            <div class="flex origin-bottom transform flex-col justify-center rounded-2xl bg-white p-4 text-primary-900 shadow-[0_0_20px_rgba(255,255,255,0.15)] lg:scale-105">

                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-primary-500 sm:text-xs">
                    Total KRS
                </div>

                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-black leading-none text-primary-700 sm:text-4xl">
                        {{ $totalSks }}
                    </span>

                    <span class="text-sm font-bold text-primary-400 sm:text-base">
                        SKS
                    </span>
                </div>

                <div class="mt-1 text-xs font-bold text-primary-400">
                    {{ $totalMk }} Mata Kuliah
                </div>

            </div>

        </div>


        {{-- Informasi PAKET --}}
        @if(($modeKrs ?? 'PAKET') === 'PAKET')

        <div class="mt-5 flex items-start gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3.5">
            <x-heroicon-o-information-circle class="mt-0.5 h-5 w-5 shrink-0 text-primary-100" />

            <div class="min-w-0">
                <p class="text-sm font-semibold text-white">
                    KRS sudah disiapkan untuk Anda
                </p>

                <p class="mt-0.5 text-xs leading-5 text-primary-100 sm:text-sm">
                    Periksa daftar mata kuliah dan jadwal di bawah.
                    Jika sudah sesuai, lanjutkan dengan mengajukan KRS kepada Dosen Wali.
                </p>
            </div>
        </div>

        @endif

    </div>
</div>