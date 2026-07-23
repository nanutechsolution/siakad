<div class="space-y-4">
    @if (! $batch)
        <p class="text-sm text-gray-500">Belum ada hasil migrasi.</p>
    @else
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-lg border p-4">
                <p class="text-xs text-gray-500">Status</p>
                <p class="text-xl font-semibold">{{ $batch->status->label() }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-xs text-gray-500">Waktu Proses</p>
                <p class="text-xl font-semibold">
                    {{ $batch->execution_time_seconds !== null ? $batch->execution_time_seconds . ' detik' : '-' }}
                </p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-xs text-gray-500">Berhasil</p>
                <p class="text-xl font-semibold text-success-600">{{ $batch->total_berhasil }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-xs text-gray-500">Gagal</p>
                <p class="text-xl font-semibold text-danger-600">{{ $batch->total_gagal }}</p>
            </div>
        </div>

        <x-filament::button
            tag="a"
            href="{{ route('migration.batches.error-report', $batch) }}"
            target="_blank"
        >
            Unduh Laporan Kesalahan
        </x-filament::button>
    @endif
</div>