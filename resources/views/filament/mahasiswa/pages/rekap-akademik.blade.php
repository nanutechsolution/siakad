<x-filament-panels::page>
    {{-- Progres SKS --}}
    <x-filament::section class="mb-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium">Progres SKS Kelulusan</span>
            <span class="text-sm text-gray-500">
                {{ $data['sks_lulus_saat_ini'] }} / {{ $data['total_sks_wajib'] }} SKS
                ({{ $data['persentase_progres'] }}%)
            </span>
        </div>
        <div class="h-3 w-full rounded-full bg-gray-200 dark:bg-gray-700">
            <div class="h-3 rounded-full bg-primary-500" style="width: {{ $data['persentase_progres'] }}%"></div>
        </div>
    </x-filament::section>

    {{-- Grafik IPK per semester --}}
    <x-filament::section class="mb-4">
        <h3 class="mb-2 text-sm font-medium">Perkembangan IPS &amp; IPK per Semester</h3>
        <div wire:ignore x-data="ipkChart(@js($data['riwayat_per_semester']->map(fn ($r) => [
                'label' => $r->tahunAkademik?->nama_tahun,
                'ips' => (float) $r->ips,
                'ipk' => (float) $r->ipk,
            ])))" x-init="init()">
            <canvas x-ref="canvas" height="90"></canvas>
        </div>
    </x-filament::section>

    {{-- Distribusi nilai huruf --}}
    <x-filament::section>
        <h3 class="mb-2 text-sm font-medium">Distribusi Nilai Huruf</h3>
        <div class="flex flex-wrap gap-2">
            @forelse ($data['distribusi_huruf'] as $huruf => $jumlah)
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs dark:bg-gray-800">
                    {{ $huruf }}: {{ $jumlah }}
                </span>
            @empty
                <span class="text-sm text-gray-400">Belum ada data.</span>
            @endforelse
        </div>
    </x-filament::section>

    @pushonce('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
            function ipkChart(rows) {
                return {
                    init() {
                        new Chart(this.$refs.canvas, {
                            type: 'line',
                            data: {
                                labels: rows.map(r => r.label),
                                datasets: [
                                    { label: 'IPS', data: rows.map(r => r.ips), borderColor: '#6366f1', tension: 0.3 },
                                    { label: 'IPK', data: rows.map(r => r.ipk), borderColor: '#22c55e', tension: 0.3 },
                                ],
                            },
                            options: { scales: { y: { min: 0, max: 4 } } },
                        });
                    },
                };
            }
        </script>
    @endpushonce
</x-filament-panels::page>