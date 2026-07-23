<div class="space-y-4">
    @if (! $preview || $preview['invalid_count'] === 0)
    <p class="text-sm text-success-600">Tidak ada baris tidak valid. Semua data siap diimpor.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm border">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="p-2 border">Baris</th>
                    <th class="p-2 border">NIM</th>
                    <th class="p-2 border">Kode MK</th>
                    <th class="p-2 border">Kesalahan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($preview['invalid_rows'] as $row)
                <tr>
                    <td class="p-2 border">{{ $row['row_number'] }}</td>
                    <td class="p-2 border">{{ $row['data']['nim'] ?? '-' }}</td>
                    <td class="p-2 border">{{ $row['data']['kode_mk'] ?? '-' }}</td>
                    <td class="p-2 border text-danger-600">
                        <ul class="list-disc list-inside">
                            @foreach ($row['errors'] as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-500">
        Anda tetap dapat melanjutkan ke langkah Impor — baris tidak valid di atas akan otomatis
        dilewati dan dicatat pada Laporan Kesalahan.
    </p>
    @endif
</div>