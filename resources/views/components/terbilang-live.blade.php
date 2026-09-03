@props(['nominalTerbilang'])

<div class="-mt-4 px-4 py-3 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 rounded-lg shadow-inner flex items-center gap-2">
    <x-heroicon-o-check-circle class="w-5 h-5 text-primary-600 dark:text-primary-400" />
    <span class="text-primary-700 dark:text-primary-300 text-sm font-semibold tracking-wide">
        {{ $nominalTerbilang }}
    </span>
</div>