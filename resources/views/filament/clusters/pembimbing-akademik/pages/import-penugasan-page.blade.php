<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Langkah 1 — Unggah File</x-slot>
            <x-slot name="description">
                Kolom: nim_mahasiswa, nidn_dosen, jenis, tanggal_mulai, keterangan (opsional).
            </x-slot>

            <div class="mb-4">
                <x-filament::button color="gray" wire:click="downloadTemplate" icon="heroicon-o-arrow-down-tray">
                    Unduh Template Excel
                </x-filament::button>
            </div>

            <form wire:submit.prevent="generatePreview">
                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button type="submit" icon="heroicon-o-eye">
                        Tampilkan Preview
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        @if ($previewGenerated)
        <x-filament::section>
            <x-slot name="heading">Langkah 2 — Preview &amp; Validasi</x-slot>

            <div class="mb-4 flex gap-4 text-sm">
                <span class="text-success-600 font-semibold">{{ $totalValid }} baris valid</span>
                <span class="text-danger-600 font-semibold">{{ $totalGagal }} baris bermasalah (diabaikan)</span>
            </div>

            @if (empty($previewRows))
            <div class="text-center py-8 text-gray-500">
                <p>Tidak ada data terbaca dari file.</p>
            </div>
            @else
            <div class="overflow-x-auto mb-4 max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-white dark:bg-gray-900">
                        <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-2">Baris</th>
                            <th class="py-2 pr-2">NIM</th>
                            <th class="py-2 pr-2">Mahasiswa</th>
                            <th class="py-2 pr-2">NIDN</th>
                            <th class="py-2 pr-2">Dosen</th>
                            <th class="py-2 pr-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($previewRows as $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800 {{ $row['valid'] ? '' : 'bg-danger-50 dark:bg-danger-500/10' }}">
                            <td class="py-1 pr-2">{{ $row['baris'] }}</td>
                            <td class="py-1 pr-2">{{ $row['nim'] }}</td>
                            <td class="py-1 pr-2">{{ $row['mahasiswa_nama'] ?? '-' }}</td>
                            <td class="py-1 pr-2">{{ $row['nidn'] }}</td>
                            <td class="py-1 pr-2">{{ $row['dosen_nama'] ?? '-' }}</td>
                            <td class="py-1 pr-2">
                                @if ($row['valid'])
                                <span class="text-success-600">Valid</span>
                                @else
                                <span class="text-danger-600">{{ implode('; ', $row['errors']) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if ($totalValid > 0)
            <div
                x-data="{
                            running: false,
                            async start() {
                                this.running = true
                                let done = false
                                while (! done) {
                                    const result = await $wire.processBatch(10)
                                    done = result.done
                                }
                                this.running = false
                            },
                        }">
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span>Progres: {{ $processed }} / {{ $totalValid }}</span>
                    <span>{{ $totalValid > 0 ? round(($processed / $totalValid) * 100) : 0 }}%</span>
                </div>

                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden mb-4">
                    <div
                        class="bg-primary-600 h-3 transition-all duration-300"
                        style="width: {{ $totalValid > 0 ? round(($processed / $totalValid) * 100) : 0 }}%"></div>
                </div>

                <x-filament::button
                    x-on:click="start()"
                    x-bind:disabled="running || {{ $processed >= $totalValid ? 'true' : 'false' }}"
                    icon="heroicon-o-play">
                    <span x-show="! running">Proses {{ $totalValid }} Baris Valid</span>
                    <span x-show="running">Memproses...</span>
                </x-filament::button>

                <x-filament::button color="gray" wire:click="resetImport" class="ml-2">
                    Reset
                </x-filament::button>
            </div>
            @endif
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>