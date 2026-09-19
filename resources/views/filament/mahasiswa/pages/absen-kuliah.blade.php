<x-filament-panels::page>
    <div class="mx-auto w-full max-w-md px-4 py-6 sm:px-6">

        {{-- Header --}}
        <div class="mb-6 text-center">
            <div
                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl
                       bg-primary-50 text-primary-600
                       dark:bg-primary-500/10 dark:text-primary-400">
                <x-heroicon-o-key class="h-8 w-8" />
            </div>

            <h1 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                Presensi Kuliah
            </h1>

            <p class="mx-auto mt-2 max-w-xs text-sm leading-5 text-gray-500 dark:text-gray-400">
                Masukkan token presensi yang diberikan oleh dosen untuk mencatat kehadiran Anda.
            </p>
        </div>


        {{-- Main Card --}}
        <div
            class="overflow-hidden rounded-2xl bg-white shadow-sm
                   ring-1 ring-gray-950/5
                   dark:bg-gray-900 dark:ring-white/10">

            {{-- Token Form --}}
            <div class="p-5 sm:p-6">

                <div class="mb-5">
                    <div class="mb-1 flex items-center gap-2">
                        <x-heroicon-o-key class="h-4 w-4 text-primary-600 dark:text-primary-400" />

                        <h2 class="text-sm font-semibold text-gray-950 dark:text-white">
                            Token Presensi
                        </h2>
                    </div>

                    <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">
                        Masukkan kode yang sedang ditampilkan oleh dosen.
                    </p>
                </div>


                {{-- Token Input --}}
                <div>
                    <label
                        for="tokenInput"
                        class="sr-only">
                        Token Presensi
                    </label>

                    <input
                        id="tokenInput"
                        type="text"
                        wire:model.live="tokenInput"
                        maxlength="10"
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
                        inputmode="text"
                        placeholder="MASUKKAN TOKEN"
                        oninput="this.value = this.value.toUpperCase()"
                        class="block w-full rounded-xl border-gray-300 bg-gray-50
                               px-4 py-4 text-center font-mono text-xl font-bold
                               uppercase tracking-[0.3em] text-gray-950
                               shadow-sm transition
                               placeholder:font-sans placeholder:text-sm
                               placeholder:font-medium placeholder:tracking-normal
                               placeholder:text-gray-400
                               focus:border-primary-500 focus:bg-white
                               focus:ring-primary-500
                               dark:border-gray-700 dark:bg-gray-800
                               dark:text-white dark:placeholder:text-gray-500
                               dark:focus:bg-gray-800" />

                    @error('tokenInput')
                    <p class="mt-2 text-xs font-medium text-danger-600">
                        {{ $message }}
                    </p>
                    @enderror
                </div>


                {{-- Submit Button --}}
                <div class="mt-4">
                    <x-filament::button
                        wire:click="submitToken"
                        wire:loading.attr="disabled"
                        wire:target="submitToken"
                        type="button"
                        icon="heroicon-m-check-circle"
                        class="w-full justify-center rounded-xl py-3 text-sm font-semibold">
                        <span wire:loading.remove wire:target="submitToken">
                            Absen Sekarang
                        </span>

                        <span
                            wire:loading
                            wire:target="submitToken"
                            class="flex items-center justify-center gap-2">
                            <x-filament::loading-indicator class="h-4 w-4" />
                            Memproses...
                        </span>
                    </x-filament::button>
                </div>


                {{-- Helper --}}
                <div class="mt-4 flex items-start gap-2 rounded-xl bg-primary-50 p-3.5
                            dark:bg-primary-500/10">
                    <x-heroicon-o-information-circle
                        class="mt-0.5 h-4 w-4 shrink-0 text-primary-600 dark:text-primary-400" />

                    <p class="text-xs leading-5 text-primary-700 dark:text-primary-300">
                        Pastikan token sesuai dengan yang diberikan dosen sebelum menekan
                        <strong>Absen Sekarang</strong>.
                    </p>
                </div>

            </div>
        </div>


        {{-- Security Info --}}
        <div
            class="mt-4 flex items-start gap-3 rounded-2xl bg-gray-50 p-4
                   ring-1 ring-gray-950/5
                   dark:bg-gray-800/50 dark:ring-white/5">
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                       bg-emerald-100 text-emerald-600
                       dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-heroicon-o-shield-check class="h-5 w-5" />
            </div>

            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                    Presensi aman
                </p>

                <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">
                    Gunakan perangkat Anda sendiri dan lakukan presensi sesuai
                    instruksi dosen.
                </p>
            </div>
        </div>


        {{-- Instruction --}}
        <div class="mt-5 text-center">
            <p class="text-[11px] leading-4 text-gray-400 dark:text-gray-500">
                Jangan membagikan token presensi kepada mahasiswa lain.
            </p>
        </div>

    </div>


    @pushonce('scripts')

    {{-- Device Fingerprint --}}
    <script>
        (function() {
            function getDeviceFingerprint() {
                let fp = localStorage.getItem('siakad_device_fp');

                if (!fp) {
                    const raw = [
                        navigator.userAgent,
                        navigator.language,
                        screen.width + 'x' + screen.height,
                        Intl.DateTimeFormat().resolvedOptions().timeZone,
                    ].join('|');

                    fp = btoa(
                        unescape(
                            encodeURIComponent(raw)
                        )
                    ).substring(0, 64);

                    localStorage.setItem('siakad_device_fp', fp);
                }

                return fp;
            }

            function setFingerprint() {
                const component = window.Livewire.find(
                    '{{ $this->getId() }}'
                );

                if (component) {
                    component.set(
                        'deviceFingerprint',
                        getDeviceFingerprint()
                    );
                }
            }

            document.addEventListener(
                'livewire:navigated',
                setFingerprint
            );

            setFingerprint();
        })();
    </script>

    @endpushonce

</x-filament-panels::page>