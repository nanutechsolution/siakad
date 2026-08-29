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
        <div class="max-h-[28rem] overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-2">Nama</th>
                        <th class="px-3 py-2">NIM</th>
                        <th class="px-3 py-2">Kelas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($this->daftarMahasiswaTersaring as $m)
                    <tr wire:key="mhs-{{ $m['id'] }}">
                        <td class="px-3 py-2 text-gray-950 dark:text-white">{{ $m['nama'] }}</td>
                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $m['nim'] }}</td>
                        <td class="px-3 py-2">
                            <select
                                wire:model.live="distribusi.{{ $m['id'] }}"
                                class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                                @foreach ($labelKelas as $label => $nama)
                                <option value="{{ $label }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            {{ $this->generateAction() }}
        </div>
    </div>
    @endif
</x-filament-panels::page>