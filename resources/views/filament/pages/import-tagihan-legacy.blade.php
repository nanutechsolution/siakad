<x-filament-panels::page>
    <form wire:submit="processImport" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-start" style="margin-top: 10px;">
            <x-filament::button type="submit" color="primary" icon="heroicon-o-arrow-up-tray">
                Mulai Proses Import
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>