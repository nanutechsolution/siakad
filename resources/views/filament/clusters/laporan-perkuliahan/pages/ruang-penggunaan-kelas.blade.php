<x-filament-panels::page>
    <x-filament-widgets::widgets
        :widgets="$this->getVisibleHeaderWidgets()"
        :columns="$this->getHeaderWidgetsColumns()"
        />

    {{ $this->table }}
</x-filament-panels::page>