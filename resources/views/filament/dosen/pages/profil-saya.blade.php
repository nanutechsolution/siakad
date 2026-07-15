<x-filament-panels::page>

    {{-- Dashboard Kinerja --}}
    <x-filament::section>
        <x-slot name="heading">
            Ringkasan Kinerja — {{ $stats['tahun_aktif'] }}
        </x-slot>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border p-4">
                <p class="text-xs text-gray-500">Kelas Diampu</p>
                <p class="text-2xl font-bold">{{ $stats['jumlah_kelas_aktif'] }}</p>
            </div>
            <div class="rounded-xl border p-4">
                <p class="text-xs text-gray-500">Mahasiswa Wali</p>
                <p class="text-2xl font-bold">{{ $stats['jumlah_mahasiswa_wali'] }}</p>
            </div>
            <div class="rounded-xl border p-4">
                <p class="text-xs text-gray-500">Riset sbg Ketua</p>
                <p class="text-2xl font-bold">{{ $stats['jumlah_penelitian_ketua'] }}</p>
            </div>
            <div class="rounded-xl border p-4">
                <p class="text-xs text-gray-500">Riset sbg Anggota</p>
                <p class="text-2xl font-bold">{{ $stats['jumlah_penelitian_anggota'] }}</p>
            </div>
            <div class="rounded-xl border p-4">
                <p class="text-xs text-gray-500">Total Luaran/Publikasi</p>
                <p class="text-2xl font-bold">{{ $stats['total_luaran'] }}</p>
            </div>
            <div class="rounded-xl border p-4">
                <p class="text-xs text-gray-500">Skor EDOM Rata-rata</p>
                <p class="text-2xl font-bold">{{ $stats['skor_edom'] ?? '-' }}</p>
            </div>
        </div>

        @if ($stats['luaran_per_tahun']->isNotEmpty())
            <div class="mt-4">
                <p class="mb-2 text-sm font-medium text-gray-600">Luaran Terverifikasi per Tahun (5 tahun terakhir)</p>
                <div class="flex gap-3">
                    @foreach ($stats['luaran_per_tahun'] as $row)
                        <div class="rounded-lg border px-3 py-2 text-center">
                            <p class="text-xs text-gray-500">{{ $row->tahun_terbit }}</p>
                            <p class="font-semibold">{{ $row->jumlah }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-filament::section>

    {{-- Form Biodata & Dokumen --}}
    <form wire:submit="save" class="mt-6">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Simpan Perubahan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>