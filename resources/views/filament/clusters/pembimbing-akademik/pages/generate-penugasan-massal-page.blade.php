<x-filament-panels::page>
    <!-- Form Area -->
    <form wire:submit.prevent="generatePreview" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-end">
            <!-- Loading Indicator Mencegah Double Submit -->
            <x-filament::button 
                type="submit" 
                icon="heroicon-o-sparkles" 
                size="lg"
                wire:loading.attr="disabled"
                wire:target="generatePreview"
            >
                <span wire:loading.remove wire:target="generatePreview">Buat Preview Distribusi</span>
                <span wire:loading wire:target="generatePreview" class="flex items-center gap-2">
                    Menyiapkan Data...
                </span>
            </x-filament::button>
        </div>
    </form>

    <!-- Preview Area -->
    @if ($previewGenerated)
    <div id="preview-section" class="mt-8 animate-fade-in" x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })">
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                    <span>3. Preview & Terapkan Penugasan</span>
                </div>
            </x-slot>
            <x-slot name="description">
                Gunakan pencarian untuk merubah dosen pada mahasiswa tertentu. Periksa ringkasan beban sebelum diterapkan.
            </x-slot>

            @if (empty($preview))
            <div class="flex flex-col items-center justify-center py-12 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-12 w-12 text-success-500 mb-3" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tidak ada target tersisa</h3>
                <p class="text-sm text-center max-w-sm mt-1">Semua kelas atau mahasiswa pada kombinasi ini sudah memiliki Dosen Wali aktif.</p>
            </div>
            @else
            
            <!-- Workload Summary (Ringkasan Beban Real-time) -->
            <div class="mb-6 bg-primary-50 dark:bg-primary-500/10 rounded-xl p-4 border border-primary-200 dark:border-primary-500/20">
                <h4 class="text-sm font-semibold text-primary-800 dark:text-primary-300 mb-3 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-chart-pie" class="h-4 w-4" />
                    Estimasi Pembagian Beban Dosen
                </h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($this->getWorkloadSummary() as $summary)
                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white ring-1 ring-inset ring-gray-200 dark:ring-gray-700 shadow-sm">
                            <x-filament::icon icon="heroicon-m-user" class="h-4 w-4 text-gray-400" />
                            {{ $summary['nama'] }}
                            <span class="ml-1 rounded-full bg-primary-100 dark:bg-primary-900 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:text-primary-300 transition-all duration-300">
                                {{ $summary['count'] }} Target
                            </span>
                        </span>
                    @endforeach
                </div>
            </div>

            <!-- Tabel Data dengan Alpine.js untuk Filter Pencarian Instan -->
            <div x-data="{ searchQuery: '' }" class="mb-8">
                
                <!-- Search Bar UI -->
                <div class="mb-4 max-w-md">
                    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                        <x-filament::input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Cari NIM atau Nama Mahasiswa..."
                        />
                    </x-filament::input.wrapper>
                </div>

                <!-- Table -->
                <div class="ring-1 ring-gray-200 dark:ring-white/10 rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto max-h-[32rem] overflow-y-auto relative">
                        <table class="w-full text-sm text-left divide-y divide-gray-200 dark:divide-white/5">
                            <thead class="bg-gray-50 dark:bg-white/5 sticky top-0 z-10 backdrop-blur-md shadow-sm">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-medium text-gray-900 dark:text-white">Nama Target (Kelas/Mahasiswa)</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-gray-900 dark:text-white w-1/2">Ditugaskan Kepada (Dosen Wali)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                                @foreach ($preview as $i => $row)
                                <tr 
                                    class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75"
                                    x-show="searchQuery === '' || '{{ strtolower(addslashes($row['target_label'])) }}'.includes(searchQuery.toLowerCase())"
                                >
                                    <td class="px-4 py-3 align-middle">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $row['target_label'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            Tipe: {{ $row['target_type'] === 'KELAS' ? 'Kelas' : 'Mahasiswa' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select
                                                wire:model.live="preview.{{ $i }}.dosen_id"
                                                :disabled="$processed > 0"
                                            >
                                                @foreach ($row['dosen_options'] as $dosenId => $label)
                                                    <option value="{{ $dosenId }}">{{ $label }}</option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Eksekusi Batch via Alpine JS -->
            <div
                class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-5 border border-gray-200 dark:border-gray-700 relative overflow-hidden"
                x-data="{
                    running: false,
                    networkError: false,
                    
                    async start() {
                        if (this.running) return;
                        
                        this.running = true;
                        this.networkError = false;
                        let done = false;
                        
                        // Proteksi tab tertutup
                        window.onbeforeunload = () => 'Proses generate sedang berjalan! Data bisa korup jika halaman ditutup. Yakin ingin keluar?';

                        try {
                            while (!done) {
                                const result = await $wire.processBatch(10);
                                done = result.done;
                            }
                            this.running = false;
                            window.onbeforeunload = null;
                        } catch (error) {
                            this.running = false;
                            this.networkError = true;
                            window.onbeforeunload = null;
                            console.error('Terjadi kesalahan jaringan atau server saat memproses batch:', error);
                        }
                    },
                }"
            >
                <!-- Overlay pelindung saat eksekusi -->
                <div x-show="running" class="absolute inset-0 z-20 bg-gray-50/50 dark:bg-gray-900/50 cursor-not-allowed"></div>

                <!-- Progress Header -->
                <div class="mb-3 flex items-center justify-between text-sm font-medium relative z-30">
                    <span class="text-gray-700 dark:text-gray-300">
                        Progres Eksekusi: <span class="text-primary-600 dark:text-primary-400 font-bold" x-text="`${$wire.processed} / {{ count($preview) }}`"></span> target
                    </span>
                    <span class="text-gray-700 dark:text-gray-300 font-bold" x-text="`${Math.round(($wire.processed / {{ count($preview) }}) * 100)}%`"></span>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden relative shadow-inner mb-4 z-30">
                    <div
                        class="bg-primary-600 dark:bg-primary-500 h-4 transition-all duration-300 ease-out relative"
                        :style="`width: ${Math.round(($wire.processed / {{ count($preview) }}) * 100)}%`"
                    >
                        <div x-show="running" class="absolute inset-0 bg-white/20 animate-pulse"></div>
                    </div>
                </div>

                <!-- Error Warning -->
                <div x-show="networkError" style="display: none;" class="mb-4 rounded-lg bg-danger-50 dark:bg-danger-500/10 p-3 text-sm text-danger-700 dark:text-danger-400 border border-danger-200 dark:border-danger-500/20 flex gap-2 relative z-30">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 shrink-0" />
                    <span>Terjadi gangguan koneksi jaringan atau server lambat. Silakan klik <strong>Lanjutkan Eksekusi</strong> untuk meresume proses.</span>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 relative z-30">
                    <x-filament::button
                        x-on:click="start()"
                        x-bind:disabled="running || $wire.processed >= {{ count($preview) }}"
                        icon="heroicon-o-play"
                        size="lg"
                    >
                        <span x-show="!running && !networkError && $wire.processed === 0">Mulai Terapkan ke {{ count($preview) }} Target</span>
                        <span x-show="!running && (networkError || ($wire.processed > 0 && $wire.processed < {{ count($preview) }}))">Lanjutkan Eksekusi</span>
                        <span x-show="!running && $wire.processed >= {{ count($preview) }}">Proses Selesai</span>
                        <span x-show="running" class="flex items-center gap-2">
                            <x-filament::loading-indicator class="h-5 w-5" /> Sedang Memproses...
                        </span>
                    </x-filament::button>

                    <x-filament::button color="gray" wire:click="resetGenerate" x-bind:disabled="running" x-show="$wire.processed < {{ count($preview) }}">
                        Batal / Reset Data
                    </x-filament::button>
                </div>
            </div>
            @endif
        </x-filament::section>
    </div>
    @endif
</x-filament-panels::page>