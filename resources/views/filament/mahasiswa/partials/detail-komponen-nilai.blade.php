<div class="space-y-3">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                <th class="py-2">Komponen</th>
                <th class="py-2 text-right">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($record->nilaiKomponen as $komponen)
            <tr class="border-b border-gray-100 dark:border-gray-800">
                <td class="py-2">{{ $komponen->komponenNilai?->nama_komponen ?? '-' }}</td>
                <td class="py-2 text-right">{{ number_format($komponen->nilai_angka, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="py-3 text-center text-gray-400">
                    Belum ada rincian komponen untuk mata kuliah ini.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-gray-300 dark:border-gray-600 font-semibold">
                <td class="py-2">Nilai Akhir</td>
                <td class="py-2 text-right">
                    {{ number_format($record->nilai_angka, 2) }} ({{ $record->nilai_huruf }})
                </td>
            </tr>
        </tfoot>
    </table>
</div>