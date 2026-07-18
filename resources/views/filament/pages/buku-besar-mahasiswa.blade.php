<x-filament-panels::page>
    {{ $this->form }}
    @if($mahasiswaInfo)
    <x-filament::fieldset>
        <x-slot name="label">
            Informasi Mahasiswa
        </x-slot>
        {{-- Content --}}
        <div class="p-6 bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Informasi Mahasiswa</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="block text-gray-500 dark:text-gray-400">NIM</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $mahasiswaInfo->nim }}</span>
                </div>
                <div>
                    <span class="block text-gray-500 dark:text-gray-400">Nama Lengkap</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $mahasiswaInfo->nama_mahasiswa }}</span>
                </div>
                <div>
                    <span class="block text-gray-500 dark:text-gray-400">Program Studi</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $mahasiswaInfo->nama_prodi }}</span>
                </div>
                <div>
                    <span class="block text-gray-500 dark:text-gray-400">Angkatan</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $mahasiswaInfo->angkatan }}</span>
                </div>
            </div>
        </div>
    </x-filament::fieldset>

    @endif
    {{ $this->table }}
</x-filament-panels::page>