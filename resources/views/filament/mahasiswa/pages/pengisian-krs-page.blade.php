<x-filament-panels::page>
    @if(!$isEligible)
    <div class="p-6 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex flex-col items-center justify-center text-center space-y-4">
            <div class="p-4 bg-danger-100 rounded-full dark:bg-danger-900/50">
                <x-filament::icon
                    icon="heroicon-o-lock-closed"
                    class="w-12 h-12 text-danger-600 dark:text-danger-400" />
            </div>
            <h2 class="text-xl font-bold text-gray-950 dark:text-white">
                Akses Pengisian KRS Terkunci
            </h2>
            <p class="max-w-md text-gray-600 dark:text-gray-400">
                {{ $eligibilityMessage }}
            </p>

            @if(str_contains($eligibilityMessage, 'tunggakan') || str_contains($eligibilityMessage, 'Syarat pembayaran'))
            <x-filament::button tag="a" href="/mahasiswa/tagihan-mahasiswas" color="warning" icon="heroicon-o-banknotes">
                Selesaikan Tagihan Anda
            </x-filament::button>
            @endif
        </div>
    </div>
    @else
    <div class="mb-4 p-4 bg-primary-50 rounded-lg ring-1 ring-primary-200 dark:bg-primary-900/30 dark:ring-primary-800">
        <h3 class="text-lg font-bold text-primary-800 dark:text-primary-300">Tahun Akademik Aktif: {{ $activeTa?->nama_tahun }}</h3>
        <p class="text-sm text-primary-700 dark:text-primary-400 mt-1">
            Batas maksimal SKS Anda akan dievaluasi secara otomatis saat menyimpan KRS.
            Pastikan Anda telah berkonsultasi dengan Dosen Wali.
        </p>
    </div>

    <form wire:submit="simpanKrs" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end gap-3">
            <x-filament::button type="submit" color="primary" icon="heroicon-o-paper-airplane">
                Ajukan KRS ke Dosen Wali
            </x-filament::button>
        </div>
    </form>
    @endif
</x-filament-panels::page>