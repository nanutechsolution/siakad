@php
$stats = $this->stats;
$ta = $this->tahunAkademikAktif;

$warna = [
'gray' => ['icon' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300', 'angka' => 'text-gray-900 dark:text-white'],
'success' => ['icon' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400', 'angka' => 'text-success-600 dark:text-success-400'],
'warning' => ['icon' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400', 'angka' => 'text-warning-600 dark:text-warning-400'],
'danger' => ['icon' => 'bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400', 'angka' => 'text-danger-600 dark:text-danger-400'],
'primary' => ['icon' => 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400', 'angka' => 'text-primary-600 dark:text-primary-400'],
];

$rupiah = fn ($nilai) => 'Rp ' . number_format((float) $nilai, 0, ',', '.');

$kelompok = [
[
'judul' => 'Kelayakan Generate NIM',
'kolom' => 'xl:grid-cols-3',
'kartu' => [
['label' => 'Total Calon Mahasiswa', 'nilai' => number_format($stats['total']), 'warna' => 'gray', 'icon' => 'heroicon-o-users'],
['label' => 'Memenuhi Persyaratan', 'nilai' => number_format($stats['siap']), 'warna' => 'success', 'icon' => 'heroicon-o-check-badge'],
['label' => 'Belum Memenuhi Persyaratan', 'nilai' => number_format($stats['belum_siap']), 'warna' => 'warning', 'icon' => 'heroicon-o-exclamation-triangle'],
],
],
[
'judul' => 'Status Tagihan',
'kolom' => 'xl:grid-cols-4',
'kartu' => [
['label' => 'Belum Ditagihkan', 'nilai' => number_format($stats['belum_ditagihkan']), 'warna' => 'gray', 'icon' => 'heroicon-o-minus-circle'],
['label' => 'Belum Bayar', 'nilai' => number_format($stats['belum_bayar']), 'warna' => 'danger', 'icon' => 'heroicon-o-x-circle'],
['label' => 'Cicilan', 'nilai' => number_format($stats['cicilan']), 'warna' => 'warning', 'icon' => 'heroicon-o-clock'],
['label' => 'Lunas', 'nilai' => number_format($stats['lunas']), 'warna' => 'success', 'icon' => 'heroicon-o-check-circle'],
],
],
[
'judul' => 'Keuangan',
'kolom' => 'xl:grid-cols-3',
'kartu' => [
['label' => 'Total Tagihan', 'nilai' => $rupiah($stats['total_tagihan']), 'warna' => 'primary', 'icon' => 'heroicon-o-document-text', 'kecil' => true],
['label' => 'Total Terbayar (' . $stats['persen_terbayar'] . '%)', 'nilai' => $rupiah($stats['total_bayar']), 'warna' => 'success', 'icon' => 'heroicon-o-banknotes', 'kecil' => true],
['label' => 'Total Tunggakan', 'nilai' => $rupiah($stats['tunggakan']), 'warna' => 'danger', 'icon' => 'heroicon-o-exclamation-circle', 'kecil' => true],
],
],
];

$progress = max(0, min(100, (float) $stats['progress']));
@endphp

<x-filament-panels::page>

    <div class="space-y-6">

        {{-- Header --}}
        <x-filament::section>
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">

                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        Generate Nomor Induk Mahasiswa
                    </h2>

                    <p class="mt-2 max-w-3xl text-sm text-gray-500 dark:text-gray-400">
                        Halaman ini digunakan untuk memantau calon mahasiswa yang masih
                        menggunakan NIM sementara (PMB). Setelah seluruh persyaratan
                        akademik dan pembayaran terpenuhi, mahasiswa dapat diberikan
                        Nomor Induk Mahasiswa resmi.
                    </p>
                </div>

                <div class="flex flex-col items-start gap-2 md:items-end">
                    @if ($ta)
                    <x-filament::badge color="primary" size="lg" icon="heroicon-m-calendar-days">
                        {{ $ta->nama_tahun }}
                    </x-filament::badge>
                    @else
                    <x-filament::badge color="danger" size="lg" icon="heroicon-m-exclamation-triangle">
                        Tidak ada tahun akademik aktif
                    </x-filament::badge>
                    @endif

                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Menampilkan {{ number_format($stats['total']) }}
                        @if ($stats['total'] !== $stats['total_semua'])
                        dari {{ number_format($stats['total_semua']) }}
                        @endif
                        calon mahasiswa sesuai filter
                    </span>
                </div>

            </div>
        </x-filament::section>

        {{-- Statistik (mengikuti filter tabel) --}}
        <div class="space-y-4 transition-opacity" wire:loading.class="opacity-50">

            @foreach ($kelompok as $grup)
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ $grup['judul'] }}
                </h3>

                <div class="grid gap-4 md:grid-cols-2 {{ $grup['kolom'] }}">
                    @foreach ($grup['kartu'] as $kartu)
                    <x-filament::section compact>
                        <div class="flex items-center gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $warna[$kartu['warna']]['icon'] }}">
                                <x-filament::icon :icon="$kartu['icon']" class="h-6 w-6" />
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                    {{ $kartu['label'] }}
                                </p>
                                <p class="mt-1 truncate font-bold {{ ($kartu['kecil'] ?? false) ? 'text-xl' : 'text-3xl' }} {{ $warna[$kartu['warna']]['angka'] }}">
                                    {{ $kartu['nilai'] }}
                                </p>
                            </div>
                        </div>
                    </x-filament::section>
                    @endforeach
                </div>
            </div>
            @endforeach

        </div>

        {{-- Progress aktivasi --}}
        <x-filament::section heading="Progress Aktivasi" icon="heroicon-o-chart-bar">
            <div class="space-y-3" wire:loading.class="opacity-50">

                <div class="flex items-center justify-between gap-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Calon mahasiswa yang memenuhi syarat Generate NIM
                        ({{ number_format($stats['siap']) }} dari {{ number_format($stats['total']) }})
                    </span>

                    <span class="text-lg font-semibold text-gray-950 dark:text-white">
                        {{ $stats['progress'] }}%
                    </span>
                </div>

                <div class="h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"
                    role="progressbar"
                    aria-valuenow="{{ $progress }}"
                    aria-valuemin="0"
                    aria-valuemax="100">
                    <div class="h-full rounded-full bg-success-600 transition-all duration-500"
                        style="width: {{ $progress }}%"></div>
                </div>

            </div>
        </x-filament::section>

        {{-- Tabel --}}
        {{ $this->table }}

    </div>

</x-filament-panels::page>