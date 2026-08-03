<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="submit">
            {{ $this->form }}

            <div class="mt-4 flex justify-end">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    Simpan Penugasan
                </x-filament::button>
            </div>
        </form>

        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white mb-4">
                Daftar Penugasan Pembimbing Akademik
            </h2>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>