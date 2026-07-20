<x-filament-panels::page>

    <x-filament::section
        heading="Filter"
        description="Pilih parameter laporan"
        icon="heroicon-o-funnel">
        {{ $this->schema }}
    </x-filament::section>

    <div class="mt-6">
        <x-filament::section
            heading="Ringkasan Rekap KRS"
            icon="heroicon-o-chart-bar">
            @livewire(\App\Filament\Widgets\Reports\RekapKrsOverviewWidget::class, [
            'summary' => $this->getReportData()['summary'] ?? [],
            ])
        </x-filament::section>
    </div>
    {{ $this->table }}
</x-filament-panels::page>