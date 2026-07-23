<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-5">
            
            <!-- Bagian Atas: Profil Mahasiswa -->
            <div class="flex items-center gap-4">
                <!-- Avatar -->
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-primary-500 text-xl font-bold text-white shadow-sm">
                    {{ strtoupper(substr($mahasiswa->person?->nama_lengkap ?? $user->name ?? 'M', 0, 1)) }}
                </div>
                
                <!-- Nama & Identitas -->
                <div class="flex-1 min-w-0">
                    <h2 class="truncate text-lg font-bold text-gray-900 dark:text-white">
                        {{ $mahasiswa->person?->nama_lengkap ?? $user->name }}
                    </h2>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-semibold text-primary-600 dark:text-primary-400">
                            {{ $mahasiswa->nim ?? 'NIM KOSONG' }}
                        </span>
                        <span>&bull;</span>
                        <span class="truncate">
                            {{ $mahasiswa->prodi?->nama_prodi ?? 'Prodi Belum Diset' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Garis Pemisah -->
            <hr class="border-gray-200 dark:border-white/10" />

            <!-- Bagian Bawah: Informasi Akademik (Grid) -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                @if($kelasAktif)
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kelas</span>
                        <span class="mt-1 font-medium text-gray-900 dark:text-white truncate">{{ $kelasAktif->nama_kelas }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Angkatan</span>
                        <span class="mt-1 font-medium text-gray-900 dark:text-white truncate">{{ $kelasAktif->angkatan_id }}</span>
                    </div>
                @endif

                @if($mahasiswa->kurikulum)
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kurikulum</span>
                        <span class="mt-1 font-medium text-gray-900 dark:text-white truncate">{{ $mahasiswa->kurikulum->nama_kurikulum }}</span>
                    </div>
                @endif

                <div class="flex flex-col">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Dosen Wali</span>
                    <span class="mt-1 font-medium text-gray-900 dark:text-white line-clamp-2" title="{{ $dosenWali?->nama_dengan_gelar }}">
                        {{ $dosenWali?->nama_dengan_gelar ?? 'Belum ditentukan' }}
                    </span>
                </div>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>