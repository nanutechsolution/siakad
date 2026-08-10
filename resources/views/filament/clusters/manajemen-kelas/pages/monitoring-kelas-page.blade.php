<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mb-6">
        <x-filament::section>
            <div class="text-sm text-gray-500">Total Kelas</div>
            <div class="text-2xl font-bold">{{ $this->getTotalKelas() }}</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Mahasiswa Belum Punya Kelas</div>
            <div class="text-2xl font-bold {{ $this->getTotalMahasiswaTanpaKelas() > 0 ? 'text-danger-600' : 'text-success-600' }}">
                {{ $this->getTotalMahasiswaTanpaKelas() }}
            </div>
        </x-filament::section>
    </div>

    <x-filament::section class="mb-6" collapsible>
        <x-slot name="heading">Kapasitas per Kelas</x-slot>

        @php $daftarKelas = $this->getKapasitasKelas(); @endphp

        @if ($daftarKelas->isEmpty())
        <div class="text-center py-6 text-gray-500">
            <p>Belum ada kelas.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach ($daftarKelas as $item)
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span>{{ $item['nama'] }}</span>
                    <span class="{{ $item['penuh'] ? 'text-danger-600 font-semibold' : 'text-gray-500' }}">
                        {{ $item['jumlah'] }}{{ $item['kapasitas'] ? '/'.$item['kapasitas'] : ' (tanpa batas)' }}
                        @if ($item['penuh']) &middot; PENUH @endif
                    </span>
                </div>
                @if ($item['persen'] !== null)
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                    <div
                        class="h-2 {{ $item['penuh'] ? 'bg-danger-600' : ($item['persen'] >= 80 ? 'bg-warning-500' : 'bg-success-500') }}"
                        style="width: {{ $item['persen'] }}%"></div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </x-filament::section>

    {{ $this->table }}
</x-filament-panels::page>