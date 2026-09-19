<x-filament-panels::page>

    {{-- =========================================================
        PROFILE HUB
    ========================================================== --}}
    @if ($section === 'hub')

    <div class="space-y-5">

        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-crest-700 via-crest-600 to-crest-500 p-5 text-white shadow-sm sm:p-6">

            <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-horizon-500/20 blur-2xl"></div>

            <div class="relative flex items-center gap-4">

                {{-- PHOTO --}}
                <div class="shrink-0">

                    @if ($this->photoUrl())
                    <img
                        src="{{ $this->photoUrl() }}"
                        alt="{{ $mahasiswa->person?->nama_lengkap }}"
                        class="h-20 w-20 rounded-2xl border-2 border-white/30 object-cover shadow-lg sm:h-24 sm:w-24">
                    @else
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/10 text-2xl font-semibold ring-1 ring-white/20 sm:h-24 sm:w-24">
                        {{ Str::upper(Str::substr($mahasiswa->person?->nama_lengkap ?? 'M', 0, 1)) }}
                    </div>
                    @endif

                </div>

                {{-- INFO --}}
                <div class="min-w-0 flex-1">

                    <p class="text-sm font-medium text-white/70">
                        Profil Mahasiswa
                    </p>

                    <h1 class="mt-1 truncate text-xl font-semibold sm:text-2xl">
                        {{ $mahasiswa->person?->nama_lengkap ?? '-' }}
                    </h1>

                    <p class="mt-1 truncate text-sm text-white/75">
                        {{ $mahasiswa->prodi?->nama_prodi ?? '-' }}
                    </p>

                    <div class="mt-3 flex flex-wrap gap-2">

                        <span class="rounded-full bg-white/10 px-2.5 py-1 font-mono text-xs text-white/90">
                            {{ $mahasiswa->nim }}
                        </span>

                        <span class="rounded-full bg-horizon-500 px-2.5 py-1 text-xs font-semibold text-white">
                            Mahasiswa Aktif
                        </span>

                    </div>

                </div>

            </div>

        </div>


        {{-- PROFILE COMPLETION --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-[#131B2E] sm:p-5">

            <div class="flex items-start justify-between gap-4">

                <div>
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Profil Anda
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $this->profileCompletion() }}% data profil sudah lengkap.
                    </p>
                </div>

                <div class="shrink-0 text-right">
                    <span class="text-lg font-bold text-crest-600 dark:text-horizon-400">
                        {{ $this->profileCompletion() }}%
                    </span>
                </div>

            </div>

            <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                <div
                    class="h-full rounded-full bg-gradient-to-r from-crest-600 to-horizon-500 transition-all"
                    style="width: {{ $this->profileCompletion() }}%"></div>
            </div>

            <p class="mt-3 text-xs leading-5 text-gray-500 dark:text-gray-400">
                Lengkapi data Anda agar informasi yang digunakan untuk administrasi kampus tetap akurat.
            </p>

        </div>


        {{-- PENDING IDENTITY --}}
        @if ($this->pendingIdentityCount() > 0)

        <div class="rounded-2xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-500/20 dark:bg-warning-500/10">

            <div class="flex gap-3">

                <div class="mt-0.5 shrink-0 text-warning-600 dark:text-warning-400">
                    <x-heroicon-o-clock class="h-5 w-5" />
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-warning-900 dark:text-warning-300">
                        Perubahan identitas sedang diperiksa
                    </h3>

                    <p class="mt-1 text-sm leading-5 text-warning-800 dark:text-warning-200">
                        Ada {{ $this->pendingIdentityCount() }} perubahan data identitas yang sedang menunggu pemeriksaan Admin Akademik.
                    </p>
                </div>

            </div>

        </div>

        @endif


        {{-- SECTION LIST --}}
        <div>

            <div class="mb-3 px-1">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                    Data Saya
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Pilih bagian yang ingin Anda lihat atau perbarui.
                </p>
            </div>


            <div class="space-y-3">

                @php
                $sections = [
                [
                'key' => 'akademik',
                'title' => 'Data Akademik',
                'description' => 'NIM, program studi, angkatan, dan status',
                'icon' => 'academic-cap',
                ],
                [
                'key' => 'identitas',
                'title' => 'Identitas',
                'description' => 'Nama, NIK, tanggal dan tempat lahir',
                'icon' => 'identification',
                ],
                [
                'key' => 'kontak',
                'title' => 'Kontak',
                'description' => 'Email, nomor HP, dan foto profil',
                'icon' => 'device-phone-mobile',
                ],
                [
                'key' => 'alamat',
                'title' => 'Alamat',
                'description' => 'Alamat KTP dan tempat tinggal',
                'icon' => 'home',
                ],
                [
                'key' => 'keluarga',
                'title' => 'Data Keluarga',
                'description' => 'Orang tua, wali, dan informasi keluarga',
                'icon' => 'user-group',
                ],
                ];
                @endphp


                @foreach ($sections as $item)

                @php
                $status = $this->sectionStatus($item['key']);
                @endphp

                <button
                    type="button"
                    wire:click="openSection('{{ $item['key'] }}')"
                    class="group flex w-full items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-crest-200 hover:shadow-md active:scale-[.99] dark:border-white/10 dark:bg-[#131B2E] dark:hover:border-white/20">

                    {{-- ICON --}}
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-crest-50 text-crest-600 dark:bg-crest-500/10 dark:text-crest-300">
                        <x-dynamic-component
                            :component="'heroicon-o-' . $item['icon']"
                            class="h-5 w-5" />
                    </div>


                    {{-- TEXT --}}
                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ $item['title'] }}
                            </h3>

                            @if ($status['tone'] === 'success')

                            <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-[11px] font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                <x-heroicon-m-check class="h-3 w-3" />
                                {{ $status['label'] }}
                            </span>

                            @elseif ($status['tone'] === 'warning')

                            <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-[11px] font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                                <x-heroicon-m-clock class="h-3 w-3" />
                                {{ $status['label'] }}
                            </span>

                            @else

                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-white/10 dark:text-gray-400">
                                {{ $status['label'] }}
                            </span>

                            @endif

                        </div>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $item['description'] }}
                        </p>

                    </div>


                    {{-- ARROW --}}
                    <div class="shrink-0 text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-crest-600 dark:group-hover:text-horizon-400">

                        <x-heroicon-m-chevron-right class="h-5 w-5" />

                    </div>

                </button>

                @endforeach

            </div>

        </div>


        {{-- INFORMATION --}}
        <div class="rounded-2xl border border-crest-100 bg-crest-50 p-4 dark:border-crest-500/20 dark:bg-crest-500/10">

            <div class="flex gap-3">

                <div class="mt-0.5 shrink-0 text-crest-600 dark:text-crest-300">
                    <x-heroicon-o-information-circle class="h-5 w-5" />
                </div>

                <div>

                    <h3 class="text-sm font-semibold text-crest-900 dark:text-crest-200">
                        Tentang perubahan data
                    </h3>

                    <p class="mt-1 text-sm leading-6 text-crest-800 dark:text-crest-100">
                        Nomor HP, email, alamat, dan data keluarga dapat diperbarui langsung.
                        Perubahan nama, NIK, tanggal lahir, tempat lahir, dan jenis kelamin
                        akan diperiksa terlebih dahulu oleh Admin Akademik.
                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        DETAIL SECTION
    ========================================================== --}}
    @else

    <div class="space-y-5">

        {{-- BACK --}}
        <div>

            <button
                type="button"
                wire:click="backToHub"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition hover:text-crest-600 dark:text-gray-400 dark:hover:text-horizon-400">

                <x-heroicon-m-arrow-left class="h-4 w-4" />

                Kembali ke Profil

            </button>

        </div>


        {{-- DETAIL HEADER --}}
        <div>

            <h1 class="font-display text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                {{ $this->sectionTitle() }}
            </h1>

            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500 dark:text-gray-400">
                {{ $this->sectionDescription() }}
            </p>

        </div>


        {{-- IDENTITY WARNING --}}
        @if ($section === 'identitas' && $this->pendingIdentityCount() > 0)

        <div class="rounded-2xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-500/20 dark:bg-warning-500/10">

            <div class="flex gap-3">

                <x-heroicon-o-clock class="mt-0.5 h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400" />

                <div>

                    <p class="text-sm font-semibold text-warning-900 dark:text-warning-300">
                        Menunggu pemeriksaan Admin Akademik
                    </p>

                    <p class="mt-1 text-sm leading-5 text-warning-800 dark:text-warning-200">
                        Perubahan identitas yang sudah diajukan belum dapat diajukan kembali
                        sampai pemeriksaan selesai.
                    </p>

                </div>

            </div>

        </div>

        @endif


        {{-- FORM --}}
        <form
            wire:submit="save"
            class="space-y-5">

            {{ $this->form }}


            {{-- SAVE --}}
            @if ($section !== 'akademik')

            <div class="sticky bottom-3 z-20">

                <div class="rounded-2xl border border-gray-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-white/10 dark:bg-[#131B2E]/95 sm:static sm:border-0 sm:bg-transparent sm:p-0 sm:shadow-none">

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-crest-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-crest-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto">

                        <span wire:loading.remove wire:target="save">
                            Simpan Perubahan
                        </span>

                        <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                            <x-filament::loading-indicator class="h-4 w-4" />
                            Menyimpan...
                        </span>

                    </button>

                </div>

            </div>

            @endif

        </form>

    </div>

    @endif

</x-filament-panels::page>