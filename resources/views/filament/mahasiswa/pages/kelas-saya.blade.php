<x-filament-panels::page>

    @php
        $keanggotaan = $this->keanggotaanAktif;
    @endphp

    @if ($keanggotaan->isEmpty())
        {{-- EMPTY STATE MODERN --}}
        <div class="relative overflow-hidden rounded-3xl border border-gray-200/80 bg-white/60 p-10 text-center shadow-sm backdrop-blur-xl dark:border-gray-800/80 dark:bg-gray-900/60">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200/60 shadow-inner dark:from-gray-800 dark:to-gray-800/40">
                <x-filament::icon
                    icon="heroicon-o-user-group"
                    class="h-10 w-10 text-gray-400 dark:text-gray-500" />
            </div>

            <h3 class="mt-5 text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                Belum Memiliki Kelas Aktif
            </h3>

            <p class="mx-auto mt-2 max-w-md text-sm text-gray-500 dark:text-gray-400">
                Anda belum terdaftar pada kelas akademik semester ini. Silakan hubungi admin akademik program studi.
            </p>
        </div>
    @else

        @foreach($keanggotaan as $anggota)
            @php
                $kelas = $anggota->kelas;
                $dosenWali = $this->dosenWali();
                $teman = $kelas ? $this->temanSekelas($kelas->id) : collect();
            @endphp

            @if($kelas)
                <div class="space-y-6">
                    {{-- HEADER KELAS & STATS --}}
                    <div class="relative overflow-hidden rounded-3xl border border-gray-200/60 bg-gradient-to-br from-white via-horizon-50/20 to-white p-6 shadow-sm backdrop-blur-xl dark:border-gray-800/60 dark:from-gray-900 dark:via-gray-900/80 dark:to-gray-950 sm:p-8">
                        {{-- Ambient Glow Light --}}
                        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-horizon-500/10 blur-3xl"></div>
                        <div class="absolute -bottom-16 -left-16 h-64 w-64 rounded-full bg-crest-500/10 blur-3xl"></div>

                        <div class="relative">
                            {{-- Class Info Header --}}
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-crest-600 to-horizon-500 text-white shadow-lg shadow-crest-500/20 ring-4 ring-white/80 dark:ring-gray-900/80">
                                        <x-filament::icon
                                            icon="heroicon-o-academic-cap"
                                            class="h-8 w-8" />
                                    </div>

                                    <div class="min-w-0">
                                        <h1 class="truncate font-display text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                                           Kelas: {{ $kelas->nama_kelas }}
                                        </h1>

                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                            <span class="truncate">{{ $kelas->prodi?->nama_prodi ?? '-' }}</span>
                                            <span class="inline-block h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                                            <span class="truncate">{{ $kelas->program?->nama_program ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- STAT CARDS GRID --}}
                            <div class="mt-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                                <div class="rounded-2xl border border-gray-100 bg-white/80 p-4 shadow-sm backdrop-blur-md transition hover:border-gray-200 dark:border-gray-800/80 dark:bg-gray-800/40">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        Angkatan
                                    </p>
                                    <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $kelas->angkatan_id }}
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-gray-100 bg-white/80 p-4 shadow-sm backdrop-blur-md transition hover:border-gray-200 dark:border-gray-800/80 dark:bg-gray-800/40">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        Kapasitas
                                    </p>
                                    <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $kelas->kapasitas ?? '-' }} <span class="text-xs font-normal text-gray-400">Mahasiswa</span>
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-gray-100 bg-white/80 p-4 shadow-sm backdrop-blur-md transition hover:border-gray-200 dark:border-gray-800/80 dark:bg-gray-800/40">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        Jumlah Anggota
                                    </p>
                                    <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $teman->count() + 1 }} <span class="text-xs font-normal text-gray-400">Orang</span>
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-gray-100 bg-white/80 p-4 shadow-sm backdrop-blur-md transition hover:border-gray-200 dark:border-gray-800/80 dark:bg-gray-800/40">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        Bergabung
                                    </p>
                                    <p class="mt-1 text-base font-bold text-gray-900 dark:text-white">
                                        {{ $anggota->tanggal_masuk?->translatedFormat('d M Y') ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DOSEN WALI & TEMAN SEKELAS CONTAINER --}}
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {{-- DOSEN WALI --}}
                        <div class="rounded-3xl border border-gray-200/60 bg-white p-6 shadow-sm dark:border-gray-800/60 dark:bg-gray-900 lg:col-span-1">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                                Dosen Wali / PA
                            </h2>

                            <div class="mt-4 flex items-center gap-4 rounded-2xl border border-gray-100 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-crest-500 to-horizon-500 font-bold text-white shadow-md">
                                    {{ strtoupper(substr(data_get($dosenWali, 'person.nama_dengan_gelar', 'D'), 0, 1)) }}
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-900 dark:text-white">
                                        {{ data_get($dosenWali, 'dosen.person.nama_dengan_gelar', 'Belum ditentukan') }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        Pembimbing Akademik
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- TEMAN SEKELAS --}}
                        <div class="rounded-3xl border border-gray-200/60 bg-white p-6 shadow-sm dark:border-gray-800/60 dark:bg-gray-900 lg:col-span-2">
                            <div class="flex items-center justify-between">
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">
                                    Teman Sekelas
                                </h2>

                                <span class="rounded-full bg-crest-500/10 px-3 py-1 text-xs font-semibold text-crest-600 dark:bg-crest-500/20 dark:text-crest-400">
                                    {{ $teman->count() + 1 }} Mahasiswa
                                </span>
                            </div>

                            @if($teman->isEmpty())
                                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada teman sekelas.
                                </p>
                            @else
                                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @foreach($teman as $mhs)
                                        <div class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50/60 p-3 transition hover:border-gray-200 dark:border-gray-800/60 dark:bg-gray-800/30 dark:hover:border-gray-700">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-200/70 font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                {{ strtoupper(substr($mhs->person?->nama_lengkap ?? $mhs->nim, 0, 1)) }}
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $mhs->person?->nama_lengkap ?? $mhs->nim }}
                                                </p>
                                                <p class="font-mono text-xs text-gray-400 dark:text-gray-500">
                                                    {{ $mhs->nim }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

    @endif

</x-filament-panels::page>