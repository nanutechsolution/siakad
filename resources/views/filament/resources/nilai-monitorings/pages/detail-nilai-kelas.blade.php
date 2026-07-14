<x-filament-panels::page>
    <x-filament::section>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="text-gray-500">Tahun Akademik</div>
                <div class="font-medium">{{ $record->tahunAkademik?->nama_tahun }}</div>
            </div>
            <div>
                <div class="text-gray-500">Program Studi</div>
                <div class="font-medium">{{ $record->kelas?->prodi?->nama_prodi }}</div>
            </div>
            <div>
                <div class="text-gray-500">Kelas Kuliah</div>
                <div class="font-medium">{{ $record->kelas?->nama_kelas }}</div>
            </div>
            <div>
                <div class="text-gray-500">Dosen Pengampu</div>
                <div class="font-medium">
                    @foreach ($record->dosenPengampu as $dosen)
                    {{ $dosen->person?->nama_lengkap }}{{ $dosen->pivot->is_koordinator ? ' (Koordinator)' : '' }}@if (!$loop->last), @endif
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::section>

    {{ $this->table }}
</x-filament-panels::page>