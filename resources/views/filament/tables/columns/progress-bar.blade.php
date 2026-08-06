{{-- resources/views/filament/tables/columns/progress-bar.blade.php --}}
@php
    /** @var \App\Models\RefTahunAkademik $record */
    $percent = $record->status->progressPercent();
    $barColor = match (true) {
        $percent >= 100 => 'bg-gray-400 dark:bg-gray-500',
        $percent >= 70 => 'bg-success-500',
        $percent >= 40 => 'bg-warning-500',
        default => 'bg-primary-500',
    };
@endphp

<div class="flex items-center gap-x-2 w-32">
    <div class="flex-1 h-1.5 rounded-full bg-gray-200 dark:bg-white/10 overflow-hidden">
        <div class="h-full rounded-full {{ $barColor }} transition-all" style="width: {{ $percent }}%"></div>
    </div>
    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 tabular-nums w-9 text-right">{{ $percent }}%</span>
</div>
