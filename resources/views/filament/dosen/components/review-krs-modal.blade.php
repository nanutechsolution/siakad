<div class="space-y-4">
    <div class="text-sm text-gray-500">
        Silakan periksa mata kuliah yang diambil mahasiswa sebelum menyetujui.
    </div>
    
    <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2">Mata Kuliah</th>
                    <th class="px-4 py-2">SKS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $item)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $item->nama_mk_snapshot }}</td>
                        <td class="px-4 py-2">{{ $item->sks_snapshot }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 font-bold">
                <tr>
                    <td class="px-4 py-2">Total SKS</td>
                    <td class="px-4 py-2">{{ $krs->total_sks_diambil }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>