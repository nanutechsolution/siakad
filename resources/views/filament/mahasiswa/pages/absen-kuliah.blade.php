<x-filament-panels::page>
    <div class="mx-auto w-full max-w-md px-4 py-4 sm:px-6 sm:py-6">

        {{-- Main Card --}}
        <div
            class="overflow-hidden rounded-2xl bg-white
                   shadow-sm ring-1 ring-gray-950/5
                   dark:bg-gray-900 dark:ring-white/10">
            <div class="p-5 sm:p-6">

            <form wire:submit="submitToken">
                {{-- Section Label --}}
                <div class="mb-4">
                    <label
                        for="tokenInput"
                        class="block text-sm font-semibold text-gray-950 dark:text-white">
                        Token Presensi
                    </label>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Masukkan token yang ditampilkan dosen.
                    </p>
                </div>


                {{-- Token Input --}}                <div>
                    <input
                        id="tokenInput"
                        type="text"
                        wire:model="tokenInput"
                        maxlength="10"
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
                        inputmode="text"
                        placeholder="MASUKKAN TOKEN"
                        oninput="this.value = this.value.toUpperCase()"
                        class="block w-full rounded-xl border-gray-300 bg-gray-50
                               px-4 py-4 text-center font-mono text-xl font-bold
                               uppercase tracking-[0.25em] text-gray-950
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


                {{-- Submit --}}
                <div class="mt-4">
                    <x-filament::button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="submitToken"
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
            </form>


                {{-- Security Hint --}}
                <div
                    class="mt-4 flex items-start gap-2.5 rounded-xl
                           bg-primary-50 px-3.5 py-3
                           dark:bg-primary-500/10">
                    <x-heroicon-o-information-circle
                        class="mt-0.5 h-4 w-4 shrink-0
                               text-primary-600 dark:text-primary-400" />

                    <p class="text-xs leading-5 text-primary-700 dark:text-primary-300">
                        Token bersifat rahasia. Jangan membagikannya kepada mahasiswa lain.
                    </p>
                </div>

            </div>
        </div>


        {{-- Device Security --}}
        <div
            class="mt-4 flex items-center gap-3 rounded-2xl
                   bg-gray-50 px-4 py-3.5
                   ring-1 ring-gray-950/5
                   dark:bg-gray-800/50 dark:ring-white/5">
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center
                       rounded-xl bg-emerald-100 text-emerald-600
                       dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-heroicon-o-shield-check class="h-5 w-5" />
            </div>

            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                    Presensi aman
                </p>

                <p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">
                    Gunakan perangkat Anda sendiri.
                </p>
            </div>
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