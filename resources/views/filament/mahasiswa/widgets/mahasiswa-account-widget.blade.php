<x-filament-widgets::widget>
    <x-filament::section class="shadow-sm border-slate-200 bg-white/50 backdrop-blur-sm">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
            <!-- Avatar & Profil Utama -->
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-950 text-xl font-bold text-yellow-500 shadow-md ring-4 ring-indigo-50">
                    {{ strtoupper(substr($mahasiswa->person?->nama_lengkap ?? $user->name ?? 'M', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="truncate text-lg font-extrabold text-indigo-950 dark:text-white">
                        {{ $mahasiswa->person?->nama_lengkap ?? $user->name }}
                    </h2>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                            {{ $mahasiswa->nim ?? 'NIM TIDAK DITEMUKAN' }}
                        </span>
                        <span class="text-slate-300">•</span>
                        <span class="text-sm text-slate-600 font-medium truncate">
                            {{ $mahasiswa->prodi?->nama_prodi ?? 'Prodi Belum Diset' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Separator --}}
            <div class="hidden h-12 w-px bg-slate-200 lg:block"></div>

            {{-- Grid Info Akademik --}}
            <div class="flex-1 grid grid-cols-2 md:grid-cols-3 gap-6">
                @if($kelasAktif)
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Kelas</div>
                    <div class="mt-1 font-bold text-indigo-950">{{ $kelasAktif->nama_kelas }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Angkatan</div>
                    <div class="mt-1 font-bold text-indigo-950">{{ $kelasAktif->angkatan_id }}</div>
                </div>
                @endif

                @if($mahasiswa->kurikulum)
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Kurikulum</div>
                    <div class="mt-1 font-bold text-indigo-950 truncate">{{ $mahasiswa->kurikulum->nama_kurikulum }}</div>
                </div>
                @endif
            </div>

            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100 min-w-[200px]">
                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Dosen Wali</div>
                <div class="text-sm font-bold text-indigo-900 leading-snug">
                    {{ $dosenWali?->nama_dengan_gelar ?? 'Belum ditentukan' }}
                </div>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>