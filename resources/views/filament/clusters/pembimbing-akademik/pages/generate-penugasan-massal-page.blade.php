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

        <div
            x-data="{ query: '' }"
            class="space-y-3">
            <x-filament::input.wrapper class="max-w-sm">
                <x-filament::input type="text" x-model="query" placeholder="Cari kelas / mahasiswa..." />
            </x-filament::input.wrapper>

            @foreach ($previewGrouped as $group)
            <div
                x-data="{ open: true }"
                x-show="query === '' || {{ collect($group['rows'])->pluck('target_label')->map(fn ($l) => "'" . strtolower($l) . "'.includes(query.toLowerCase())")->implode(' || ') }}"
                class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
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

                <table x-show="open" class="w-full text-sm">
                    <tbody>
                        @foreach ($group['rows'] as $row)
                        @php $index = array_search($row, $preview, true); @endphp
                        <tr
                            x-show="query === '' || '{{ strtolower($row['target_label']) }}'.includes(query.toLowerCase())"
                            class="border-t border-gray-100 dark:border-white/5">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row['target_label'] }}</td>
                            <td class="w-64 px-4 py-2">
                                <select
                                    class="fi-select-input block w-full rounded-lg border-gray-300 text-xs shadow-sm dark:border-gray-600 dark:bg-gray-700"
                                    wire:change="reassignTarget({{ $index }}, $event.target.value)">
                                    @foreach ($dosenOptions as $id => $label)
                                    <option value="{{ $id }}" @selected($row['dosen_id']==$id)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
    </x-filament::section>

    <div class="mt-6 flex items-center justify-between">
        <x-filament::button color="gray" wire:click="goToStep(2)">
            Kembali
        </x-filament::button>

        <div
            x-data="{
                    async run() {
                        await $wire.startProcessing();
                        let done = false;
                        while (! done) {
                            let result = await $wire.processBatch(10);
                            done = result.done;
                        }
                    },
                }">
            <x-filament::button color="warning" x-on:click="run()">
                Proses {{ count($preview) }} Penugasan Ini
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