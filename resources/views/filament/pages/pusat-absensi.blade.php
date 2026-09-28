<x-filament-panels::page>
    <form wire:submit.prevent="preview">
        {{ $this->form }}

        <div class="mt-4 flex flex-wrap gap-3">
            <x-filament::button type="submit" icon="heroicon-o-eye">
                Preview Dokumen
            </x-filament::button>

            @if ($previewGenerated)
                <x-filament::button color="danger" icon="heroicon-o-document-arrow-down" wire:click="downloadPdf">
                    Unduh PDF
                </x-filament::button>
                <x-filament::button color="success" icon="heroicon-o-table-cells" wire:click="downloadExcel">
                    Unduh Excel
                </x-filament::button>
            @endif
        </div>
    </form>

    @if ($previewGenerated && $this->documentSummary)
        @php $summary = $this->documentSummary; @endphp
        <div class="mt-6 space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-950 dark:text-white">Pratinjau Dokumen</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">{{ $summary['mode'] }}</div>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">
                        <span class="text-gray-500">Mata Kuliah:</span>
                        {{ trim($summary['akademik']['kode_mk'] . ' - ' . $summary['akademik']['mata_kuliah'], ' -') ?: '-' }}
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">
                        <span class="text-gray-500">Kelas:</span> {{ $summary['akademik']['kelas'] ?: '-' }}
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">
                        <span class="text-gray-500">Dosen:</span> {{ $summary['akademik']['dosen'] ?: '-' }}
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">
                        <span class="text-gray-500">Tahun Akademik:</span>
                        {{ trim($summary['akademik']['tahun_akademik'] . ' (' . $summary['akademik']['semester'] . ')') ?: '-' }}
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">
                        <span class="text-gray-500">Pertemuan / Tanggal:</span>
                        {{ ($summary['akademik']['pertemuan'] ? 'P' . $summary['akademik']['pertemuan'] : '—') }}
                        / {{ $summary['akademik']['tanggal'] ?: '—' }}
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">
                        <span class="text-gray-500">Jumlah mahasiswa:</span> {{ $summary['summary']['jumlah_mahasiswa'] }}
                    </div>
                </div>

                @if (($summary['mode'] ?? '') === 'Hasil Absensi Online')
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        <x-filament::badge color="success">Hadir {{ $summary['summary']['hadir'] }}</x-filament::badge>
                        <x-filament::badge color="info">Izin {{ $summary['summary']['izin'] }}</x-filament::badge>
                        <x-filament::badge color="warning">Sakit {{ $summary['summary']['sakit'] }}</x-filament::badge>
                        <x-filament::badge color="danger">Alpa {{ $summary['summary']['alpa'] }}</x-filament::badge>
                        <x-filament::badge color="gray">Belum presensi {{ $summary['summary']['belum_presensi'] }}</x-filament::badge>
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm font-semibold text-gray-950 dark:text-white">
                    Pratinjau Baris ({{ count($summary['rows']) }}{{ $summary['summary']['jumlah_mahasiswa'] > count($summary['rows']) ? ' dari ' . $summary['summary']['jumlah_mahasiswa'] : '' }})
                </div>
                @if (count($summary['rows']) === 0)
                    <div class="mt-2 text-sm text-gray-500">Tidak ada mahasiswa pada data yang dipilih.</div>
                @else
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500 dark:border-gray-700">
                                    <th class="py-2 pr-3">No</th>
                                    <th class="py-2 pr-3">NIM</th>
                                    <th class="py-2 pr-3">Nama</th>
                                    @if (($summary['mode'] ?? '') === 'Hasil Absensi Online')
                                        <th class="py-2 pr-3">Status</th>
                                        <th class="py-2 pr-3">Jam</th>
                                        <th class="py-2 pr-3">Keterangan</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary['rows'] as $row)
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="py-2 pr-3">{{ $row['no'] }}</td>
                                        <td class="py-2 pr-3">{{ $row['nim'] }}</td>
                                        <td class="py-2 pr-3">{{ $row['nama'] }}</td>
                                        @if (($summary['mode'] ?? '') === 'Hasil Absensi Online')
                                            <td class="py-2 pr-3">{{ $row['status'] }}</td>
                                            <td class="py-2 pr-3">{{ $row['waktu'] ?: '—' }}</td>
                                            <td class="py-2 pr-3">{{ $row['keterangan'] ?: '—' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
