<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Langkah 1 &amp; 2 — Ruang Lingkup &amp; Distribusi Dosen</x-slot>

            <form wire:submit.prevent="generatePreview">
                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button type="submit" icon="heroicon-o-eye">
                        Buat Preview Distribusi
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        @if ($previewGenerated)
        <x-filament::section>
            <x-slot name="heading">Langkah 3 — Preview &amp; Terapkan</x-slot>

            @if (empty($preview))
            <div class="text-center py-10 text-gray-500">
                <p class="font-medium">Tidak ada target tersisa 🎉</p>
                <p class="text-sm">Semua kelas/mahasiswa pada kombinasi ini sudah memiliki Dosen Wali aktif.</p>
            </div>
            @else
            <div class="overflow-x-auto mb-4 max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-white dark:bg-gray-900">
                        <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-2">Target</th>
                            <th class="py-2 pr-2">Dosen Wali</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview as $i => $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-1 pr-2">{{ $row['target_label'] }}</td>
                            <td class="py-1 pr-2">
                                <select
                                    wire:model="preview.{{ $i }}.dosen_id"
                                    class="fi-select-input block w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 text-sm"
                                    @if ($processed> 0) disabled @endif
                                    >
                                    @foreach ($row['dosen_options'] as $dosenId => $label)
                                    <option value="{{ $dosenId }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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
                    <span>Progres: {{ $processed }} / {{ count($preview) }}</span>
                    <span>{{ count($preview) > 0 ? round(($processed / count($preview)) * 100) : 0 }}%</span>
                </div>

                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden mb-4">
                    <div
                        class="bg-primary-600 h-3 transition-all duration-300"
                        style="width: {{ count($preview) > 0 ? round(($processed / count($preview)) * 100) : 0 }}%"></div>
                </div>

                <x-filament::button
                    x-on:click="start()"
                    x-bind:disabled="running || {{ $processed >= count($preview) ? 'true' : 'false' }}"
                    icon="heroicon-o-play">
                    <span x-show="! running">Terapkan ke {{ count($preview) }} Target</span>
                    <span x-show="running">Memproses...</span>
                </x-filament::button>

                <x-filament::button color="gray" wire:click="resetGenerate" class="ml-2">
                    Reset
                </x-filament::button>
            </div>
            @endif
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>