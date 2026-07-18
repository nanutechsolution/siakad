<x-filament-panels::page>
    {{ $this->form }}
    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-white/10">
        <x-filament::actions
            :actions="$this->getFormActions()"
            alignment="end" />
    </div>
</x-filament-panels::page>