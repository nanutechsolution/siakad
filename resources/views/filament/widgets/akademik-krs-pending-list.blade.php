<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            KRS Menunggu Persetujuan
        </x-slot>

        @if ($rows->isEmpty())
            <div class="flex items-center gap-x-3 rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">
                <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
                Tidak ada KRS yang menunggu persetujuan saat ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="py-2 pr-4 font-medium">Nama Mahasiswa</th>
                            <th class="py-2 pr-4 font-medium">NIM</th>
                            <th class="py-2 pr-4 font-medium">Program Studi</th>
                            <th class="py-2 pr-4 font-medium">Diajukan Pada</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($rows as $row)
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-950 dark:text-white">{{ $row->nama_lengkap }}</td>
                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">{{ $row->nim }}</td>
                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">{{ $row->nama_prodi }}</td>
                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">
                                    {{ $row->diajukan_at ? \Illuminate\Support\Carbon::parse($row->diajukan_at)->translatedFormat('d M Y, H:i') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>