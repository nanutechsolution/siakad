<x-filament-panels::page>
    <div class="mb-4 max-w-xs">
        <x-filament::input.wrapper>
            <select wire:model.live="tahunAkademikId" class="fi-select-input block w-full">
                <option value="">-- Pilih Tahun Akademik --</option>
                @foreach ($daftarTahunAkademik as $ta)
                <option value="{{ $ta->id }}">
                    {{ $ta->nama_tahun }} ({{ $ta->semester == 1 ? 'Ganjil' : ($ta->semester == 2 ? 'Genap' : 'Pendek') }})
                </option>
                @endforeach
            </select>
        </x-filament::input.wrapper>
    </div>

    @if (blank($tahunAkademikId))
    <x-filament::section>
        Belum ada riwayat KRS untuk ditampilkan.
    </x-filament::section>
    @else
    {{-- Header identitas --}}
    <x-filament::section class="mb-4">
        <div class="flex items-start gap-4">
            @if ($logo = config('siakad.logo_path'))
            <img src="{{ asset($logo) }}" alt="Logo" class="h-16 w-16 object-contain" />
            @endif
            <div>
                <h2 class="text-lg font-bold">{{ config('siakad.nama_universitas', 'Universitas Stella Maris Sumba') }}</h2>
                <p class="text-sm text-gray-500">Kartu Hasil Studi (KHS)</p>
            </div>
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
            <div>
                <dt class="text-gray-500">Nama</dt>
                <dd class="font-medium">{{ $mahasiswa->nama_lengkap }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">NIM</dt>
                <dd class="font-medium">{{ $mahasiswa->nim }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Program Studi</dt>
                <dd class="font-medium">{{ $mahasiswa->prodi?->nama_prodi }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tahun Akademik</dt>
                <dd class="font-medium">{{ $daftarTahunAkademik->firstWhere('id', $tahunAkademikId)?->nama_tahun }}</dd>
            </div>
        </dl>
    </x-filament::section>

    {{-- Ringkasan --}}
    <div class="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-filament::section>
            <p class="text-xs text-gray-500">IPS</p>
            <p class="text-2xl font-bold">{{ number_format($khs['ringkasan']?->ips ?? 0, 2) }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500">IPK</p>
            <p class="text-2xl font-bold">{{ number_format($khs['ringkasan']?->ipk ?? 0, 2) }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500">Total SKS Semester</p>
            <p class="text-2xl font-bold">{{ $khs['ringkasan']?->sks_semester ?? 0 }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-xs text-gray-500">Jumlah Mata Kuliah</p>
            <p class="text-2xl font-bold">{{ $khs['jumlah_mk'] }}</p>
        </x-filament::section>
    </div>

    {{-- Tabel nilai --}}
    <x-filament::section>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left dark:border-gray-700">
                    <th class="py-2">Kode MK</th>
                    <th class="py-2">Nama MK</th>
                    <th class="py-2 text-center">SKS</th>
                    <th class="py-2 text-center">Nilai Huruf</th>
                    <th class="py-2 text-center">Bobot</th>
                    <th class="py-2 text-center">Mutu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($khs['mata_kuliah'] as $row)
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-2">{{ $row->mataKuliah?->kode_mk }}</td>
                    <td class="py-2">{{ $row->mataKuliah?->nama_mk }}</td>
                    <td class="py-2 text-center">{{ $row->mataKuliah?->sks_default }}</td>
                    <td class="py-2 text-center">{{ $row->nilai_huruf }}</td>
                    <td class="py-2 text-center">{{ number_format($row->nilai_indeks, 2) }}</td>
                    <td class="py-2 text-center">{{ number_format(($row->mataKuliah?->sks_default ?? 0) * $row->nilai_indeks, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-4 text-center text-gray-400">
                        Belum ada nilai yang dipublikasikan untuk semester ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-filament::section>
    @endif
</x-filament-panels::page>