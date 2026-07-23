<div class="space-y-4">
    @if (! $preview)
    <p class="text-sm text-gray-500">Silakan selesaikan langkah "Unggah File" terlebih dahulu.</p>
    @else
    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-lg border p-4">
            <p class="text-xs text-gray-500">Total Baris</p>
            <p class="text-2xl font-semibold">{{ $preview['total_records'] }}</p>
        </div>
        <div class="rounded-lg border p-4">
            <p class="text-xs text-gray-500">Valid</p>
            <p class="text-2xl font-semibold text-success-600">{{ $preview['valid_count'] }}</p>
        </div>
        <div class="rounded-lg border p-4">
            <p class="text-xs text-gray-500">Tidak Valid</p>
            <p class="text-2xl font-semibold text-danger-600">{{ $preview['invalid_count'] }}</p>
        </div>
    </div>

    @if (count($preview['warnings'] ?? []) > 0)
    <div class="rounded-lg border border-warning-300 bg-warning-50 p-4">
        <p class="text-sm font-medium text-warning-700 mb-2">Peringatan</p>
        <ul class="list-disc list-inside text-sm text-warning-700">
            @foreach ($preview['warnings'] as $warning)
            <li>{{ $warning }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    @endif
</div>