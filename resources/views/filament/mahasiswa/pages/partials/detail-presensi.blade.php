<div class="max-h-[28rem] overflow-y-auto">
    @if ($sesi->isEmpty())
        <p class="py-6 text-center text-sm text-gray-500">
            Belum ada sesi perkuliahan yang terlaksana untuk mata kuliah ini.
        </p>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left text-gray-500 dark:border-white/10">
                    <th class="py-2 pr-2">Pertemuan</th>
                    <th class="py-2 pr-2">Tanggal</th>
                    <th class="py-2 pr-2">Check-in</th>
                    <th class="py-2 pr-2">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($sesi as $item)
                    <tr>
                        <td class="py-2 pr-2 align-top">{{ $item->pertemuan_ke }}</td>
                        <td class="py-2 pr-2 align-top">
                            {{ $item->tanggal?->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-2 pr-2 align-top">
                            {{ $item->waktu_check_in?->format('H:i') ?? '-' }}
                        </td>
                        <td class="py-2 pr-2 align-top">
                            <x-filament::badge :color="match ($item->status_kehadiran) {
                                'H' => 'success',
                                'I', 'S' => 'warning',
                                default => 'danger',
                            }">
                                {{ $item->status_label }}
                            </x-filament::badge>

                            @if ($item->is_manual_update)
                                <p class="mt-1 text-xs text-gray-500">
                                    Dikoreksi manual{{ $item->alasan_perubahan ? ": {$item->alasan_perubahan}" : '.' }}
                                </p>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
