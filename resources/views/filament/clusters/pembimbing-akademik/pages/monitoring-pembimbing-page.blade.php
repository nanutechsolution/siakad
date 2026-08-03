<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mb-6">
        <x-filament::section>
            <div class="text-sm text-gray-500">Total Mahasiswa Aktif</div>
            <div class="text-2xl font-bold">{{ $this->getTotalMahasiswaAktif() }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Sudah Memiliki Dosen Wali</div>
            <div class="text-2xl font-bold text-success-600">{{ $this->getTotalTerbimbing() }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Belum Memiliki Dosen Wali</div>
            <div class="text-2xl font-bold text-danger-600">{{ $this->getTotalBelumTerbimbing() }}</div>
        </x-filament::section>
    </div>

    {{ $this->table }}
</x-filament-panels::page>