@php
// Ambil data detail komponen biaya secara langsung berdasarkan ID tagihan
$details = DB::table('tagihan_mahasiswas_details')
->where('tagihan_id', $getRecord()->id)
->get();
@endphp
<x-filament::section>
    <div class="overflow-x-auto border border-gray-200 rounded-lg dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold">
                <tr>
                    <th class="px-4 py-3">Komponen Biaya</th>
                    <th class="px-4 py-3 text-right">Nominal Tagihan</th>
                    <th class="px-4 py-3 text-right">Potongan/Diskon</th>
                    <th class="px-4 py-3 text-right">Telah Terbayar</th>
                    <th class="px-4 py-3 text-right">Sisa Kewajiban</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-600 dark:text-gray-400">
                @forelse($details as $item)
                @php
                $nettoTagihan = $item->nominal_dasar - $item->nominal_diskon;
                $sisa = max(0, $nettoTagihan - $item->nominal_terbayar);
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                        {{ $item->nama_komponen_snapshot }}
                    </td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($item->nominal_dasar, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-success-600 font-medium">- Rp {{ number_format($item->nominal_diskon, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-success-700">Rp {{ number_format($item->nominal_terbayar, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right {{ $sisa > 0 ? 'text-danger-600 font-bold' : 'text-success-600' }}">
                        Rp {{ number_format($sisa, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-4 text-center text-gray-400">Tidak ada rincian komponen biaya.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament::section>