<x-filament-panels::page>
    <x-filament::section class="mb-6">
        <x-slot name="heading">Beban Bimbingan Terbanyak (Dosen Wali)</x-slot>

        @php $beban = $this->getBebanDosenTerbanyak(); @endphp

        @if ($beban->isEmpty())
        <div class="text-center py-6 text-gray-500">
            <p>Belum ada data penugasan Dosen Wali aktif.</p>
        </div>
        @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($beban as $item)
            <li class="flex items-center justify-between py-2 text-sm">
                <span>{{ $item['dosen']->person?->nama_lengkap ?? '-' }} <span class="text-gray-400">({{ $item['dosen']->nidn }})</span></span>
                <span class="font-semibold">{{ $item['total'] }} mahasiswa/kelas</span>
            </li>
            @endforeach
        </ul>
        @endif
    </x-filament::section>

    {{ $this->table }}
</x-filament-panels::page>