<x-filament-panels::page>

    @include('filament.pages.partials.plotting-dosen-summary')

    <div class="mt-2">
        {{ $this->table }}
    </div>

</x-filament-panels::page>