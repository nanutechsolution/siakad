<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="text-lg font-bold mb-4">Jadwal Mengajar Hari Ini</h2>
        @if($jadwal->isEmpty())
            <p>Tidak ada jadwal mengajar hari ini.</p>
        @else
            <div class="space-y-2">
                @foreach($jadwal as $item)
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg flex justify-between">
                        <div>
                            <p class="font-bold">{{ $item->mataKuliah->nama_mk }}</p>
                            <p class="text-sm text-gray-600">Ruang: {{ $item->ruang->nama_ruang ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold">{{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>