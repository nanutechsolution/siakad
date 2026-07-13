<x-filament-panels::page>
    <div class="max-w-md mx-auto">
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div id="qr-reader" class="mb-4 rounded-lg overflow-hidden"></div>

            <p class="text-center text-sm text-gray-500 mb-4">
                Arahkan kamera ke QR Code yang ditampilkan dosen
            </p>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <p class="text-sm text-gray-500 mb-2 text-center">Atau masukkan token manual</p>
                <div class="flex gap-2">
                    <input
                        type="text"
                        wire:model="tokenInput"
                        maxlength="10"
                        placeholder="Contoh: 94YC8B"
                        class="fi-input flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-center font-mono uppercase tracking-widest"
                        style="text-transform: uppercase;" />
                    <x-filament::button wire:click="submitToken">
                        Absen
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>

    @pushonce('scripts')
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
                        Math.random().toString(36),
                    ].join('|');

                    fp = btoa(raw).substring(0, 64);
                    localStorage.setItem('siakad_device_fp', fp);
                }

                return fp;
            }

            document.addEventListener('livewire:navigated', () => {
                const component = window.Livewire.find('{{ $this->getId() }}');
                if (component) component.set('deviceFingerprint', getDeviceFingerprint());
            });

            const component = window.Livewire.find('{{ $this->getId() }}');
            if (component) component.set('deviceFingerprint', getDeviceFingerprint());
        })();
    </script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        (function() {
            const wireId = '{{ $this->getId() }}';
            let html5QrCode = null;

            function initQrScanner() {
                const readerEl = document.getElementById('qr-reader');
                if (!readerEl || html5QrCode) return;

                html5QrCode = new Html5Qrcode('qr-reader');

                html5QrCode.start({
                        facingMode: 'environment'
                    }, {
                        fps: 10,
                        qrbox: {
                            width: 220,
                            height: 220
                        }
                    },
                    (decodedText) => {
                        const component = window.Livewire.find(wireId);
                        component.set('tokenInput', decodedText.trim().toUpperCase());
                        component.call('submitToken');

                        html5QrCode.pause(true);
                        setTimeout(() => html5QrCode && html5QrCode.resume(), 3000);
                    },
                    () => {}
                ).catch(() => {
                    readerEl.innerHTML = '<p class="text-sm text-danger-600 text-center p-4">Kamera tidak tersedia. Gunakan input token manual di bawah.</p>';
                });
            }

            document.addEventListener('livewire:navigated', initQrScanner);
            document.addEventListener('livewire:navigating', () => {
                if (html5QrCode) {
                    html5QrCode.stop().catch(() => {});
                    html5QrCode = null;
                }
            });

            initQrScanner();
        })();
    </script>
    @endpushonce
</x-filament-panels::page>