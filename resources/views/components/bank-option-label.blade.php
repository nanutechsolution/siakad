@props(['bank'])

<div class="flex items-center gap-3">
    {{-- Jika bank punya logo, tampilkan. Jika tidak, tampilkan inisial kotak --}}
    @if($bank->logo)
        <div class="flex-shrink-0 w-10 h-10 bg-white rounded border flex items-center justify-center p-1">
            <img src="{{ Storage::url($bank->logo) }}" alt="{{ $bank->nama_bank }}" class="max-w-full max-h-full object-contain">
        </div>
    @else
        <div class="flex-shrink-0 w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 flex items-center justify-center">
            <span class="text-sm font-bold text-gray-500 dark:text-gray-400">
                {{ strtoupper(substr($bank->nama_bank, 0, 2)) }}
            </span>
        </div>
    @endif

    <div class="flex flex-col">
        <span class="font-bold text-sm text-gray-900 dark:text-gray-100">
            {{ $bank->nama_bank }}
        </span>
        <span class="text-xs text-gray-500 dark:text-gray-400">
            {{ $bank->no_rekening }} <span class="mx-1">•</span> a.n. {{ $bank->atas_nama }}
        </span>
    </div>
</div>