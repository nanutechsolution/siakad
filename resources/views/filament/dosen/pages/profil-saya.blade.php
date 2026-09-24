<x-filament-panels::page>
    <div class="space-y-4 sm:space-y-6">
        {{-- Header: identitas singkat, terbaca baik di mobile maupun desktop --}}
        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-4 dark:border-primary-500/20 dark:bg-primary-500/10 sm:p-6">
            <div class="flex items-start gap-3">
                @if ($dosen->person?->photo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($dosen->person->photo_path) }}"
                         alt="Foto profil"
                         class="h-14 w-14 shrink-0 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 sm:h-16 sm:w-16">
                @else
                    <div class="shrink-0 rounded-full bg-primary-600 p-3 text-white">
                        <x-heroicon-o-user-circle class="h-8 w-8" />
                    </div>
                @endif
                <div class="min-w-0">
                    <h2 class="truncate text-lg font-bold text-gray-900 dark:text-white sm:text-xl">
                        {{ $dosen->person?->nama_dengan_gelar ?? $dosen->person?->nama_lengkap ?? 'Profil Dosen' }}
                    </h2>
                    <p class="mt-0.5 truncate text-sm text-gray-600 dark:text-gray-300">
                        {{ $dosen->prodi?->nama_prodi ?? 'Program Studi belum diatur' }}
                        @if ($dosen->nidn)
                            <span class="ml-1 rounded bg-white/70 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">NIDN {{ $dosen->nidn }}</span>
                        @endif
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Kelola kontak, profil akademik, riwayat pendidikan, dan dokumen.
                    </p>
                </div>
            </div>
        </div>

        {{-- Ringkasan kinerja: 2 kolom di layar kecil, 3 di tablet, 6 di desktop --}}
        <x-filament::section>
            <x-slot name="heading">Ringkasan Kinerja</x-slot>
            <x-slot name="description">{{ $stats['tahun_aktif'] }}</x-slot>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @php
                    $cards = [
                        ['Kelas Diampu', $stats['jumlah_kelas_aktif'], 'heroicon-o-academic-cap', 'text-primary-600'],
                        ['Mahasiswa Wali', $stats['jumlah_mahasiswa_wali'], 'heroicon-o-users', 'text-success-600'],
                        ['Riset sbg Ketua', $stats['jumlah_penelitian_ketua'], 'heroicon-o-beaker', 'text-info-600'],
                        ['Riset sbg Anggota', $stats['jumlah_penelitian_anggota'], 'heroicon-o-user-group', 'text-gray-500'],
                        ['Total Luaran', $stats['total_luaran'], 'heroicon-o-document-chart-bar', 'text-warning-600'],
                        ['Skor EDOM', $stats['skor_edom'] ?? '-', 'heroicon-o-star', 'text-success-600'],
                    ];
                @endphp

                @foreach ($cards as [$label, $value, $icon, $color])
                    <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                        <x-filament::icon :icon="$icon" class="{{ $color }} mb-2 h-5 w-5" />
                        <p class="text-xs leading-tight text-gray-500 dark:text-gray-400">{{ $label }}</p>
                        <p class="mt-1 text-xl font-bold tabular-nums text-gray-900 dark:text-white">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            @if ($stats['luaran_per_tahun']->isNotEmpty())
                <div class="mt-5 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                        Luaran terverifikasi per tahun (5 tahun terakhir)
                    </p>
                    <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
                        @foreach ($stats['luaran_per_tahun'] as $row)
                            <div class="min-w-[70px] shrink-0 rounded-lg border border-gray-200 px-3 py-2 text-center dark:border-gray-700">
                                <p class="text-xs text-gray-500">{{ $row->tahun_terbit }}</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $row->jumlah }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (filled($stats['jabatan_aktif']))
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($stats['jabatan_aktif'] as $jabatan)
                        <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                            <x-heroicon-o-briefcase class="h-3.5 w-3.5" /> {{ $jabatan }}
                        </span>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Form profil --}}
        <form wire:submit="save" class="space-y-4 sm:space-y-6">
            {{ $this->form }}

            {{-- Desktop: tombol biasa. Mobile: baris menempel di bawah layar agar mudah dijangkau jempol. --}}
            <div class="sticky bottom-0 z-10 -mx-4 border-t border-gray-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-gray-700 dark:bg-gray-900/95 sm:static sm:mx-0 sm:border-0 sm:bg-transparent sm:px-0 sm:py-0 sm:backdrop-blur-none">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <x-filament::button type="submit" icon="heroicon-o-check" class="w-full sm:w-auto">
                        Simpan Perubahan
                    </x-filament::button>
                    <p class="text-center text-xs text-gray-500 sm:text-left">
                        Perubahan identitas resmi akan menunggu verifikasi admin akademik.
                    </p>
                </div>
            </div>
        </form>
    </div>
</x-filament-panels::page>
