<x-filament-panels::page>
    <form wire:submit="applyFilters">
        {{ $this->filterForm }}
        <div class="flex items-center gap-x-3 mt-4">
            <x-filament::button type="submit" icon="heroicon-o-funnel">
                Terapkan Filter
            </x-filament::button>
            <x-filament::button type="button" color="gray" icon="heroicon-o-x-mark" wire:click="resetFilters">
                Reset Filter
            </x-filament::button>
        </div>
    </form>
    {{ $this->table }}
</x-filament-panels::page>