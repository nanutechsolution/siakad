<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @foreach ($contexts as $context)
            <button
                type="button"
                wire:click="pilihKonteks({{ $context->id }})"
                class="flex flex-col items-start gap-1 rounded-xl border border-gray-200 p-4 text-left transition hover:border-primary-500 hover:shadow-md dark:border-gray-700"
            >
                <span class="text-sm font-semibold text-gray-950 dark:text-white">
                    {{ $context->jabatan?->nama_jabatan }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    @if ($context->prodi)
                        {{ $context->prodi->nama_prodi }}
                    @elseif ($context->fakultas)
                        {{ $context->fakultas->nama_fakultas }}
                    @else
                        Lingkup Universitas
                    @endif
                </span>
            </button>
        @endforeach
    </div>
</x-filament-panels::page>