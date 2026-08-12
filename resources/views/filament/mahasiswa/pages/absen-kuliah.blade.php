<x-filament-panels::page>
    <div class="mx-auto w-full max-w-md px-4 py-6">

        {{-- Header --}}
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                <x-heroicon-o-qr-code class="h-7 w-7" />
            </div>

            <h1 class="text-xl font-bold text-gray-950 dark:text-white">
                Scan Absensi
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Scan QR Code yang ditampilkan oleh dosen
            </p>
        </div>

        {{-- Main Card --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- QR Scanner --}}
            <div class="p-4 sm:p-6">

                <div class="relative overflow-hidden rounded-xl bg-gray-950">

                    {{-- Scanner --}}
                    <div
                        id="qr-reader"
                        class="min-h-[280px] w-full"></div>

                    {{-- Scanner Overlay --}}
                    <div
                        id="qr-overlay"
                        class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div class="relative h-56 w-56">
                            {{-- Corner --}}
                            <div class="absolute left-0 top-0 h-8 w-8 rounded-tl-xl border-l-4 border-t-4 border-primary-400"></div>
                            <div class="absolute right-0 top-0 h-8 w-8 rounded-tr-xl border-r-4 border-t-4 border-primary-400"></div>
                            <div class="absolute bottom-0 left-0 h-8 w-8 rounded-bl-xl border-b-4 border-l-4 border-primary-400"></div>
                            <div class="absolute bottom-0 right-0 h-8 w-8 rounded-br-xl border-b-4 border-r-4 border-primary-400"></div>

                            {{-- Scan Line --}}
                            <div class="absolute left-2 right-2 top-1/2 h-0.5 animate-pulse bg-primary-400"></div>
                        </div>
                    </div>

                    {{-- Camera Status --}}
                    <div
                        id="camera-status"
                        class="absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-black/60 px-3 py-1.5 text-xs font-medium text-white backdrop-blur">
                        <span class="mr-1 inline-block h-2 w-2 rounded-full bg-emerald-400"></span>
                        Kamera aktif
                    </div>
                </div>

                <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">
                    Pastikan seluruh QR Code berada di dalam kotak
                </p>
            </div>

            {{-- Divider --}}
            <div class="flex items-center gap-3 px-6">
                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                <span class="text-xs font-medium text-gray-400">ATAU</span>
                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
            </div>

            {{-- Manual Token --}}
            <div class="p-4 sm:p-6">

                <div class="mb-3">
                    <h2 class="text-sm font-semibold text-gray-950 dark:text-white">
                        Masukkan Token Manual
                    </h2>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Gunakan token jika kamera tidak dapat digunakan
                    </p>
                </div>

                <div class="flex gap-2">
                    <input
                        type="text"
                        wire:model.live="tokenInput"
                        maxlength="10"
                        autocomplete="off"
                        autocapitalize="characters"
                        placeholder="Contoh: 94YC8B"
                        oninput="this.value = this.value.toUpperCase()"
                        class="fi-input min-w-0 flex-1 rounded-xl border-gray-300 text-center font-mono text-base font-semibold uppercase tracking-[0.25em] shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800" />

                    <x-filament::button
                        wire:click="submitToken"
                        wire:loading.attr="disabled"
                        wire:target="submitToken"
                        class="min-w-[90px] rounded-xl">
                        <span wire:loading.remove wire:target="submitToken">
                            Absen
                        </span>

                        <span
                            wire:loading
                            wire:target="submitToken"
                            class="flex items-center gap-2">
                            <x-filament::loading-indicator class="h-4 w-4" />
                            Proses
                        </span>
                    </x-filament::button>
                </div>

                @error('tokenInput')
                <p class="mt-2 text-xs font-medium text-danger-600">
                    {{ $message }}
                </p>
                @enderror

            </div>
        </div>

        {{-- Security Info --}}
        <div class="mt-4 flex items-start gap-3 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
            <x-heroicon-o-shield-check class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />

            <div>
                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Absensi aman
                </p>

                <p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">
                    Pastikan Anda menggunakan perangkat sendiri dan melakukan absensi sesuai instruksi dosen.
                </p>
            </div>
        </div>

        {{-- Success / Error Notification --}}
        <div
            id="scan-feedback"
            class="mt-4 hidden rounded-xl p-4 text-center"></div>

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

                    fp = btoa(unescape(encodeURIComponent(raw)))
                        .substring(0, 64);

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

    {{-- HTML5 QR Code --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        (function() {

            const wireId = '{{ $this->getId() }}';

            let html5QrCode = null;
            let isProcessing = false;

            function getComponent() {
                return window.Livewire.find(wireId);
            }

            function setCameraStatus(type, message) {
                const status = document.getElementById('camera-status');

                if (!status) return;

                const colors = {
                    active: 'bg-black/60',
                    loading: 'bg-blue-600/80',
                    error: 'bg-red-600/80',
                    success: 'bg-emerald-600/80',
                };

                status.className =
                    `absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full px-3 py-1.5 text-xs font-medium text-white backdrop-blur ${colors[type] || colors.active}`;

                const dots = {
                    active: 'bg-emerald-400',
                    loading: 'bg-blue-200 animate-pulse',
                    error: 'bg-red-200',
                    success: 'bg-emerald-200',
                };

                status.innerHTML = `
                        <span class="mr-1 inline-block h-2 w-2 rounded-full ${dots[type]}"></span>
                        ${message}
                    `;
            }

            function showFeedback(type, message) {
                const feedback =
                    document.getElementById('scan-feedback');

                if (!feedback) return;

                feedback.classList.remove(
                    'hidden',
                    'bg-emerald-50',
                    'text-emerald-700',
                    'bg-red-50',
                    'text-red-700'
                );

                if (type === 'success') {
                    feedback.classList.add(
                        'bg-emerald-50',
                        'text-emerald-700'
                    );

                    feedback.innerHTML = `
                            <div class="flex items-center justify-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414-1.414 1.414l2 2a1 1 0 001.414 0l3.707-3.707z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span class="font-semibold">${message}</span>
                            </div>
                        `;
                } else {
                    feedback.classList.add(
                        'bg-red-50',
                        'text-red-700'
                    );

                    feedback.innerHTML = `
                            <div class="flex items-center justify-center gap-2">
                                <span class="font-semibold">${message}</span>
                            </div>
                        `;
                }
            }

            async function handleQrResult(decodedText) {

                if (isProcessing) return;

                isProcessing = true;

                const token = decodedText
                    .trim()
                    .toUpperCase();

                const component = getComponent();

                if (!component) {
                    isProcessing = false;
                    return;
                }

                setCameraStatus(
                    'loading',
                    'Memproses absensi...'
                );

                try {
                    component.set('tokenInput', token);

                    await component.call('submitToken');

                    showFeedback(
                        'success',
                        'QR berhasil dipindai. Absensi sedang diproses.'
                    );

                } catch (error) {

                    showFeedback(
                        'error',
                        'QR tidak dapat diproses. Silakan coba lagi.'
                    );

                } finally {

                    if (html5QrCode) {
                        html5QrCode.pause(true);
                    }

                    setTimeout(() => {

                        isProcessing = false;

                        if (html5QrCode) {
                            html5QrCode.resume();
                            setCameraStatus(
                                'active',
                                'Kamera aktif'
                            );
                        }

                    }, 3000);
                }
            }

            async function initQrScanner() {

                const readerEl =
                    document.getElementById('qr-reader');

                if (!readerEl || html5QrCode) {
                    return;
                }

                if (typeof Html5Qrcode === 'undefined') {
                    setCameraStatus(
                        'error',
                        'Scanner tidak tersedia'
                    );

                    return;
                }

                html5QrCode =
                    new Html5Qrcode('qr-reader');

                try {

                    await html5QrCode.start({
                            facingMode: 'environment'
                        }, {
                            fps: 10,
                            qrbox: {
                                width: 220,
                                height: 220
                            },
                            aspectRatio: 1
                        },
                        handleQrResult,
                        () => {}
                    );

                    setCameraStatus(
                        'active',
                        'Kamera aktif'
                    );

                } catch (error) {

                    setCameraStatus(
                        'error',
                        'Kamera tidak tersedia'
                    );

                    readerEl.innerHTML = `
                            <div class="flex min-h-[280px] flex-col items-center justify-center px-6 text-center">
                                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10 text-red-500">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3l18 18M10.5 10.5a2 2 0 102.8 2.8M6.6 6.6A8 8 0 0118 12M17.4 17.4A8 8 0 016 12"/>
                                    </svg>
                                </div>

                                <p class="text-sm font-medium text-white">
                                    Kamera tidak tersedia
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Gunakan token manual di bawah untuk melakukan absensi.
                                </p>
                            </div>
                        `;
                }
            }

            document.addEventListener(
                'livewire:navigated',
                initQrScanner
            );

            document.addEventListener(
                'livewire:navigating',
                () => {

                    if (html5QrCode) {
                        html5QrCode
                            .stop()
                            .catch(() => {});

                        html5QrCode = null;
                    }

                    isProcessing = false;
                }
            );

            initQrScanner();

        })();
    </script>

    @endpushonce
</x-filament-panels::page>