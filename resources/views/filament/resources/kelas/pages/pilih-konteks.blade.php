<x-filament-panels::page>
    <form wire:submit="terapkan">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Terapkan &amp; Lihat Daftar Kelas
        </x-filament::button>
    </form>
</x-filament-panels::page>