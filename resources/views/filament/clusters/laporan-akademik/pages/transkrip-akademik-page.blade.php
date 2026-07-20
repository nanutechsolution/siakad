<x-filament-panels::page>

    <x-filament::section
        heading="Filter"
        icon="heroicon-o-funnel"
    >
        {{ $this->schema }}
    </x-filament::section>

    @php
        $result = $this->getTranskripData();
    @endphp

    @if ($result)
        @php
            $dto = $result['data'];
        @endphp

        <div class="mt-6">
            <x-filament::section
                heading="Informasi Mahasiswa"
                icon="heroicon-o-user"
            >
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div>
                        <div class="text-sm text-gray-500">NIM</div>
                        <div class="text-lg font-semibold">{{ $dto->nim }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Nama Mahasiswa</div>
                        <div class="text-lg font-semibold">{{ $dto->nama_mahasiswa }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">IPK</div>
                        <div class="text-lg font-semibold text-primary-600">
                            {{ number_format($dto->ipk_final, 2) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Total SKS</div>
                        <div class="text-lg font-semibold">
                            {{ $dto->total_sks_final }}
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <div class="mt-6">
            <x-filament::section
                heading="Daftar Mata Kuliah"
                icon="heroicon-o-academic-cap"
            >
                <div class="overflow-x-auto">
                    <table class="fi-ta-table w-full">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode MK</th>
                                <th>Nama Mata Kuliah</th>
                                <th class="text-center">SKS</th>
                                <th class="text-center">Nilai Angka</th>
                                <th class="text-center">Nilai Huruf</th>
                                <th class="text-center">Nilai Indeks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dto->mata_kuliah_details as $i => $mk)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $mk['kode_mk'] }}</td>
                                    <td>{{ $mk['nama_mk'] }}</td>
                                    <td class="text-center">{{ $mk['sks'] }}</td>
                                    <td class="text-center">{{ $mk['nilai_angka'] }}</td>
                                    <td class="text-center font-semibold">
                                        {{ $mk['nilai_huruf'] }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($mk['nilai_indeks'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-gray-500">
                                        Belum ada data nilai untuk mahasiswa ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    @else
        <div class="mt-6">
            <x-filament::section>
                <div class="py-8 text-center text-gray-500">
                    Silakan pilih mahasiswa terlebih dahulu untuk menampilkan transkrip akademik.
                </div>
            </x-filament::section>
        </div>
    @endif

</x-filament-panels::page>