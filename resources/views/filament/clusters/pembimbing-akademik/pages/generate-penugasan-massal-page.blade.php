<x-filament-panels::page>

    {{-- ============ STEPPER ============ --}}
    <div class="flex items-center mb-6">
        @foreach ($this->getSteps() as $stepNumber => $label)
        <div class="flex items-center gap-2 text-sm font-medium
                {{ $stepNumber === $currentStep ? 'text-primary-600 dark:text-primary-400' : ($stepNumber < $currentStep ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-600') }}">
            <span
                @class([ 'flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold ring-1 ring-inset' , 'bg-primary-600 text-white ring-primary-600'=> $stepNumber === $currentStep,
                'bg-success-600 text-white ring-success-600' => $stepNumber < $currentStep, 'bg-white text-gray-400 ring-gray-300 dark:bg-gray-800 dark:ring-gray-700'=> $stepNumber > $currentStep,
                    ])
                    >
                    @if ($stepNumber
                    < $currentStep)
                        <x-heroicon-m-check class="h-3.5 w-3.5" />
                    @else
                    {{ $stepNumber }}
                    @endif
            </span>
            <span>{{ $label }}</span>
        </div>

        @if (! $loop->last)
        <div @class([ 'mx-3 h-px flex-1' , 'bg-success-500'=> $stepNumber < $currentStep, 'bg-gray-200 dark:bg-gray-700'=> $stepNumber >= $currentStep,
                ])></div>
        @endif
        @endforeach
    </div>

    {{-- ============ STEP 1 & 2 : FORM + LIVE IMPACT PANEL ============ --}}
    @if ($currentStep === 1 || $currentStep === 2)
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-filament::section>
                <x-slot name="heading">
                    {{ $currentStep === 1 ? 'Ruang Lingkup' : 'Distribusi Dosen' }}
                </x-slot>
                <x-slot name="description">
                    {{ $currentStep === 1
                            ? 'Tentukan program studi dan angkatan yang akan diproses.'
                            : 'Pilih dosen yang akan dilibatkan beserta detail SK penugasan.' }}
                </x-slot>

                {{ $this->form }}
            </x-filament::section>
        </div>

        {{-- Live impact panel: menetap di sisi kanan, bukan placeholder statis --}}
        <div class="lg:col-span-1">
            <x-filament::section class="lg:sticky lg:top-6">
                @if (! $konfigurasi)
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-information-circle class="h-5 w-5 shrink-0" />
                    <span>Pilih program studi & angkatan untuk melihat dampak penugasan.</span>
                </div>
                @elseif ($konfigurasi['sisa'] === 0)
                <x-filament::badge color="success" icon="heroicon-m-check-circle">
                    Semua {{ $konfigurasi['satuan'] }} sudah punya wali
                </x-filament::badge>
                @else
                <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle">
                    Konfigurasi aktif · {{ $konfigurasi['mode_label'] }}
                </x-filament::badge>

                <div
                    class="mt-4"
                    x-data="{ display: 0 }"
                    x-init="
                                let target = {{ $konfigurasi['sisa'] }};
                                let start = performance.now();
                                let duration = 700;
                                const step = (now) => {
                                    let p = Math.min((now - start) / duration, 1);
                                    display = Math.round(target * (1 - Math.pow(1 - p, 3)));
                                    if (p < 1) requestAnimationFrame(step);
                                };
                                requestAnimationFrame(step);
                            ">
                    <span class="text-5xl font-semibold tabular-nums text-gray-950 dark:text-white" x-text="display"></span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $konfigurasi['satuan'] }} belum memiliki Dosen Wali aktif
                </p>

                <dl class="mt-4 space-y-2 border-t border-gray-100 pt-4 text-sm dark:border-white/10">
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Mode pembimbingan</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $konfigurasi['mode_label'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Total {{ $konfigurasi['satuan'] }}</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $konfigurasi['total'] }}</dd>
                    </div>
                </dl>
                @endif

                {{-- Perkiraan distribusi beban, muncul begitu dosen dicentang --}}
                @if ($currentStep === 2 && count($distribusi) > 0)
                <p class="mb-2 mt-6 text-xs font-semibold text-gray-500 dark:text-gray-400">
                    Perkiraan distribusi beban
                </p>
                @php $maxJumlah = max(array_column($distribusi, 'jumlah')) ?: 1; @endphp
                <div class="space-y-2.5">
                    @foreach ($distribusi as $d)
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-warning-50 text-[10px] font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                            {{ collect(explode(' ', $d['nama']))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}
                        </span>
                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-full rounded-full bg-warning-500" style="width: {{ round(($d['jumlah'] / $maxJumlah) * 100) }}%"></div>
                        </div>
                        <span class="w-6 text-right text-xs font-medium tabular-nums text-gray-700 dark:text-gray-300">
                            {{ $d['jumlah'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-filament::section>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-between">
        <x-filament::button color="gray" tag="a" href="{{ static::getUrl(panel: filament()->getCurrentPanel()->getId()) }}">
            Batal
        </x-filament::button>

        @if ($currentStep === 1)
        <x-filament::button wire:click="goToStep(2)">
            Lanjut ke Distribusi Dosen
        </x-filament::button>
        @else
        <div class="flex gap-2">
            <x-filament::button color="gray" wire:click="goToStep(1)">
                Kembali
            </x-filament::button>
            <x-filament::button color="warning" wire:click="goToStep(3)">
                Buat Preview
            </x-filament::button>
        </div>
        @endif
    </div>
    @endif

    {{-- ============ STEP 3 : PREVIEW (dikelompokkan per dosen) ============ --}}
    @if ($currentStep === 3 && ! $isProcessing)
    <x-filament::section>
        <x-slot name="heading">Preview Penugasan</x-slot>
        <x-slot name="description">
            Periksa hasil pembagian sebelum diproses. Anda masih bisa mengganti dosen pada baris tertentu.
        </x-slot>

        {{--
            wire:loading + wire:target="reassignTarget" mengunci interaksi (drag/dropdown)
            selama request reassignTarget masih diproses server, supaya user tidak bisa
            memicu drag/klik baru yang bisa balapan (race condition) dengan request
            sebelumnya pada koneksi lambat.
        --}}
        <div
            x-data="{
        query: '',
        dosenOptions: @js($dosenOptions),
    }"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="reassignTarget"
            class="space-y-3"
            wire:key="preview-groups-wrapper">
            <div class="flex items-center justify-between gap-3">
                <x-filament::input.wrapper class="max-w-sm">
                    <x-filament::input type="text" x-model="query" placeholder="Cari kelas / mahasiswa..." />
                </x-filament::input.wrapper>
                <p class="hidden text-xs text-gray-400 dark:text-gray-500 sm:block">
                    Pencarian hanya berlaku untuk baris yang sedang ditampilkan — klik "Tampilkan lebih banyak" dulu kalau target yang dicari belum kelihatan. Seret baris ke grup dosen lain untuk memindahkan, atau pakai menu di kanan tiap baris.
                </p>
            </div>

            @foreach ($previewGrouped as $group)
            @php
            $limit = $groupRenderLimit[$group['dosen_id']] ?? $this::PREVIEW_ROW_CHUNK;
            $visibleRows = array_slice($group['rows'], 0, $limit);
            $sisaRows = count($group['rows']) - count($visibleRows);
            @endphp
            {{--
                x-show grup TIDAK lagi bergantung pada array `labels` yang di-snapshot
                sekali saat Alpine init (bug lama: labels bisa basi setelah drag & drop
                memindahkan baris ke grup ini, karena wire:key grup stabil sehingga
                Alpine tidak re-init x-data). Sekarang di-scan langsung dari DOM
                ($el.querySelectorAll) setiap kali dievaluasi, jadi selalu sinkron
                dengan hasil render Livewire terkini.
            --}}
            <div
                x-data="{ open: true }"
                x-show="query === '' || Array.from($el.querySelectorAll('[data-index]')).some((li) => li.dataset.label.includes(query.toLowerCase()))"
                class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10"
                wire:key="preview-group-{{ $group['dosen_id'] }}">
                <button
                    type="button"
                    @click="open = !open"
                    class="flex w-full items-center justify-between bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-950 dark:bg-white/5 dark:text-white">
                    <span class="flex items-center gap-2">
                        {{ $group['nama'] }}
                        <x-filament::badge color="gray" size="sm">{{ count($group['rows']) }} target</x-filament::badge>
                    </span>
                    <x-heroicon-m-chevron-down class="h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': ! open }" />
                </button>

                {{-- data-dosen-group menandai elemen ini sebagai satu Sortable list;
                             semua list berbagi group name "preview-targets" sehingga baris
                             bisa di-drag lintas dosen. Lihat script di bawah. --}}
                <ul
                    x-show="open"
                    data-dosen-group="{{ $group['dosen_id'] }}"
                    class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($visibleRows as $row)
                    <li
                        wire:key="preview-row-{{ $row['index'] }}"
                        data-index="{{ $row['index'] }}"
                        data-label="{{ strtolower($row['target_label']) }}"
                        x-show="query === '' || $el.dataset.label.includes(query.toLowerCase())"
                        class="drag-row flex cursor-grab items-center gap-3 bg-white px-4 py-2 text-sm active:cursor-grabbing dark:bg-gray-900">
                        <span class="drag-handle shrink-0 text-gray-300 dark:text-gray-600">
                            <x-heroicon-m-bars-2 class="h-4 w-4" />
                        </span>
                        <span class="flex-1 text-gray-700 dark:text-gray-300">{{ $row['target_label'] }}</span>
                        {{--
                            wire:key di sini sengaja disertakan dosen_id-nya. Kalau
                            dosen_id baris ini berubah (lewat dropdown ini sendiri atau
                            drag & drop), key berubah -> Livewire dipaksa membuat ulang
                            elemen ini dari HTML server terbaru, sehingga dropdown TIDAK
                            PERNAH menampilkan nilai basi walau ada morphing DOM lintas
                            grup dosen.
                        --}}
                        <div
                            wire:key="select-{{ $row['index'] }}-{{ $row['dosen_id'] }}"
                            x-data="{
        selected: @js($row['dosen_id']),
    }"
                            class="w-56">
                            <select
                                x-model="selected"
                                @change="$wire.reassignTarget({{ $row['index'] }}, selected)"
                                class="fi-select-input w-full rounded-lg border-gray-300 text-xs shadow-sm dark:border-gray-600 dark:bg-gray-700">
                                <template x-for="[id, label] in Object.entries(dosenOptions)" :key="id">
                                    <option
                                        :value="id"
                                        x-text="label"></option>
                                </template>
                            </select>
                        </div>
                    </li>
                    @empty
                    <li class="no-drag px-4 py-6 text-center text-xs text-gray-400 dark:text-gray-600">
                        Belum ada target — seret baris ke sini untuk memindahkan.
                    </li>
                    @endforelse
                </ul>

                @if ($sisaRows > 0)
                <button
                    type="button"
                    wire:click="expandGroupRows({{ $group['dosen_id'] }})"
                    class="no-drag w-full border-t border-gray-100 px-4 py-2 text-center text-xs font-medium text-primary-600 hover:bg-gray-50 dark:border-white/10 dark:text-primary-400 dark:hover:bg-white/5">
                    Tampilkan {{ min($sisaRows, $this::PREVIEW_ROW_CHUNK) }} baris lagi ({{ $sisaRows }} belum ditampilkan)
                </button>
                @endif
            </div>
            @endforeach
        </div>
    </x-filament::section>

    @once
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    @endpush
    @endonce

    <script>
        document.addEventListener('livewire:init', () => {
            const mountDragGroups = (root = document) => {
                root.querySelectorAll('[data-dosen-group]').forEach((el) => {
                    if (el._sortableBound) return;
                    el._sortableBound = true;

                    new Sortable(el, {
                        group: 'preview-targets',
                        animation: 150,
                        handle: '.drag-handle',
                        filter: '.no-drag',
                        ghostClass: 'opacity-40',
                        onAdd: (evt) => {
                            const index = evt.item.dataset.index;
                            const dosenId = evt.to.dataset.dosenGroup;

                            if (index === undefined) return;

                            @this.reassignTarget(parseInt(index), parseInt(dosenId));
                        },
                    });
                });
            };

            mountDragGroups();

            // Livewire mem-morph ulang markup tiap kali properti berubah
            // (mis. setelah reassignTarget). Elemen yang wire:key-nya sama
            // dipertahankan (jadi binding Sortable lama tetap valid), tapi
            // grup/baris baru yang belum pernah ada perlu di-mount ulang.
            Livewire.hook('morph.updated', ({
                el
            }) => mountDragGroups(el));
        });
    </script>

    <div class="mt-6 flex items-center justify-between">
        <x-filament::button color="gray" wire:click="goToStep(2)">
            Kembali
        </x-filament::button>

        {{--
            Guard `running` mencegah double-submit: klik ganda/cepat pada tombol
            Proses tidak lagi memicu dua loop run() berjalan bersamaan (yang bisa
            membuat processBatch() dipanggil paralel dan merusak counter $processed).
        --}}
        <div
            x-data="{
                    running: false,
                    async run() {
                        if (this.running) return;
                        this.running = true;
                        try {
                            await $wire.startProcessing();
                            let done = false;
                            while (! done) {
                                let result = await $wire.processBatch(10);
                                done = result.done;
                            }
                        } finally {
                            this.running = false;
                        }
                    },
                }">
            <x-filament::button color="warning" x-on:click="run()" x-bind:disabled="running">
                <span x-show="! running">Proses {{ count($preview) }} Penugasan Ini</span>
                <span x-show="running" x-cloak>Memproses...</span>
            </x-filament::button>
        </div>
    </div>
    @endif

    {{-- ============ STEP 3b : EKSEKUSI ============ --}}
    @if ($isProcessing)
    <x-filament::section class="mx-auto max-w-xl text-center">
        <x-slot name="heading">Memproses penugasan…</x-slot>
        <x-slot name="description">Jangan tutup halaman ini sampai proses selesai.</x-slot>

        @php $total = count($preview) ?: 1; @endphp
        <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
            <div
                class="h-full rounded-full bg-warning-500 transition-all duration-300"
                style="width: {{ round(($processed / $total) * 100) }}%"></div>
        </div>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ $processed }} dari {{ count($preview) }} diproses
            @if ($totalGagal > 0)
            · {{ $totalGagal }} dilewati
            @endif
        </p>

        <div class="mt-4 max-h-40 space-y-1 overflow-y-auto rounded-lg border border-gray-100 p-3 text-left font-mono text-xs dark:border-white/10">
            @foreach (array_reverse($liveFeed) as $entry)
            <div class="{{ $entry['status'] === 'ok' ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                {{ $entry['status'] === 'ok' ? '✓' : '✕' }} {{ $entry['label'] }}
            </div>
            @endforeach
        </div>

        <x-filament::button color="gray" class="mt-4" wire:click="stopProcessing" wire:confirm="Sisa target yang belum diproses akan dibatalkan. Penugasan yang sudah dibuat tidak akan dibatalkan. Lanjutkan menghentikan?">
            Hentikan sisa proses
        </x-filament::button>
    </x-filament::section>
    @endif

    {{-- ============ STEP 4 : SELESAI ============ --}}
    @if ($currentStep === 4)
    <x-filament::section class="mx-auto max-w-xl text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
            <x-heroicon-o-check class="h-7 w-7 text-success-600 dark:text-success-400" />
        </div>
        <h2 class="mt-4 text-xl font-semibold text-gray-950 dark:text-white">Generate massal selesai</h2>

        <div class="mt-6 flex justify-center gap-10">
            <div>
                <p class="text-3xl font-semibold text-success-600 dark:text-success-400">{{ count($preview) - $totalGagal }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Berhasil dibuat</p>
            </div>
            <div>
                <p class="text-3xl font-semibold text-danger-600 dark:text-danger-400">{{ $totalGagal }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Dilewati</p>
            </div>
        </div>

        <div class="mt-6 flex justify-center gap-2">
            <x-filament::button color="gray" wire:click="resetGenerate">
                Generate Lagi
            </x-filament::button>
        </div>
    </x-filament::section>
    @endif

</x-filament-panels::page>