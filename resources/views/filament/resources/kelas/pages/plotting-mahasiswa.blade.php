<x-filament-panels::page>
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900 mb-4">
        <h2 class="text-lg font-bold">Plotting Mahasiswa</h2>

        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Konteks:
            @if ($tahunAkademikLabel !== '-')
            {{ $tahunAkademikLabel }} ·
            @endif
            {{ $prodiLabel }} · Angkatan {{ $angkatanId }}
        </p>

        <p class="mt-2 font-semibold text-gray-900 dark:text-gray-100">
            Belum memiliki kelas: {{ $this->getJumlahBelumBerkelas() }} mahasiswa
        </p>
    </div>

    {{ $this->table }}
</x-filament-panels::page>