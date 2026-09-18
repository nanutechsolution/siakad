@php
$sisaKuota = max(0, ($jadwal->kuota_kelas ?? 0) - ($jadwal->isi_kelas ?? 0));

$namaRuang = $jadwal->ruang?->nama_ruang ?? 'Belum ditentukan';
$namaKelas = $jadwal->kelas?->nama_kelas ?? '-';
$kodeMk = $jadwal->mataKuliah?->kode_mk ?? '-';
$namaMk = $jadwal->mataKuliah?->nama_mk ?? 'Mata Kuliah';
$sks = $jadwal->mataKuliah?->sks_default ?? 0;

$jamMulai = $jadwal->jam_mulai
? substr($jadwal->jam_mulai, 0, 5)
: '-';

$jamSelesai = $jadwal->jam_selesai
? substr($jadwal->jam_selesai, 0, 5)
: '-';

$namaDosens = $jadwal->dosenPengajars
->map(function ($dp) {
$person = $dp->dosen?->person;

return $person?->nama_dengan_gelar
?? $person?->nama_lengkap;
})
->filter()
->values();

$isPenuh = $sisaKuota <= 0;

    // Ambil sifat MK dari kurikulum jadwal.
    // Jika jadwal tidak memiliki kurikulum_id,
    // gunakan kurikulum mahasiswa sebagai fallback.
    $semesterPaket='-' ;
    $sifatMk='W' ;

    $kurikulumIdAktif=$jadwal->kurikulum_id
    ?? $mahasiswaKurikulumId
    ?? null;

    if ($kurikulumIdAktif && $jadwal->mata_kuliah_id) {
    $kurikulumMk = \Illuminate\Support\Facades\DB::table('kurikulum_mata_kuliah')
    ->where('kurikulum_id', $kurikulumIdAktif)
    ->where('mata_kuliah_id', $jadwal->mata_kuliah_id)
    ->first();

    if ($kurikulumMk) {
    $semesterPaket = $kurikulumMk->semester_paket;
    $sifatMk = $kurikulumMk->sifat_mk;
    }
    }

    $teksSifatMk = match ($sifatMk) {
    'W' => 'Wajib',
    'P' => 'Pilihan',
    default => 'MK Lainnya',
    };
    @endphp

    <div
        class="group mb-3 w-full overflow-hidden rounded-2xl border bg-white shadow-sm transition-all duration-200
        dark:bg-gray-900
        {{ $isPenuh
            ? 'border-danger-200 dark:border-danger-800'
            : 'border-gray-200 hover:border-primary-300 hover:shadow-md dark:border-gray-800 dark:hover:border-primary-700'
        }}">
        <div class="flex flex-col sm:flex-row">

            {{-- =========================================================
             INFORMASI UTAMA
        ========================================================== --}}
            <div class="min-w-0 flex-1 p-4 sm:p-5">

                {{-- Badge --}}
                <div class="mb-3 flex flex-wrap items-center gap-2">

                    {{-- Kode MK --}}
                    <span
                        class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1
                           text-[11px] font-bold tracking-wide text-gray-700
                           ring-1 ring-inset ring-gray-200
                           dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                        {{ $kodeMk }}
                    </span>

                    {{-- SKS --}}
                    <span
                        class="inline-flex items-center rounded-md bg-primary-50 px-2.5 py-1
                           text-[11px] font-bold text-primary-700
                           ring-1 ring-inset ring-primary-200
                           dark:bg-primary-950/40 dark:text-primary-300 dark:ring-primary-800">
                        {{ $sks }} SKS
                    </span>

                    {{-- Sifat MK --}}
                    <span
                        class="inline-flex items-center rounded-md px-2.5 py-1
                           text-[11px] font-bold ring-1 ring-inset
                           {{ $sifatMk === 'W'
                                ? 'bg-success-50 text-success-700 ring-success-200 dark:bg-success-950/30 dark:text-success-300 dark:ring-success-800'
                                : 'bg-purple-50 text-purple-700 ring-purple-200 dark:bg-purple-950/30 dark:text-purple-300 dark:ring-purple-800'
                           }}">
                        {{ $teksSifatMk }}
                    </span>

                    {{-- Lintas Kelas --}}
                    @if($isLintasKelas)
                    <span
                        class="inline-flex items-center gap-1 rounded-md bg-warning-50 px-2.5 py-1
                               text-[11px] font-bold text-warning-700
                               ring-1 ring-inset ring-warning-200
                               dark:bg-warning-950/30 dark:text-warning-300 dark:ring-warning-800">
                        <x-heroicon-s-arrow-path class="h-3.5 w-3.5" />
                        Lintas Kelas
                    </span>
                    @endif

                </div>

                {{-- Nama Mata Kuliah --}}
                <h4
                    class="mb-4 text-base font-bold leading-snug text-gray-950
                       transition-colors group-hover:text-primary-600
                       sm:text-lg dark:text-white dark:group-hover:text-primary-400">
                    {{ $namaMk }}
                </h4>

                {{-- Detail Akademik --}}
                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">

                    {{-- Kelas --}}
                    <div class="flex min-w-0 items-center gap-2.5">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                               bg-gray-50 text-gray-500 ring-1 ring-gray-200
                               dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                            <x-heroicon-s-academic-cap class="h-4 w-4" />
                        </div>

                        <div class="min-w-0">
                            <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                                Kelas
                            </div>

                            <div class="truncate font-semibold text-gray-800 dark:text-gray-200">
                                {{ $namaKelas }}
                            </div>
                        </div>
                    </div>

                    {{-- Jadwal --}}
                    <div class="flex min-w-0 items-center gap-2.5">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                               bg-gray-50 text-gray-500 ring-1 ring-gray-200
                               dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                            <x-heroicon-s-clock class="h-4 w-4" />
                        </div>

                        <div class="min-w-0">
                            <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                                Jadwal
                            </div>

                            <div class="truncate font-semibold text-gray-800 dark:text-gray-200">
                                {{ $jadwal->hari ?? '-' }},
                                {{ $jamMulai }}–{{ $jamSelesai }}
                            </div>
                        </div>
                    </div>

                    {{-- Ruang --}}
                    <div class="flex min-w-0 items-center gap-2.5">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                               bg-gray-50 text-gray-500 ring-1 ring-gray-200
                               dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                            <x-heroicon-s-map-pin class="h-4 w-4" />
                        </div>

                        <div class="min-w-0">
                            <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                                Ruang
                            </div>

                            <div class="truncate font-semibold text-gray-800 dark:text-gray-200">
                                {{ $namaRuang }}
                            </div>
                        </div>
                    </div>

                    {{-- Dosen --}}
                    <div class="flex min-w-0 items-start gap-2.5">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                               bg-gray-50 text-gray-500 ring-1 ring-gray-200
                               dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                            <x-heroicon-s-user class="h-4 w-4" />
                        </div>

                        <div class="min-w-0">
                            <div class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                                Dosen
                            </div>

                            @if($namaDosens->isNotEmpty())
                            <div class="space-y-0.5">
                                @foreach($namaDosens as $dosen)
                                <div class="truncate font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $dosen }}
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="font-medium text-gray-400">
                                Belum ditentukan
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- =========================================================
             KAPASITAS
        ========================================================== --}}
            <div
                class="border-t px-4 py-3 sm:flex sm:w-36 sm:shrink-0 sm:flex-col
                   sm:items-end sm:justify-center sm:border-l sm:border-t-0 sm:px-5
                   {{ $isPenuh
                        ? 'border-danger-100 bg-danger-50/50 dark:border-danger-900 dark:bg-danger-950/20'
                        : 'border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950/30'
                   }}">

                <div class="flex items-center justify-between sm:block sm:text-right">

                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                            Kapasitas
                        </div>

                        <div class="mt-0.5 text-base font-black text-gray-900 dark:text-white sm:text-lg">
                            {{ $jadwal->isi_kelas ?? 0 }}
                            <span class="text-xs font-medium text-gray-400">
                                / {{ $jadwal->kuota_kelas ?? 0 }}
                            </span>
                        </div>
                    </div>

                    <div class="sm:mt-2">

                        @if($isPenuh)

                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-danger-100 px-2.5 py-1
                                   text-[10px] font-bold uppercase tracking-wide text-danger-700
                                   dark:bg-danger-900/40 dark:text-danger-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-danger-500"></span>
                            Penuh
                        </span>

                        @else

                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1
                                   text-[10px] font-bold text-success-700
                                   dark:bg-success-950/30 dark:text-success-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                            Sisa {{ $sisaKuota }} kursi
                        </span>

                        @endif

                    </div>

                </div>

            </div>

        </div>
    </div>