<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="text-base font-semibold mb-4">
                    {{ $record->mataKuliah->nama_mk }} — {{ $record->kelas->nama_kelas }}
                </h3>

                @if ($sesiAktif)
                    <div class="text-center">
                        <p class="text-sm text-gray-500 mb-2">
                            Pertemuan ke-{{ $sesiAktif->pertemuan_ke }} · Sedang Berlangsung
                        </p>

                        <div
                            x-data="{ token: @entangle('sesiAktif.token_sesi') }"
                            x-init="
                                const render = () => new QRious({ element: $refs.canvas, value: token, size: 220 });
                                render();
                                $watch('token', () => render());
                            "
                            wire:poll.15s="regenerateToken"
                        >
                            <canvas x-ref="canvas"></canvas>
                            <p class="mt-2 font-mono text-lg tracking-widest">{{ $sesiAktif->token_sesi }}</p>
                        </div>

                        <p class="text-xs text-gray-400 mt-2">Kode berganti otomatis setiap 15 detik.</p>

                        <x-filament::button color="danger" class="mt-4 w-full" wire:click="tutupSesi">
                            Tutup Sesi
                        </x-filament::button>
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-sm text-gray-500 mb-4">Belum ada sesi yang dibuka hari ini.</p>
                        <x-filament::button wire:click="bukaSesi">
                            Buka Sesi Sekarang
                        </x-filament::button>
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-2">
            {{ $this->table }}
        </div>
    </div>

    @pushonce('scripts')
        <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
    @endpushonce
</x-filament-panels::page>