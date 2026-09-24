<x-filament-widgets::widget>
    @php($data = $this->getData())
    <x-filament::section
        heading="Perhatian — {{ $data['context'] }}"
        icon="heroicon-o-bell-alert"
    >
        <div class="mb-3 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <x-heroicon-o-calendar-days class="h-4 w-4" />
            <span>{{ $data['tahun'] }}</span>
        </div>

        @php($aktif = collect($data['alerts'])->filter(fn($a) => $a['total'] > 0))

        @if($aktif->isEmpty())
            <div class="flex items-center gap-3 rounded-xl border border-success-200 bg-success-50 p-4 dark:border-success-800 dark:bg-success-950/40">
                <x-heroicon-o-check-circle class="h-6 w-6 shrink-0 text-success-500" />
                <div>
                    <p class="text-sm font-semibold text-success-800 dark:text-success-300">Semua perhatian sudah beres</p>
                    <p class="text-xs text-success-700 dark:text-success-400">Tidak ada KRS tertunda, kelas bermasalah, atau mahasiswa tanpa kelas pada scope Anda.</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($aktif->sortBy(fn($a) => $a['severity'] === 'danger' ? 0 : 1)->values() as $alert)
                    <a
                        href="{{ $alert['url'] }}"
                        @class([
                            'group flex items-start gap-3 rounded-xl border p-4 transition hover:shadow-sm',
                            'border-danger-200 bg-danger-50 dark:border-danger-800 dark:bg-danger-950/40' => $alert['severity'] === 'danger',
                            'border-warning-200 bg-warning-50 dark:border-warning-800 dark:bg-warning-950/40' => $alert['severity'] === 'warning',
                            'border-info-200 bg-info-50 dark:border-info-800 dark:bg-info-950/40' => ! in_array($alert['severity'], ['danger', 'warning'], true),
                        ])
                    >
                        <x-filament::icon
                            :icon="$alert['icon']"
                            @class([
                                'h-6 w-6 shrink-0',
                                'text-danger-500' => $alert['severity'] === 'danger',
                                'text-warning-500' => $alert['severity'] === 'warning',
                                'text-info-500' => ! in_array($alert['severity'], ['danger', 'warning'], true),
                            ])
                        />
                        <div class="min-w-0">
                            <div class="flex items-baseline gap-2">
                                <span class="text-2xl font-bold tabular-nums text-gray-900 dark:text-white">{{ number_format($alert['total']) }}</span>
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $alert['label'] }}</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $alert['description'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
