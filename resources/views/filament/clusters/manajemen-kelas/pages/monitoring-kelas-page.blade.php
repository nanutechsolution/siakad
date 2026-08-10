<x-filament-panels::page>

    {{-- =========================================================
        RINGKASAN
    ========================================================== --}}

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mb-6">

        {{-- Total Kelas --}}
        <x-filament::section>
            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Kelas
                    </div>

                    <div class="mt-1 text-3xl font-bold text-gray-950 dark:text-white">
                        {{ number_format($this->getTotalKelas()) }}
                    </div>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 dark:bg-primary-500/10">
                    <x-heroicon-o-academic-cap
                        class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                </div>

            </div>
        </x-filament::section>


        {{-- Mahasiswa Tanpa Kelas --}}
        <x-filament::section>
            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Mahasiswa Belum Punya Kelas
                    </div>

                    <div class="mt-1 text-3xl font-bold text-danger-600">
                        {{ number_format($this->getTotalMahasiswaTanpaKelas()) }}
                    </div>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-danger-50 dark:bg-danger-500/10">
                    <x-heroicon-o-user-group
                        class="h-6 w-6 text-danger-600 dark:text-danger-400" />
                </div>

            </div>
        </x-filament::section>

    </div>


    {{-- =========================================================
        KAPASITAS KELAS
    ========================================================== --}}

    <x-filament::section
        class="mb-6"
        collapsible>

        <x-slot name="heading">
            Kapasitas per Kelas
        </x-slot>

        <x-slot name="description">
            Monitoring jumlah mahasiswa dan kapasitas setiap kelas
            berdasarkan hak akses Anda.
        </x-slot>


        @php
        $daftarKelas = $this->getKapasitasKelas();
        @endphp


        @if ($daftarKelas->isEmpty())

        <div class="py-8 text-center">

            <x-heroicon-o-academic-cap
                class="mx-auto h-10 w-10 text-gray-400" />

            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Belum ada kelas yang sesuai dengan filter.
            </p>

        </div>

        @else

        <div class="space-y-5">

            @foreach ($daftarKelas as $item)

            <div
                class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">

                {{-- Header --}}
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                    <div class="min-w-0">

                        <div class="flex items-center gap-2">

                            <span class="font-semibold text-gray-950 dark:text-white">
                                {{ $item['nama'] }}
                            </span>

                            @if ($item['penuh'])

                            <x-filament::badge color="danger">
                                Penuh
                            </x-filament::badge>

                            @elseif (
                            $item['persen'] !== null &&
                            $item['persen'] >= 80
                            )

                            <x-filament::badge color="warning">
                                Hampir Penuh
                            </x-filament::badge>

                            @else

                            <x-filament::badge color="success">
                                Tersedia
                            </x-filament::badge>

                            @endif

                        </div>


                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">

                            {{ $item['prodi'] }}

                            <span class="mx-1">
                                •
                            </span>

                            Angkatan {{ $item['angkatan'] }}

                        </div>

                    </div>


                    {{-- Jumlah --}}
                    <div class="text-sm sm:text-right">

                        <span class="font-semibold text-gray-950 dark:text-white">
                            {{ $item['jumlah'] }}
                        </span>

                        @if ($item['kapasitas'] !== null)

                        <span class="text-gray-500 dark:text-gray-400">
                            / {{ $item['kapasitas'] }}
                        </span>

                        @else

                        <span class="text-gray-500 dark:text-gray-400">
                            mahasiswa
                        </span>

                        @endif

                    </div>

                </div>


                {{-- Progress --}}
                @if ($item['persen'] !== null)

                <div class="mt-3">

                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">

                        <div
                            class="h-2 rounded-full transition-all
                                            {{ $item['penuh']
                                                ? 'bg-danger-600'
                                                : ($item['persen'] >= 80
                                                    ? 'bg-warning-500'
                                                    : 'bg-success-500') }}"
                            style="width: {{ $item['persen'] }}%"></div>

                    </div>


                    <div class="mt-1 flex justify-between text-xs">

                        <span class="text-gray-500 dark:text-gray-400">
                            Terisi {{ $item['persen'] }}%
                        </span>

                        @if ($item['penuh'])

                        <span class="font-medium text-danger-600">
                            Kelas penuh
                        </span>

                        @else

                        <span class="text-gray-500 dark:text-gray-400">
                            Sisa {{ $item['sisa'] }}
                        </span>

                        @endif

                    </div>

                </div>

                @else

                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Kapasitas kelas tidak dibatasi.
                </div>

                @endif

            </div>

            @endforeach

        </div>

        @endif

    </x-filament::section>


    {{-- =========================================================
        MAHASISWA BELUM PUNYA KELAS
    ========================================================== --}}

    {{ $this->table }}

</x-filament-panels::page>