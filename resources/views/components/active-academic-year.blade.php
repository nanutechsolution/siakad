@php
    $tahunAkademik = \App\Models\RefTahunAkademik::query()
        ->where('is_active', true)
        ->first();

    $semester = match ($tahunAkademik?->semester) {
        1 => 'Ganjil',
        2 => 'Genap',
        3 => 'Pendek',
        default => '',
    };
@endphp

@if ($tahunAkademik)
    <div
        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-900">

        {{-- Icon --}}
        <div class="flex h-6 w-6 items-center justify-center rounded-md bg-primary-50 dark:bg-primary-500/10">
            <x-heroicon-m-academic-cap class="h-4 w-4 text-primary-600" />
        </div>

        {{-- Tahun Akademik --}}
        <div class="flex items-center gap-2 whitespace-nowrap">
            <span class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white">
                {{ $tahunAkademik->nama_tahun }}
            </span>

            {{-- Status Aktif --}}
            <span class="flex items-center gap-1">
                <span class="h-2 w-2 rounded-full bg-success-500"></span>

                <span class="hidden sm:inline text-xs text-gray-500">
                    {{ $semester }}
                </span>
            </span>
        </div>

    </div>
@endif