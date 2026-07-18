<x-filament-widgets::widget>
    <x-filament::section heading="Perhatian" icon="heroicon-o-bell-alert">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($this->getWarnings() as $warning)
                <div @class([
                    'flex items-start gap-3 rounded-xl border p-4',
                    'border-danger-200 bg-danger-50 dark:border-danger-800 dark:bg-danger-950/40' => $warning['color'] === 'danger' && $warning['total'] > 0,
                    'border-warning-200 bg-warning-50 dark:border-warning-800 dark:bg-warning-950/40' => $warning['color'] === 'warning' && $warning['total'] > 0,
                    'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/40' => $warning['total'] === 0 || $warning['color'] === 'gray',
                ])>
                    <x-filament::icon
                        :icon="$warning['icon']"
                        @class([
                            'h-6 w-6 shrink-0',
                            'text-danger-500' => $warning['color'] === 'danger' && $warning['total'] > 0,
                            'text-warning-500' => $warning['color'] === 'warning' && $warning['total'] > 0,
                            'text-gray-400' => $warning['total'] === 0 || $warning['color'] === 'gray',
                        ])
                    />
                    <div>
                        <div class="text-2xl font-semibold tabular-nums">
                            {{ number_format($warning['total']) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $warning['label'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>