<x-filament-panels::page>
    <div class="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
        <x-filament::section>
            <p class="text-xs text-gray-500">IPK</p>
            <p class="text-2xl font-bold">{{ number_format($data['ipk'] ?? 0, 2) }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500">Total SKS Ditempuh</p>
            <p class="text-2xl font-bold">{{ $data['total_sks_ditempuh'] }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500">Total SKS Lulus</p>
            <p class="text-2xl font-bold">{{ $data['total_sks_lulus'] }}</p>
        </x-filament::section>
    </div>

    <x-filament::section>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left dark:border-gray-700">
                    <th class="py-2">Kode MK</th>
                    <th class="py-2">Nama MK</th>
                    <th class="py-2 text-center">SKS</th>
                    <th class="py-2 text-center">Nilai Terakhir</th>
                    <th class="py-2 text-center">Mutu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['mata_kuliah'] as $row)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="py-2">{{ $row->mataKuliah?->kode_mk }}</td>
                        <td class="py-2">{{ $row->mataKuliah?->nama_mk }}</td>
                        <td class="py-2 text-center">{{ $row->sks_diakui }}</td>
                        <td class="py-2 text-center">{{ $row->nilai_huruf_final }}</td>
                        <td class="py-2 text-center">{{ number_format($row->nilai_indeks_final, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-400">
                            Belum ada riwayat akademik yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-filament::section>
</x-filament-panels::page>