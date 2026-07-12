<x-filament-panels::page>
    <div class="mb-4">
        <h3 class="text-base font-semibold">
            {{ $record->mataKuliah->nama_mk }} — {{ $record->kelas->nama_kelas }}
        </h3>
        <p class="text-sm text-gray-500">
            Tahun Akademik: {{ $record->tahunAkademik->nama_tahun }}
            @unless ($record->tahunAkademik->buka_input_nilai)
                <span class="text-danger-600 font-medium">— Periode input nilai belum dibuka</span>
            @endunless
            @if ($record->tahunAkademik->is_locked_nilai)
                <span class="text-danger-600 font-medium">— Periode input nilai sudah dikunci</span>
            @endif
        </p>
    </div>

    {{ $this->table }}
</x-filament-panels::page>