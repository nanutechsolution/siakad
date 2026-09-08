@php
    $summary = $this->getSummaryData();
@endphp

<div class="mb-6 space-y-4">

    {{-- Header Informasi --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
                Ringkasan Plotting
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Statistik berdasarkan filter yang sedang digunakan
            </p>
        </div>

        <div class="flex items-center gap-2">

            <x-filament::badge
                color="info"
                icon="heroicon-m-calendar-days"
            >
                {{ optional(
                    \App\Models\RefTahunAkademik::find(
                        $this->getTahunAkademikAktifId()
                    )
                )->nama_tahun_akademik ?? 'Tahun Akademik Aktif' }}
            </x-filament::badge>

        </div>

    </div>


    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">

        {{-- Mata Kuliah --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Mata Kuliah
                    </p>

                    <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">
                        {{ number_format($summary['jumlah_mk']) }}
                    </p>
                </div>

                <div class="rounded-lg bg-primary-50 p-2.5 dark:bg-primary-500/10">
                    <x-heroicon-o-book-open class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                </div>

            </div>

            <p class="mt-3 text-xs text-gray-500">
                Mata kuliah sesuai filter
            </p>

        </div>


        {{-- Total SKS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total SKS
                    </p>

                    <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">
                        {{ number_format($summary['total_sks']) }}
                    </p>
                </div>

                <div class="rounded-lg bg-info-50 p-2.5 dark:bg-info-500/10">
                    <x-heroicon-o-academic-cap class="h-6 w-6 text-info-600 dark:text-info-400" />
                </div>

            </div>

            <p class="mt-3 text-xs text-gray-500">
                Beban SKS mata kuliah
            </p>

        </div>


        {{-- Kelas --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Kelas Dibuka
                    </p>

                    <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">
                        {{ number_format($summary['jumlah_kelas']) }}
                    </p>
                </div>

                <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                    <x-heroicon-o-building-office-2 class="h-6 w-6 text-success-600 dark:text-success-400" />
                </div>

            </div>

            <p class="mt-3 text-xs text-gray-500">
                Kelas yang sudah dibuka
            </p>

        </div>


        {{-- Dosen --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Dosen Terlibat
                    </p>

                    <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">
                        {{ number_format($summary['jumlah_dosen']) }}
                    </p>
                </div>

                <div class="rounded-lg bg-warning-50 p-2.5 dark:bg-warning-500/10">
                    <x-heroicon-o-users class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                </div>

            </div>

            <p class="mt-3 text-xs text-gray-500">
                Dosen pengampu aktif
            </p>

        </div>


        {{-- Sudah Diplot --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Sudah Diplot
                    </p>

                    <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">
                        {{ number_format($summary['sudah_diplot']) }}
                    </p>
                </div>

                <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                    <x-heroicon-o-check-circle class="h-6 w-6 text-success-600 dark:text-success-400" />
                </div>

            </div>

            <p class="mt-3 text-xs text-gray-500">
                {{ $summary['belum_diplot'] }} belum diplot
            </p>

        </div>


        {{-- Progress --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Progress
                    </p>

                    <p class="mt-1 text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $summary['persentase'] }}%
                    </p>
                </div>

                <div class="rounded-lg bg-primary-50 p-2.5 dark:bg-primary-500/10">
                    <x-heroicon-o-chart-bar class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                </div>

            </div>

            <div class="mt-3">
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">

                    <div
                        class="h-full rounded-full bg-primary-600 transition-all duration-500"
                        style="width: {{ min($summary['persentase'], 100) }}%"
                    ></div>

                </div>

                <div class="mt-1 flex justify-between text-xs text-gray-500">
                    <span>
                        {{ $summary['sudah_diplot'] }} selesai
                    </span>

                    <span>
                        {{ $summary['belum_diplot'] }} tersisa
                    </span>
                </div>
            </div>

        </div>

    </div>

</div>