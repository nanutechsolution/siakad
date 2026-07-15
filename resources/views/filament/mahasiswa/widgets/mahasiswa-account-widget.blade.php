<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-6 md:flex-row md:items-center">

            {{-- Avatar --}}
            <div class="flex justify-center md:justify-start">
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-600 text-xl font-bold text-white shadow-sm">
                    {{ strtoupper(substr($mahasiswa->person?->nama_lengkap ?? $user->name ?? 'M', 0, 1)) }}
                </div>
            </div>

            {{-- Informasi --}}
            <div class="flex-1 min-w-0 text-center md:text-left">

                <h2 class="truncate text-xl font-bold text-gray-900 dark:text-white">
                    {{ $mahasiswa->person?->nama_lengkap ?? $user->name }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $mahasiswa->nim }}
                    <span class="mx-2">•</span>
                    {{ $mahasiswa->prodi?->nama_prodi }}
                </p>

                <div class="mt-4 flex flex-wrap justify-center gap-5 md:justify-start">

                    @if($kelasAktif)
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-400">
                            Kelas
                        </div>

                        <div class="font-semibold">
                            {{ $kelasAktif->nama_kelas }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-400">
                            Angkatan
                        </div>

                        <div class="font-semibold">
                            {{ $kelasAktif->angkatan_id }}
                        </div>
                    </div>
                    @endif

                    @if($mahasiswa->kurikulum)
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-400">
                            Kurikulum
                        </div>

                        <div class="font-semibold">
                            {{ $mahasiswa->kurikulum->nama_kurikulum }}
                        </div>
                    </div>
                    @endif

                </div>

            </div>

            {{-- Divider --}}
            <div class="hidden h-14 w-px bg-gray-200 dark:bg-white/10 lg:block"></div>

            {{-- Dosen Wali --}}
            <div class="text-center md:text-right min-w-[220px]">

                <div class="text-xs uppercase tracking-wider text-gray-400">
                    Dosen Wali
                </div>

                <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $dosenWali?->nama_dengan_gelar ?? 'Belum ditentukan' }}
                </div>

            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>