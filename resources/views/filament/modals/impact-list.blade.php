{{-- resources/views/filament/modals/impact-list.blade.php --}}
<ul class="space-y-2.5">
    @foreach ($items as $item)
        <li class="flex items-start gap-x-2.5 text-sm text-gray-700 dark:text-gray-300">
            <x-heroicon-o-check-circle class="mt-0.5 h-4 w-4 shrink-0 text-success-500" />
            <span>{{ $item }}</span>
        </li>
    @endforeach
</ul>
