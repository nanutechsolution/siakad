<div class="max-h-[28rem] overflow-y-auto">
    @if ($sesi->isEmpty())
        <p class="py-6 text-center text-sm text-gray-500">
            Belum ada sesi perkuliahan yang terlaksana untuk mata kuliah ini.
        </p>
    @else
        {{-- Desktop: tabel ringkas --}}
        <div class="hidden sm:block">
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
        </div>

        {{-- Mobile: kartu per pertemuan --}}
        <div class="space-y-3 sm:hidden">
            @foreach ($sesi as $item)
                <div class="rounded-xl border border-gray-200 p-3 dark:border-white/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold">Pertemuan {{ $item->pertemuan_ke }}</p>
                        <x-filament::badge :color="match ($item->status_kehadiran) {
                            'H' => 'success',
                            'I', 'S' => 'warning',
                            default => 'danger',
                        }">
                            {{ $item->status_label }}
                        </x-filament::badge>
                    </div>

                    <dl class="mt-2 grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <dt class="text-xs text-gray-500">Tanggal</dt>
                            <dd>{{ $item->tanggal?->translatedFormat('d M Y') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Check-in</dt>
                            <dd>{{ $item->waktu_check_in?->format('H:i') ?? '-' }}</dd>
                        </div>
                    </dl>

                    @if ($item->is_manual_update)
                        <p class="mt-2 text-xs text-gray-500">
                            Dikoreksi manual{{ $item->alasan_perubahan ? ": {$item->alasan_perubahan}" : '.' }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
