<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-filament::section>
            <x-slot name="heading">SK Penugasan (Individu)</x-slot>
            <p class="text-sm text-gray-500 mb-3">
                Cetak Surat Keputusan untuk satu penugasan pembimbing tertentu — cari berdasarkan NIM, nama kelas, atau nama dosen.
            </p>
            {{ $this->skIndividuAction }}
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">SK Massal per Dosen</x-slot>
            <p class="text-sm text-gray-500 mb-3">
                Cetak SK untuk SEMUA penugasan aktif seorang dosen sekaligus dalam satu file PDF — tidak perlu cetak satu-satu per mahasiswa/kelas.
            </p>
            {{ $this->skMassalDosenAction }}
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Daftar Pembimbing (Rekap)</x-slot>
            <p class="text-sm text-gray-500 mb-3">
                Rekap tabel seluruh pembimbing aktif. Filter opsional per Program Studi &amp; Angkatan, atau kosongkan untuk semua data.
            </p>
            {{ $this->daftarPembimbingAction }}
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Daftar Bimbingan per Dosen</x-slot>
            <p class="text-sm text-gray-500 mb-3">
                Daftar seluruh mahasiswa/kelas yang dibimbing seorang dosen — cocok untuk lampiran laporan kinerja/BKD dosen.
            </p>
            {{ $this->bimbinganDosenAction }}
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Laporan Monitoring</x-slot>
            <p class="text-sm text-gray-500 mb-3">
                Ringkasan statistik keseluruhan + daftar lengkap mahasiswa yang belum memiliki Dosen Wali saat ini.
            </p>
            {{ $this->laporanMonitoringAction }}
        </x-filament::section>
    </div>
</x-filament-panels::page>