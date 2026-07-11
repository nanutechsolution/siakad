<x-filament-panels::page>
    {{ $this->form }}
    <x-filament::actions
        :actions="$this->getFormActions()"
        alignment="start" />
    {{ $this->table }}
</x-filament-panels::page>