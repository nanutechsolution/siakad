@php
    $tahunAkademik = \App\Models\RefTahunAkademik::query()
        ->where('is_active', true)
        ->first();
@endphp

@if ($tahunAkademik)
    <div class="flex items-center gap-x-2 text-sm text-gray-600 dark:text-gray-400">
        <x-heroicon-m-academic-cap class="h-5 w-5 text-primary-500" />
        <span>Tahun Akademik</span>

        <span class="font-semibold text-gray-950 dark:text-white">
            {{ $tahunAkademik->nama_tahun }}
        </span>
    </div>
@endif