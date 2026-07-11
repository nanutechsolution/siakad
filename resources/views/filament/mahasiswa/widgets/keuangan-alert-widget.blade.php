<x-filament-widgets::widget>
    @if($hasTunggakan)
        <div class="p-4 rounded-xl bg-danger-50 ring-1 ring-danger-200 dark:bg-danger-900/30 dark:ring-danger-800">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <x-filament::icon
                        icon="heroicon-o-exclamation-triangle"
                        class="w-6 h-6 text-danger-600 dark:text-danger-400"
                    />
                </div>
                <div class="flex-1 text-sm text-danger-800 dark:text-danger-400">
                    <h3 class="text-base font-bold text-danger-900 dark:text-danger-300">
                        Peringatan: Terdapat Tunggakan Keuangan
                    </h3>
                    <p class="mt-1">
                        Anda memiliki tagihan yang belum dilunasi sebesar 
                        <strong class="font-bold">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</strong>. 
                        Tunggakan ini dapat memblokir akses pengisian Kartu Rencana Studi (KRS) Anda.
                    </p>
                    <div class="mt-4">
                        <x-filament::button 
                            color="danger" 
                            tag="a" 
                            href="/mahasiswa/tagihan-mahasiswas" 
                            size="sm"
                        >
                            Lihat Rincian Tagihan
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-widgets::widget>