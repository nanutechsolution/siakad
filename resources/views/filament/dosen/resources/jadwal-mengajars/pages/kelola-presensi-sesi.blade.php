<x-filament-panels::page>
    <div class="mb-4">
        <p class="text-sm text-gray-500">
            Token Sesi: <span class="font-mono font-semibold">{{ $sesi->token_sesi ?? '-' }}</span>
            · Status: {{ $sesi->status_sesi->getLabel() }}
        </p>
    </div>

    {{ $this->table }}
</x-filament-panels::page>