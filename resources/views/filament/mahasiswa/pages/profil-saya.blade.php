<x-filament-panels::page>

    {{-- ================================================================
         HERO PROFILE
         ================================================================ --}}
    <div
        class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

        {{-- Background decoration --}}
        <div
            class="pointer-events-none absolute -right-20 -top-20 h-48 w-48 rounded-full bg-primary-500/10 blur-3xl"></div>

        <div class="relative p-5 sm:p-7">

            <div class="flex items-center gap-4 sm:gap-5">

                {{-- FOTO --}}
                <div class="shrink-0">

                    <div
                        class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-gradient-to-br from-crest-600 to-horizon-500 shadow-lg dark:border-gray-800 sm:h-24 sm:w-24">

                        @if ($this->mahasiswa->person->photo_path)

                        <img
                            src="{{ asset('storage/' . $this->mahasiswa->person->photo_path) }}"
                            alt="Foto {{ $this->mahasiswa->person->nama_lengkap }}"
                            class="h-full w-full object-cover" />

                        @else

                        <span class="text-2xl font-bold text-white sm:text-3xl">
                            {{ strtoupper(substr($this->mahasiswa->person->nama_lengkap, 0, 1)) }}
                        </span>

                        @endif

                    </div>

                </div>


                {{-- INFORMASI UTAMA --}}
                <div class="min-w-0 flex-1">

                    <h1
                        class="truncate text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                        {{ $this->mahasiswa->person->nama_lengkap }}
                    </h1>

                    <p
                        class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">
                        {{ $this->mahasiswa->prodi->nama_prodi ?? '-' }}
                    </p>

                    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">

                        <span class="font-mono font-medium text-gray-700 dark:text-gray-300">
                            {{ $this->mahasiswa->nim }}
                        </span>

                        <span class="text-gray-300 dark:text-gray-700">•</span>

                        <span>
                            Angkatan {{ $this->mahasiswa->angkatan_id }}
                        </span>

                    </div>

                    <div class="mt-3">

                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Mahasiswa Aktif
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
         STATUS PROFIL
         ================================================================ --}}
    <div
        class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5">

        <div class="flex items-start gap-3">

            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                {{ $this->profileCompletion >= 100
                    ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                    : 'bg-primary-50 text-primary-600 dark:bg-primary-950/40 dark:text-primary-400' }}">

                @if ($this->profileCompletion >= 100)

                <svg
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 13l4 4L19 7" />
                </svg>

                @else

                <svg
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 8v4l3 2" />
                    <circle
                        cx="12"
                        cy="12"
                        r="9" />
                </svg>

                @endif

            </div>


            <div class="min-w-0 flex-1">

                <div class="flex items-center justify-between gap-3">

                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            Kelengkapan Profil
                        </p>

                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{ $this->profileCompletion >= 100
                                ? 'Semua data utama sudah lengkap.'
                                : 'Lengkapi data profil Anda agar informasi tetap akurat.' }}
                        </p>
                    </div>

                    <span
                        class="shrink-0 text-sm font-bold
                        {{ $this->profileCompletion >= 100
                            ? 'text-emerald-600 dark:text-emerald-400'
                            : 'text-primary-600 dark:text-primary-400' }}">
                        {{ $this->profileCompletion }}%
                    </span>

                </div>


                {{-- PROGRESS --}}
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">

                    <div
                        class="h-full rounded-full transition-all duration-500
                        {{ $this->profileCompletion >= 100
                            ? 'bg-emerald-500'
                            : 'bg-primary-500' }}"
                        style="width: {{ $this->profileCompletion }}%"></div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
         STATUS PERUBAHAN IDENTITAS
         ================================================================ --}}
    @if ($this->pendingIdentityCount > 0)

    <div
        class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/30">

        <div class="flex items-start gap-3">

            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">

                <svg
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 8v4l3 2" />
                    <circle
                        cx="12"
                        cy="12"
                        r="9" />
                </svg>

            </div>


            <div>

                <p class="text-sm font-semibold text-amber-900 dark:text-amber-300">
                    Perubahan sedang diperiksa
                </p>

                <p class="mt-1 text-xs leading-5 text-amber-800 dark:text-amber-400">
                    Ada {{ $this->pendingIdentityCount }}
                    perubahan identitas yang sedang diperiksa oleh Admin Akademik.
                    Data akan diperbarui setelah disetujui.
                </p>

            </div>

        </div>

    </div>

    @endif


    {{-- ================================================================
         PETUNJUK SINGKAT
         ================================================================ --}}
    <div
        class="mt-4 rounded-2xl border border-primary-100 bg-primary-50/70 p-4 dark:border-primary-900/50 dark:bg-primary-950/20">

        <div class="flex items-start gap-3">

            <svg
                class="mt-0.5 h-5 w-5 shrink-0 text-primary-600 dark:text-primary-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 100-20 10 10 0 000 20z" />
            </svg>

            <div>

                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    Tentang perubahan data
                </p>

                <p class="mt-1 text-xs leading-5 text-gray-600 dark:text-gray-400">
                    Data kontak, alamat, dan keluarga dapat diperbarui langsung.
                    Perubahan nama, NIK, tanggal lahir, tempat lahir, dan jenis kelamin
                    perlu diperiksa Admin Akademik terlebih dahulu.
                </p>

            </div>

        </div>

    </div>


    {{-- ================================================================
         FORM
         ================================================================ --}}
    <form
        wire:submit="save"
        class="mt-5 pb-24 sm:mt-6 sm:pb-8">

        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            {{ $this->form }}

        </div>


        {{-- ============================================================
             SAVE BUTTON
             ============================================================ --}}
        <div
            class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200/80 bg-white/95 p-3 backdrop-blur-md dark:border-gray-800 dark:bg-gray-950/95 sm:static sm:mt-6 sm:border-0 sm:bg-transparent sm:p-0 sm:backdrop-blur-none">

            <div class="mx-auto max-w-screen-xl sm:flex sm:justify-end">

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-crest-600 to-crest-500 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-crest-500/20 transition hover:from-crest-700 hover:to-crest-600 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto sm:px-7">

                    {{-- NORMAL --}}
                    <span
                        wire:loading.remove
                        wire:target="save"
                        class="flex items-center gap-2">

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 13l4 4L19 7" />
                        </svg>

                        Simpan Perubahan

                    </span>


                    {{-- LOADING --}}
                    <span
                        wire:loading
                        wire:target="save"
                        class="flex items-center gap-2">

                        <svg
                            class="h-5 w-5 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24">
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4" />

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>

                        Menyimpan...

                    </span>

                </button>

            </div>

        </div>

    </form>

</x-filament-panels::page>