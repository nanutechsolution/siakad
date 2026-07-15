<x-filament-panels::page>
    @php $keanggotaan = $this->keanggotaanAktif; @endphp

    @if ($keanggotaan->isEmpty())
    <x-filament::section>
        <div class="flex flex-col items-center gap-2 py-8 text-center">
            <x-filament::icon icon="heroicon-o-user-group" class="h-10 w-10 text-gray-400" />
            <p class="text-sm font-medium text-gray-950 dark:text-white">
                Anda belum tercatat di kelas mana pun
            </p>
            <p class="max-w-sm text-sm text-gray-500">
                Silakan hubungi admin akademik program studi Anda apabila seharusnya
                sudah memiliki kelas aktif pada semester ini.
            </p>
        </div>
    </x-filament::section>
    @else
    @foreach ($keanggotaan as $anggota)
    @php
    $kelas = $anggota->kelas;
    $dosenWali = $kelas?->dosenWali?->first();
    $teman = $kelas ? $this->temanSekelas($kelas->id) : collect();
    @endphp

    @if ($kelas)
    <x-filament::section>
        <x-slot name="heading">{{ $kelas->nama_kelas }}</x-slot>
        <x-slot name="description">
            {{ $kelas->prodi?->nama_prodi ?? 'Program studi tidak diketahui' }}
            &middot; {{ $kelas->program?->nama_program ?? '-' }}
            &middot; Angkatan {{ $kelas->angkatan_id }}
        </x-slot>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <p class="text-sm font-medium text-gray-500">Dosen Wali / PA</p>
                <p class="text-gray-950 dark:text-white">
                    {{ data_get($dosenWali, 'person.nama_dengan_gelar', 'Belum ditentukan') }}
                </p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500">Bergabung Sejak</p>
                <p class="text-gray-950 dark:text-white">
                    {{ $anggota->tanggal_masuk?->translatedFormat('d M Y') ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500">Kapasitas Kelas</p>
                <p class="text-gray-950 dark:text-white">
                    {{ $kelas->kapasitas ?? '-' }} mahasiswa
                </p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500">Jumlah Anggota</p>
                <p class="text-gray-950 dark:text-white">
                    {{ $teman->count() + 1 }} mahasiswa
                </p>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Teman Sekelas</x-slot>

        @if ($teman->isEmpty())
        <p class="text-sm text-gray-500">Belum ada mahasiswa lain di kelas ini.</p>
        @else
        <div class="grid grid-cols-1 gap-x-4 gap-y-1 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($teman as $mhs)
            <p class="text-sm text-gray-700 dark:text-gray-300">
                {{ $mhs->person?->nama_lengkap ?? $mhs->nim }}
            </p>
            @endforeach
        </div>
        @endif
    </x-filament::section>
    @endif
    @endforeach
    @endif
</x-filament-panels::page>