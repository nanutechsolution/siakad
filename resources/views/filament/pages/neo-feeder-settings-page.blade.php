<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-3">
            <x-filament::button type="submit">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </form>

    <x-filament::section>
        <x-slot name="heading">Status Token</x-slot>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ $this->getTokenStatus() }}
        </p>

        <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
            Nilai token dan password tidak pernah ditampilkan. Detail error hanya tercatat di log.
        </p>
    </x-filament::section>
</x-filament-panels::page>