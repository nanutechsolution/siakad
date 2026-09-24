<x-filament-panels::page>
    <div class="-mx-1 space-y-4 pb-24 sm:mx-0 sm:space-y-6 sm:pb-0">
        {{-- Mobile-first profile header --}}
        <section class="relative overflow-hidden rounded-2xl border border-primary-200 bg-gradient-to-br from-primary-600 to-primary-800 p-4 text-white shadow-sm sm:p-6">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-16 right-12 h-36 w-36 rounded-full bg-white/5"></div>

            <div class="relative flex items-center gap-3 sm:gap-5">
                @if ($dosen->person?->photo_path)
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::url($dosen->person->photo_path) }}"
                        alt="Foto {{ $dosen->person?->nama_lengkap }}"
                        class="h-16 w-16 shrink-0 rounded-2xl object-cover ring-2 ring-white/80 sm:h-20 sm:w-20"
                    >
                @else
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-2 ring-white/30 sm:h-20 sm:w-20">
                        <x-heroicon-o-user-circle class="h-10 w-10 sm:h-12 sm:w-12" />
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <p class="mb-1 text-[11px] font-medium uppercase tracking-wider text-primary-100">Profil Dosen</p>
                    <h1 class="truncate text-lg font-bold sm:text-2xl">
                        {{ $dosen->person?->nama_dengan_gelar ?? $dosen->person?->nama_lengkap ?? 'Profil Dosen' }}
                    </h1>
                    <p class="mt-1 truncate text-xs text-primary-100 sm:text-sm">
                        {{ $dosen->prodi?->nama_prodi ?? 'Program Studi belum diatur' }}
                    </p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @if ($dosen->nidn)
                            <span class="rounded-md bg-white/15 px-2 py-1 font-mono text-[11px]">NIDN {{ $dosen->nidn }}</span>
                        @elseif ($dosen->nuptk)
                            <span class="rounded-md bg-white/15 px-2 py-1 font-mono text-[11px]">NUPTK {{ $dosen->nuptk }}</span>
                        @else
                            <span class="rounded-md bg-warning-400/20 px-2 py-1 text-[11px] text-warning-100">ID belum diisi</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Kinerja: horizontal scroll di mobile, grid di desktop --}}
        <x-filament::section>
            <x-slot name="heading">Ringkasan Kinerja</x-slot>
            <x-slot name="description">{{ $stats['tahun_aktif'] }}</x-slot>

            @php
                $cards = [
                    ['Kelas Diampu', $stats['jumlah_kelas_aktif'], 'heroicon-o-academic-cap', 'text-primary-600', 'bg-primary-50 dark:bg-primary-500/10'],
                    ['Mahasiswa Wali', $stats['jumlah_mahasiswa_wali'], 'heroicon-o-users', 'text-success-600', 'bg-success-50 dark:bg-success-500/10'],
                    ['Riset Ketua', $stats['jumlah_penelitian_ketua'], 'heroicon-o-beaker', 'text-info-600', 'bg-info-50 dark:bg-info-500/10'],
                    ['Riset Anggota', $stats['jumlah_penelitian_anggota'], 'heroicon-o-user-group', 'text-gray-500', 'bg-gray-50 dark:bg-white/5'],
                    ['Total Luaran', $stats['total_luaran'], 'heroicon-o-document-chart-bar', 'text-warning-600', 'bg-warning-50 dark:bg-warning-500/10'],
                    ['Skor EDOM', $stats['skor_edom'] ?? '-', 'heroicon-o-star', 'text-success-600', 'bg-success-50 dark:bg-success-500/10'],
                ];
            @endphp

            <div class="-mx-1 flex snap-x gap-2 overflow-x-auto px-1 pb-2 sm:mx-0 sm:grid sm:grid-cols-3 sm:gap-3 sm:overflow-visible sm:px-0 lg:grid-cols-6">
                @foreach ($cards as [$label, $value, $icon, $iconColor, $background])
                    <div class="{{ $background }} min-w-[132px] shrink-0 snap-start rounded-xl border border-gray-200/80 p-3 dark:border-gray-700 sm:min-w-0 sm:p-4">
                        <x-filament::icon :icon="$icon" class="{{ $iconColor }} mb-2 h-5 w-5" />
                        <p class="text-[11px] leading-tight text-gray-500 dark:text-gray-400">{{ $label }}</p>
                        <p class="mt-1 text-xl font-bold tabular-nums text-gray-900 dark:text-white">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <p class="mt-1 text-center text-[10px] text-gray-400 sm:hidden">Geser untuk melihat statistik lainnya →</p>

            @if ($stats['luaran_per_tahun']->isNotEmpty())
                <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Luaran terverifikasi</p>
                        <span class="text-[11px] text-gray-400">5 tahun terakhir</span>
                    </div>
                    <div class="-mx-1 flex snap-x gap-2 overflow-x-auto px-1 pb-1">
                        @foreach ($stats['luaran_per_tahun'] as $row)
                            <div class="min-w-[76px] shrink-0 snap-start rounded-lg border border-gray-200 px-3 py-2 text-center dark:border-gray-700">
                                <p class="text-xs text-gray-500">{{ $row->tahun_terbit }}</p>
                                <p class="font-bold text-gray-900 dark:text-white">{{ $row->jumlah }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (filled($stats['jabatan_aktif']))
                <div class="mt-4 flex flex-wrap gap-1.5">
                    @foreach ($stats['jabatan_aktif'] as $jabatan)
                        <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                            <x-heroicon-o-briefcase class="h-3.5 w-3.5" /> {{ $jabatan }}
                        </span>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Form utama --}}
        <form wire:submit="save" class="space-y-4 sm:space-y-6">
            {{ $this->form }}

            {{-- Safe-area friendly sticky action di mobile --}}
            <div class="fixed inset-x-0 bottom-0 z-20 border-t border-gray-200 bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-[0_-4px_14px_rgba(0,0,0,0.08)] backdrop-blur dark:border-gray-700 dark:bg-gray-900/95 sm:static sm:border-0 sm:bg-transparent sm:px-0 sm:py-0 sm:shadow-none sm:backdrop-blur-none">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 sm:mx-0 sm:flex-row sm:items-center">
                    <x-filament::button type="submit" icon="heroicon-o-check" class="w-full sm:w-auto">
                        Simpan Perubahan
                    </x-filament::button>
                    <p class="text-center text-[11px] text-gray-500 sm:text-left">
                        Identitas resmi perlu diverifikasi admin akademik.
                    </p>
                </div>
            </div>
        </form>
    </div>
</x-filament-panels::page>
