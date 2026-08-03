<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="submit"
                wire:confirm="Yakin ingin menugaskan pembimbing ini? Pastikan target dan dosen sudah benar.">
                <span wire:loading.remove wire:target="submit">Tugaskan Pembimbing</span>
                <span wire:loading wire:target="submit">Menyimpan...</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>