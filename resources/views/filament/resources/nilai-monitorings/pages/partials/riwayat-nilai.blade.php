<div class="space-y-3">
    @forelse ($logs as $log)
    <div class="border rounded-lg p-3 text-sm dark:border-gray-700">
        <div class="flex justify-between items-center mb-1">
            <span class="font-medium">
                {{ $log->old_nilai_huruf }} ({{ $log->old_nilai_angka }})
                &rarr;
                {{ $log->new_nilai_huruf }} ({{ $log->new_nilai_angka }})
            </span>
            <span class="text-gray-500 text-xs">{{ $log->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="text-gray-600 dark:text-gray-300">{{ $log->alasan_perbaikan }}</div>
        <div class="text-xs text-gray-400 mt-1">
            Oleh: {{ $log->executedBy?->name ?? '—' }}
            @if ($log->nomor_sk_perbaikan)
            &bull; No. SK: {{ $log->nomor_sk_perbaikan }}
            @endif
        </div>
    </div>
    @empty
    <p class="text-gray-500 text-sm">Belum ada riwayat koreksi untuk nilai ini.</p>
    @endforelse
</div>