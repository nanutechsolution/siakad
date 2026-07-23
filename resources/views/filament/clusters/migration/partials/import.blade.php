<div class="space-y-4" wire:poll.3s="$refresh">
    @if (! $batch)
        <p class="text-sm text-gray-500">Data belum siap. Selesaikan langkah sebelumnya.</p>
    @else
        <div class="rounded-lg border p-4 space-y-2">
            <p>Status: <span class="font-semibold">{{ $batch->status->label() }}</span></p>
            <p>Total Baris: {{ $batch->total_rows }}</p>
            <p>
                Berhasil: {{ $batch->total_berhasil }} |
                Gagal: {{ $batch->total_gagal }} |
                Dilewati: {{ $batch->total_dilewati }}
            </p>

            @if ($batch->status->value === 'PROCESSING' && $batch->started_at === null)
                <x-filament::button wire:click="startImport">
                    Mulai Proses Impor
                </x-filament::button>
            @elseif ($batch->status->value === 'PROCESSING')
                <x-filament::button color="danger" wire:click="cancelImport">
                    Batalkan Proses
                </x-filament::button>
            @endif
        </div>
    @endif
</div>