<x-filament-panels::page>

    {{ $this->form }}
    <x-filament::actions
        :actions="$this->getFormActions()"
        alignment="start" />
    <div>
        @livewire(\App\Filament\Widgets\LaporanKeuanganStatsWidget::class, ['filters' => $filterData], key(md5(json_encode($filterData))))
    </div>
    {{ $this->table }}
</x-filament-panels::page>