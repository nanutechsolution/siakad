<x-filament-widgets::widget>
    <x-filament::section
        heading="Jadwal Hari Ini"
        description="{{ $hari ?? '' }} · {{ now()->translatedFormat('d F Y') }}"
        icon="heroicon-o-calendar-days"
    >
        @if ($jadwal->isEmpty())
            <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/60">
                <x-filament::icon icon="heroicon-o-moon" class="h-6 w-6 shrink-0 text-gray-400" />
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tidak ada kuliah hari ini</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Jadwal mengajar Anda pada hari {{ $hari ?? '' }} tidak tersedia.</p>
                </div>
            </div>
        @else
            <div class="space-y-2 sm:space-y-3">
                @foreach ($jadwal as $item)
                    <div class="flex flex-col gap-2 rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-md bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                    {{ substr($item->jam_mulai ?? '', 0, 5) }} – {{ substr($item->jam_selesai ?? '', 0, 5) }}
                                </span>
                                <span class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $item->mataKuliah?->nama_mk ?? 'Mata kuliah tidak tersedia' }}</span>
                            </div>
                            <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                Kelas {{ $item->kelas?->nama_kelas ?? '-' }} · Ruang {{ $item->ruang?->nama_ruang ?? 'belum ditentukan' }}
                            </p>
                        </div>
                        <a href="{{ \App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource::getUrl('view', ['record' => $item]) }}"
                           class="shrink-0 rounded-lg bg-primary-50 px-3 py-2 text-center text-xs font-semibold text-primary-700 transition hover:bg-primary-100 dark:bg-primary-500/10 dark:text-primary-300">
                            Detail
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
