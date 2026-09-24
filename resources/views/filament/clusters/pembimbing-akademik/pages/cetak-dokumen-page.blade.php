<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-5 dark:border-primary-500/20 dark:bg-primary-500/10">
            <div class="flex items-start gap-3">
                <div class="rounded-xl bg-primary-600 p-2 text-white">
                    <x-heroicon-o-printer class="h-6 w-6" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pusat Cetak Dokumen</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Pilih jenis dokumen, isi filter atau dosen yang diperlukan, lalu cetak PDF.
                        Dokumen resmi dapat membutuhkan konfigurasi penandatangan dan QR verifikasi.
                    </p>
                </div>
            </div>
        </div>

        <section>
            <div class="mb-3 flex items-center gap-2">
                <x-heroicon-o-document-check class="h-5 w-5 text-primary-600" />
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Surat Keputusan</h3>
                <span class="rounded-full bg-primary-50 px-2 py-0.5 text-xs text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">Dokumen resmi</span>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-filament::section>
                    <x-slot name="heading">SK Penugasan Individu</x-slot>
                    <p class="mb-4 text-sm text-gray-500">Cetak SK untuk satu penugasan Dosen Wali aktif. Cari berdasarkan NIM, nama mahasiswa, atau kelas.</p>
                    {{ $this->skIndividuAction }}
                </x-filament::section>
                <x-filament::section>
                    <x-slot name="heading">SK Massal per Dosen</x-slot>
                    <p class="mb-4 text-sm text-gray-500">Satu file PDF berisi seluruh penugasan aktif seorang dosen.</p>
                    {{ $this->skMassalDosenAction }}
                </x-filament::section>
            </div>
        </section>

        <section>
            <div class="mb-3 flex items-center gap-2">
                <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-500" />
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Rekap & Lampiran</h3>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-white/10 dark:text-gray-300">Laporan</span>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-filament::section>
                    <x-slot name="heading">Rekap Pembimbing Aktif</x-slot>
                    <p class="mb-4 text-sm text-gray-500">Rekap pembimbing dengan filter Program Studi dan Angkatan opsional.</p>
                    {{ $this->daftarPembimbingAction }}
                </x-filament::section>
                <x-filament::section>
                    <x-slot name="heading">Daftar Bimbingan per Dosen</x-slot>
                    <p class="mb-4 text-sm text-gray-500">Lampiran mahasiswa/kelas yang dibimbing untuk laporan kinerja atau BKD.</p>
                    {{ $this->bimbinganDosenAction }}
                </x-filament::section>
            </div>
        </section>

        <section>
            <div class="mb-3 flex items-center gap-2">
                <x-heroicon-o-chart-bar class="h-5 w-5 text-gray-500" />
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Monitoring</h3>
            </div>
            <x-filament::section>
                <x-slot name="heading">Laporan Monitoring Pembimbing</x-slot>
                <p class="mb-4 text-sm text-gray-500">Ringkasan statistik dan daftar mahasiswa yang belum memiliki Dosen Wali.</p>
                {{ $this->laporanMonitoringAction }}
            </x-filament::section>
        </section>

        <p class="text-xs text-gray-500 dark:text-gray-400">
            Pastikan data penugasan aktif benar dan pejabat penandatangan sudah dikonfigurasi sebelum mencetak SK resmi.
        </p>
    </div>
</x-filament-panels::page>
