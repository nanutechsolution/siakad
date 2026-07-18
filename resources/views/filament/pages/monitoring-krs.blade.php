<x-filament-panels::page>
    {{-- Filter Section --}}
    <form wire:submit.prevent>
        {{ $this->filtersForm }}
    </form>

    {{-- Stats Overview (Dibuat lebih menonjol) --}}
    @livewire(\App\Filament\Widgets\MonitoringKrs\KrsStatsOverview::class, ['pageFilters' => $filters])

    {{-- Warning Panel --}}
    @livewire(\App\Filament\Widgets\MonitoringKrs\KrsWarningPanel::class, ['pageFilters' => $filters])

    {{-- Charts Container (Tetap grid agar grafik tidak terlalu lebar) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-filament::section>
            @livewire(\App\Filament\Widgets\MonitoringKrs\KrsProgressPerProdiChart::class, ['pageFilters' => $filters])
        </x-filament::section>
        <x-filament::section>
            @livewire(\App\Filament\Widgets\MonitoringKrs\KrsApprovalPieChart::class, ['pageFilters' => $filters])
        </x-filament::section>
    </div>

    {{-- Trend Chart --}}
    <x-filament::section>
        @livewire(\App\Filament\Widgets\MonitoringKrs\KrsTrendLineChart::class, ['pageFilters' => $filters])
    </x-filament::section>
</x-filament-panels::page>