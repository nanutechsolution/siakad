<x-filament-panels::page>
    @php
    $daftarTahun = $this->daftarTahunAkademikMahasiswa();
    $jadwalPerHari = $this->jadwalPerHari;
    $tanpaJadwal = $this->mataKuliahTanpaJadwal;
    @endphp

    {{-- Filter semester --}}
    <x-filament::section>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-sm flex-1">
                <label class="text-sm font-medium text-gray-950 dark:text-white">
                    Semester
                </label>

                @if ($daftarTahun->isEmpty())
                <p class="mt-1 text-sm text-gray-500">
                    Anda belum memiliki riwayat KRS pada semester mana pun.
                </p>
                @else
                <select
                    wire:model.live="tahunAkademikId"
                    class="fi-select-input mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                    @foreach ($daftarTahun as $tahun)
                    <option value="{{ $tahun->id }}">
                        {{ $tahun->nama_tahun }}
                        @if ($tahun->is_active) &mdash; Aktif @endif
                    </option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>
    </x-filament::section>

    {{-- Jadwal mingguan --}}
    @if ($jadwalPerHari->isEmpty())
    <x-filament::section>
        <div class="flex flex-col items-center gap-2 py-8 text-center">
            <x-filament::icon icon="heroicon-o-calendar" class="h-10 w-10 text-gray-400" />
            <p class="text-sm font-medium text-gray-950 dark:text-white">
                Belum ada jadwal kuliah
            </p>
            <p class="max-w-sm text-sm text-gray-500">
                Tidak ditemukan jadwal kuliah untuk semester yang dipilih. Ini bisa terjadi
                karena KRS Anda belum disetujui, atau memang belum ada jadwal yang ditautkan
                ke mata kuliah yang Anda ambil.
            </p>
        </div>
    </x-filament::section>
    @else
    @foreach ($jadwalPerHari as $hari => $daftarJadwal)
    <x-filament::section>
        <x-slot name="heading">{{ $hari }}</x-slot>

        <div class="divide-y divide-gray-100 dark:divide-white/10">
            @foreach ($daftarJadwal as $jadwal)
            <div class="flex flex-col gap-1 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-medium text-gray-950 dark:text-white">
                        {{ $jadwal->mataKuliah?->nama_mk ?? '(Mata kuliah tidak ditemukan)' }}
                        <span class="text-gray-500 font-normal">
                            ({{ $jadwal->mataKuliah?->sks_default ?? '-' }} SKS)
                        </span>
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ $jadwal->dosen_label }}
                    </p>
                </div>

                <div class="flex flex-col items-start gap-1 sm:items-end">
                    <x-filament::badge color="primary">
                        {{ $jadwal->jam_label }}
                    </x-filament::badge>
                    <span class="text-sm text-gray-500">
                        {{ $jadwal->ruang?->kode_ruang }} - {{ $jadwal->ruang?->nama_ruang }}
                        &middot; {{ $jadwal->kelas?->nama_kelas ?? '-' }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </x-filament::section>
    @endforeach
    @endif

    {{-- Mata kuliah tanpa jadwal reguler (skripsi, KKN, dsb) --}}
    @if ($tanpaJadwal->isNotEmpty())
    <x-filament::section>
        <x-slot name="heading">Tanpa Jadwal Reguler</x-slot>
        <x-slot name="description">
            Mata kuliah berikut tercatat di KRS Anda namun tidak memiliki jadwal
            tatap muka reguler (contoh: skripsi, KKN, atau kelas mandiri).
        </x-slot>

        <ul class="list-inside list-disc text-sm text-gray-700 dark:text-gray-300">
            @foreach ($tanpaJadwal as $detail)
            <li>
                {{ $detail->nama_mk_snapshot ?? $detail->mataKuliah?->nama_mk ?? '(Mata kuliah tidak dikenali)' }}
                ({{ $detail->sks_snapshot ?? '-' }} SKS)
            </li>
            @endforeach
        </ul>
    </x-filament::section>
    @endif
</x-filament-panels::page>