<div class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-primary-600 to-primary-900 text-white shadow-lg">
    {{-- Dekorasi --}}
    <div class="pointer-events-none absolute -mr-20 -mt-20 right-0 top-0 h-64 w-64 rounded-full bg-white opacity-5 blur-3xl"></div>
    <div class="pointer-events-none absolute -ml-10 -mb-10 bottom-0 left-0 h-40 w-40 rounded-full bg-white opacity-5 blur-2xl"></div>

    <div class="relative z-10 p-5 sm:p-7 md:p-8">

        {{-- Header --}}
        <div class="mb-6 sm:mb-8">
            <h2 class="text-2xl font-black tracking-tight sm:text-3xl">
                Ringkasan KRS
            </h2>
            <p class="mt-1 text-sm font-medium text-primary-100 sm:text-base">
                Pantau mata kuliah dan jumlah SKS yang Anda pilih.
            </p>
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
            </div>

            {{-- Skema --}}
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 transition-colors hover:bg-white/15">
                @if(($modeKrs ?? 'PAKET') === 'PAKET')

                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-primary-200 sm:text-xs">
                    Skema
                </div>

                <div class="mt-1.5">
                    <span class="inline-flex items-center gap-1 rounded-md bg-white/90 px-2.5 py-1 text-xs font-black text-primary-800 sm:text-sm">
                        <x-heroicon-o-lock-closed class="h-3.5 w-3.5" />
                        PAKET
                    </span>
                </div>

                @else

                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-primary-200 sm:text-xs">
                    IPS Semester Lalu
                </div>

                <div class="text-xl font-black sm:text-3xl">
                    {{ number_format($ips ?? 0, 2) }}
                </div>

                @endif
            </div>

            {{-- Beban SKS --}}
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 transition-colors hover:bg-white/15">
                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-primary-200 sm:text-xs">
                    {{ ($modeKrs ?? 'PAKET') === 'PAKET'
                        ? 'Beban SKS'
                        : 'Batas Maksimal'
                    }}
                </div>

                <div class="text-xl font-black sm:text-3xl">
                    {{ $maxSks ?? '-' }}

                    <span class="text-sm font-medium text-primary-200">
                        SKS
                    </span>
                </div>
            </div>

            {{-- Dipilih --}}
            <div class="flex origin-bottom transform flex-col justify-center rounded-2xl bg-white p-4 text-primary-900 shadow-[0_0_20px_rgba(255,255,255,0.15)] lg:scale-105">

                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-primary-500 sm:text-xs">
                    Dipilih
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
    </div>
</div>