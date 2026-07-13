@php
$sisaKuota = $jadwal->kuota_kelas - $jadwal->isi_kelas;
$namaRuang = $jadwal->ruang->nama_ruang ?? 'Belum ditentukan';
$namaKelas = $jadwal->kelas->nama_kelas ?? '-';
$kodeMk = $jadwal->mataKuliah->kode_mk ?? '-';
$sks = $jadwal->mataKuliah->sks_default ?? 0;

$jamMulai = substr($jadwal->jam_mulai, 0, 5);
$jamSelesai = substr($jadwal->jam_selesai, 0, 5);

// Format nama dosen
$namaDosens = $jadwal->dosenPengajars
->map(function ($dp) {
$person = $dp->dosen?->person;
return $person?->nama_dengan_gelar ?? $person?->nama_lengkap;
})
->filter();

$isPenuh = $sisaKuota <= 0;

    // AMBIL SEMESTER PAKET & SIFAT MK
    $semesterPaket='-' ;
    $sifatMk='W' ; // Default Wajib

    // Gunakan kurikulum dari jadwal (jika ada), jika tidak ada gunakan kurikulum mahasiswa
    $kurikulumIdAktif=$jadwal->kurikulum_id ?? $mahasiswaKurikulumId ?? null;

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

    // Konversi Kode Sifat ke Teks
    $teksSifatMk = $sifatMk === 'W' ? 'Wajib' : ($sifatMk === 'P' ? 'Pilihan' : 'MK Lainnya');
    @endphp

    <!-- Card Wrapper (Flex col on mobile, row on desktop) -->
    <div class="w-full flex flex-col sm:flex-row gap-4 p-4 mb-2 transition-all duration-300 bg-white border rounded-2xl shadow-sm cursor-pointer group 
    {{ $isPenuh ? 'border-danger-200 bg-danger-50/20 opacity-75 grayscale-[20%]' : 'border-gray-200 hover:border-primary-400 hover:shadow-md hover:bg-primary-50/30 dark:bg-gray-900 dark:border-gray-800 dark:hover:border-primary-500' }}">

        <!-- Bagian Kiri / Atas: Informasi Mata Kuliah -->
        <div class="flex-1 w-full">
            <!-- Baris Badges -->
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-extrabold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 tracking-wider border border-gray-200 dark:border-gray-700">
                    {{ $kodeMk }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-400 border border-primary-200 dark:border-primary-800">
                    {{ $sks }} SKS
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-info-100 text-info-700 dark:bg-info-900/50 dark:text-info-400 border border-info-200 dark:border-info-800">
                    Semester {{ $semesterPaket }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold border 
    {{ $sifatMk === 'W' ? 'bg-success-50 text-success-700 border-success-200 dark:bg-success-900/30 dark:text-success-400 dark:border-success-800' : 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800' }}">
                    {{ $teksSifatMk }}
                </span>

                @if($isLintasKelas)
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-400 border border-warning-200 dark:border-warning-800">
                    <x-heroicon-s-arrow-path class="w-3 h-3 mr-1" /> Lintas Kelas
                </span>
                @endif
            </div>

            <!-- Judul Mata Kuliah -->
            <h4 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white leading-tight mb-4 group-hover:text-primary-600 transition-colors">
                {{ $jadwal->mataKuliah->nama_mk }}
            </h4>

            <!-- Grid Detail (Mobile: 1 kolom, Sm: 2 kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                <!-- Item Detail dengan Ikon membulat -->
                <div class="flex items-center gap-2.5">
                    <div class="flex flex-shrink-0 items-center justify-center w-7 h-7 rounded-full bg-gray-50 border border-gray-100 dark:bg-gray-800 dark:border-gray-700 text-gray-500">
                        <x-heroicon-s-academic-cap class="w-4 h-4" />
                    </div>
                    <span class="truncate">Kelas <strong class="text-gray-900 dark:text-gray-200">{{ $namaKelas }}</strong></span>
                </div>

                <div class="flex items-center gap-2.5">
                    <div class="flex flex-shrink-0 items-center justify-center w-7 h-7 rounded-full bg-gray-50 border border-gray-100 dark:bg-gray-800 dark:border-gray-700 text-gray-500">
                        <x-heroicon-s-clock class="w-4 h-4" />
                    </div>
                    <span class="truncate">{{ $jadwal->hari }}, <span class="font-bold text-gray-800 dark:text-gray-300">{{ $jamMulai }} - {{ $jamSelesai }}</span></span>
                </div>

                <div class="flex items-center gap-2.5">
                    <div class="flex flex-shrink-0 items-center justify-center w-7 h-7 rounded-full bg-gray-50 border border-gray-100 dark:bg-gray-800 dark:border-gray-700 text-gray-500">
                        <x-heroicon-s-map-pin class="w-4 h-4" />
                    </div>
                    <span class="truncate">Ruang <span class="font-medium text-gray-800 dark:text-gray-300">{{ $namaRuang }}</span></span>
                </div>

                <div class="flex items-start gap-2.5">
                    <div class="flex flex-shrink-0 items-center justify-center w-7 h-7 rounded-full bg-gray-50 border border-gray-100 dark:bg-gray-800 dark:border-gray-700 text-gray-500">
                        <x-heroicon-s-users class="w-4 h-4" />
                    </div>

                    <div class="flex flex-col">
                        @foreach ($namaDosens as $dosen)
                        <span class="text-sm text-gray-800 dark:text-gray-300">
                            {{ $dosen }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Divider Khusus Mobile -->
        <hr class="block sm:hidden border-gray-100 dark:border-gray-800 my-2">

        <!-- Bagian Kanan / Bawah: Sisa Kuota -->
        <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-center min-w-[130px] pt-1 sm:pt-0 sm:pl-5 sm:border-l border-gray-100 dark:border-gray-800">
            <div class="text-left sm:text-right">
                <p class="text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Kapasitas</p>
                <p class="text-sm sm:text-lg font-black {{ $isPenuh ? 'text-danger-600' : 'text-gray-900 dark:text-white' }} leading-none">
                    {{ $jadwal->isi_kelas }} <span class="text-gray-400 font-medium text-xs sm:text-sm">/ {{ $jadwal->kuota_kelas }}</span>
                </p>

                <div class="mt-2 inline-flex items-center gap-1">
                    @if($isPenuh)
                    <span class="w-2 h-2 rounded-full bg-danger-500 animate-pulse"></span>
                    <p class="text-[10px] sm:text-xs font-bold text-danger-600 dark:text-danger-400 uppercase">Penuh</p>
                    @else
                    <span class="w-2 h-2 rounded-full bg-success-500"></span>
                    <p class="text-[10px] sm:text-xs font-bold text-success-600 dark:text-success-400 uppercase">Sisa {{ $sisaKuota }} kursi</p>
                    @endif
                </div>
            </div>
        </div>
    </div>