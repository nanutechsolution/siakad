<x-filament-widgets::widget>
    <x-filament::section>
        @if($pendingCount > 0)
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-danger-500/10 rounded-lg">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-danger-500" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Evaluasi Dosen Menunggu
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Anda memiliki <span class="font-bold text-danger-600">{{ $pendingCount }}</span> mata kuliah yang belum dievaluasi.
                    </p>
                </div>
            </div>
            <x-filament::button
                href="/mahasiswa/daftar-edom"
                color="danger"
                tag="a"
                icon="heroicon-m-pencil-square">
                Isi Sekarang
            </x-filament::button>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>