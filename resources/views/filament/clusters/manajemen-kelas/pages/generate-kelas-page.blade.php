<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}

        <div class="mt-4 flex gap-2">
            <x-filament::button wire:click="hitungPreview" color="gray" icon="heroicon-o-eye">
                Hitung Preview
            </x-filament::button>

            @if ($previewGenerated && $jumlahMahasiswaTanpaKelas > 0)
            <x-filament::button
                wire:click="generate"
                wire:loading.attr="disabled"
                wire:target="generate"
                wire:confirm="Yakin ingin generate kelas dan menempatkan {{ $jumlahMahasiswaTanpaKelas }} mahasiswa sekarang? Aksi ini akan langsung membuat data di database."
                icon="heroicon-o-bolt">
                <span wire:loading.remove wire:target="generate">Generate &amp; Tempatkan {{ $jumlahMahasiswaTanpaKelas }} Mahasiswa</span>
                <span wire:loading wire:target="generate">Memproses...</span>
            </x-filament::button>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>