<x-filament-panels::page>
    <div class="flex flex-col gap-y-8">
        {{-- Bagian Ringkasan Widget / KPI Cards --}}
        @if (count($this->getVisibleHeaderWidgets()))
            <div class="fi-page-header-widgets-ctn">
                <x-filament-widgets::widgets
                    :columns="$this->getHeaderWidgetsColumns()"
                    :data="$this->getHeaderWidgetsData()" 
                />
            </div>
        @endif

        {{-- Bagian Konten Utama (Panel Filter & Tabel Data) --}}
        <div class="fi-page-table-ctn transition">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>