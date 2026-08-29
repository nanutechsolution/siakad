{{-- resources/views/filament/clusters/manajemen-kelas/pages/generate-kelas-page.blade.php --}}
<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button wire:click="hitungPreview" color="gray" icon="heroicon-o-calculator">
                Hitung Preview
            </x-filament::button>
        </div>
    </form>

    @if ($previewGenerated)
    <div class="mt-6 space-y-4">

        {{-- Ringkasan kapasitas per kelas --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($this->ringkasanKelas as $k)
            <div class="rounded-xl border p-3 bg-white dark:bg-gray-900
                        {{ match($k['status']) {
                            'danger' => 'border-danger-400',
                            'warning' => 'border-warning-400',
                            default => 'border-gray-200 dark:border-gray-700',
                        } }}">
                <div class="text-sm font-medium text-gray-950 dark:text-white">{{ $k['nama'] }}</div>
                <div class="mt-1 flex items-center gap-2">
                    <x-filament::badge :color="$k['status']">
                        {{ $k['jumlah'] }}{{ $k['kapasitas'] ? '/' . $k['kapasitas'] : '' }}
                    </x-filament::badge>
                    @if ($k['status'] === 'danger')
                    <span class="text-xs text-danger-600">melebihi kapasitas</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @if ($this->adaKelasPenuh)
        <div class="rounded-lg bg-danger-50 dark:bg-danger-500/10 p-3 text-sm text-danger-700 dark:text-danger-400">
            ⚠️ Ada kelas yang melebihi kapasitas. Geser sebagian mahasiswa ke kelas lain sebelum generate.
        </div>
        @endif

        <div class="flex items-center justify-between gap-3">
            <x-filament::input.wrapper class="max-w-xs">
                <x-filament::input
                    type="text"
                    wire:model.live.debounce.300ms="cariMahasiswa"
                    placeholder="Cari nama atau NIM..." />
            </x-filament::input.wrapper>

            <x-filament::button wire:click="seimbangkanUlang" color="gray" size="sm" icon="heroicon-o-arrow-path">
                Seimbangkan Ulang
            </x-filament::button>
        </div>

        {{-- Tabel mahasiswa dengan select kelas tujuan --}}
        {{-- Papan Kanban: kolom per kelas, kartu mahasiswa bisa digeser antar kolom --}}
        <div
            x-data="{ draggedId: null, draggedFrom: null, overColumn: null }"
            x-on:mahasiswa-dipindah.window="
        $tooltip ?? null;
        window.dispatchEvent(new CustomEvent('filament-notify', {
            detail: { title: $event.detail.nama + ' dipindah ke ' + $event.detail.tujuan }
        }))
    "
            class="grid gap-3"
            style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            @foreach ($this->labelKelas as $label => $namaKelas)
            @php
            $ringkasan = collect($this->ringkasanKelas)->firstWhere('label', $label);
            $kartu = $this->mahasiswaPerKolom[$label] ?? [];
            @endphp

            <div
                x-on:dragover.prevent="overColumn = '{{ $label }}'"
                x-on:dragleave="overColumn = (overColumn === '{{ $label }}') ? null : overColumn"
                x-on:drop.prevent="
                overColumn = null;
                if (draggedId) { $wire.pindahkanMahasiswa(draggedId, '{{ $label }}') }
            "
                :class="overColumn === '{{ $label }}' ? 'ring-2 ring-primary-400' : ''"
                class="flex flex-col rounded-xl border bg-gray-50 dark:bg-gray-800/50 transition-all
                {{ match($ringkasan['status'] ?? 'success') {
                    'danger' => 'border-danger-400',
                    'warning' => 'border-warning-400',
                    default => 'border-gray-200 dark:border-gray-700',
                } }}">
                {{-- Header kolom --}}
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-3 py-2">
                    <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $namaKelas }}</span>
                    <x-filament::badge :color="$ringkasan['status'] ?? 'success'">
                        {{ $ringkasan['jumlah'] ?? 0 }}{{ ($ringkasan['kapasitas'] ?? null) ? '/' . $ringkasan['kapasitas'] : '' }}
                    </x-filament::badge>
                </div>

                {{-- Kartu mahasiswa --}}
                <div class="flex flex-col gap-2 p-2 min-h-[4rem] max-h-[26rem] overflow-y-auto">
                    @forelse ($kartu as $m)
                    <div
                        wire:key="kartu-{{ $m['id'] }}"
                        draggable="true"
                        x-on:dragstart="draggedId = '{{ $m['id'] }}'; $el.style.opacity = 0.4"
                        x-on:dragend="$el.style.opacity = 1; draggedId = null"
                        x-data="{ menuTerbuka: false }"
                        class="relative cursor-grab active:cursor-grabbing rounded-lg border border-gray-200 dark:border-gray-700
               bg-white dark:bg-gray-900 px-3 py-2 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-950 dark:text-white truncate">{{ $m['nama'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $m['nim'] }}</div>
                            </div>

                            {{-- Tombol pindah cepat — fallback untuk touch screen, juga berguna di desktop --}}
                            <div class="relative shrink-0" x-on:click.outside="menuTerbuka = false">
                                <button
                                    type="button"
                                    x-on:click="menuTerbuka = !menuTerbuka"
                                    class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600
                           dark:hover:bg-gray-800 dark:hover:text-gray-300"
                                    aria-label="Pindahkan mahasiswa">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                        <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 14a1.5 1.5 0 110 3 1.5 1.5 0 010-3z" />
                                    </svg>
                                </button>

                                <div
                                    x-show="menuTerbuka"
                                    x-transition
                                    x-cloak
                                    class="absolute right-0 z-10 mt-1 w-40 rounded-lg border border-gray-200 dark:border-gray-700
                           bg-white dark:bg-gray-900 py-1 shadow-lg">
                                    <div class="px-3 py-1.5 text-[11px] font-medium uppercase tracking-wide text-gray-400">
                                        Pindah ke
                                    </div>
                                    @foreach ($this->labelKelas as $labelTujuan => $namaTujuan)
                                    @if ($labelTujuan !== $label)
                                    <button
                                        type="button"
                                        x-on:click="menuTerbuka = false; $wire.pindahkanMahasiswa('{{ $m['id'] }}', '{{ $labelTujuan }}')"
                                        class="block w-full px-3 py-1.5 text-left text-sm text-gray-700 hover:bg-gray-50
                                       dark:text-gray-200 dark:hover:bg-gray-800">
                                        {{ $namaTujuan }}
                                    </button>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="flex items-center justify-center h-16 text-xs text-gray-400 dark:text-gray-600 border border-dashed rounded-lg">
                        Seret mahasiswa ke sini, atau pakai menu titik-tiga
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex justify-end">
            {{ $this->generateAction() }}
        </div>
    </div>
    @endif
</x-filament-panels::page>