<x-filament-panels::page>

    <x-filament::section
        heading="Filter Laporan"
        description="Pilih parameter laporan yang ingin ditampilkan."
        icon="heroicon-o-funnel">
        {{ $this->schema }}
    </x-filament::section>

    <x-filament::section
        heading="Ringkasan"
        icon="heroicon-o-chart-bar"
        collapsible>
        @livewire(\App\Filament\Widgets\Laporan\RekapKhsOverviewWidget::class, [
        'summary' => $this->getReportData()['summary'] ?? [],
        ])
    </x-filament::section>
    {{ $this->table }}
</x-filament-panels::page>