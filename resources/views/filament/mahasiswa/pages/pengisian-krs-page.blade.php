<x-filament-panels::page>

    @if(!$isEligible && $isApproved)

    {{-- ============================================================
     KRS SUDAH DISETUJUI
============================================================= --}}

    <div class="mx-auto max-w-3xl">

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- Header --}}
            <div class="border-b border-gray-200 bg-success-50 px-6 py-5 dark:border-white/10 dark:bg-success-950/30">

                <div class="flex items-start gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-success-100 dark:bg-success-900/50">

                        <x-filament::icon
                            icon="heroicon-o-check-circle"
                            class="h-7 w-7 text-success-600 dark:text-success-400" />

                    </div>

                    <div class="min-w-0">

                        <h2 class="text-xl font-bold text-gray-950 dark:text-white">
                            KRS Anda Sudah Disetujui
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Tahun Akademik {{ $activeTa?->nama_tahun }}
                        </p>

                    </div>

                </div>

            </div>

            {{-- Content --}}
            <div class="space-y-5 p-6">

                <div class="rounded-xl bg-success-50 p-4 ring-1 ring-success-200 dark:bg-success-950/30 dark:ring-success-800">

                    <div class="flex gap-3">

                        <x-filament::icon
                            icon="heroicon-o-check-circle"
                            class="mt-0.5 h-5 w-5 shrink-0 text-success-600 dark:text-success-400" />

                        <div>

                            <p class="font-semibold text-success-800 dark:text-success-300">
                                Pengisian KRS Anda telah selesai
                            </p>

                            <p class="mt-1 text-sm leading-6 text-success-700 dark:text-success-400">
                                KRS Anda telah diperiksa dan disetujui oleh
                                <strong>Dosen Wali</strong>.
                                Data KRS sudah tercatat di SIAKAD dan tidak perlu diajukan kembali.
                            </p>

                        </div>

                    </div>

                </div>

                {{-- Status --}}
                <div class="grid grid-cols-1 gap-4 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50 sm:grid-cols-2">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Tahun Akademik
                        </p>

                        <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $activeTa?->nama_tahun ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Status KRS
                        </p>

                        <div class="mt-1">
                            <x-filament::badge color="success" icon="heroicon-m-check-circle">
                                Disetujui
                            </x-filament::badge>
                        </div>
                    </div>

                </div>

                {{-- Apa yang perlu dilakukan --}}
                <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">

                    <div class="flex gap-3">

                        <x-filament::icon
                            icon="heroicon-o-information-circle"
                            class="mt-0.5 h-5 w-5 shrink-0 text-gray-500 dark:text-gray-400" />

                        <div>

                            <p class="font-semibold text-gray-900 dark:text-white">
                                Apa yang perlu Anda lakukan?
                            </p>

                            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                Tidak ada tindakan yang perlu dilakukan untuk KRS.
                                Silakan mengikuti perkuliahan sesuai jadwal yang tercantum di SIAKAD.
                            </p>

                        </div>

                    </div>

                </div>

                {{-- Informasi bantuan --}}
                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">

                    <p class="text-sm leading-6 text-gray-600 dark:text-gray-400">

                        Jika terdapat ketidaksesuaian pada mata kuliah, jadwal, dosen,
                        atau kelas, silakan hubungi
                        <strong class="text-gray-900 dark:text-white">
                            Admin Prodi
                        </strong>.

                        Jika mengalami masalah teknis pada aplikasi,
                        silakan hubungi
                        <strong class="text-gray-900 dark:text-white">
                            BTSI
                        </strong>.

                    </p>

                    <p class="mt-3 text-sm font-semibold text-success-700 dark:text-success-400">
                        Tidak perlu datang ke BTSI/IT hanya untuk memastikan KRS Anda sudah disetujui.
                    </p>

                </div>

            </div>

        </div>

    </div>

    @elseif(!$isEligible)

    {{-- ============================================================
             KRS DITOLAK — KARTU "KRS PERLU DIPERBAIKI"
        ============================================================= --}}

    <div class="mx-auto max-w-3xl">

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- Header --}}
            <div class="border-b border-gray-200 bg-danger-50 px-4 py-5 dark:border-white/10 dark:bg-danger-950/30 sm:px-6">

                <div class="flex items-start gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-danger-100 dark:bg-danger-900/50">

                        <x-filament::icon
                            icon="heroicon-o-x-circle"
                            class="h-7 w-7 text-danger-600 dark:text-danger-400" />

                    </div>

                    <div class="min-w-0">

                        <h2 class="text-lg font-bold text-gray-950 dark:text-white sm:text-xl">
                            KRS Perlu Diperbaiki
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Dosen Wali menolak pengajuan KRS Anda. Silakan perbaiki lalu ajukan kembali.
                        </p>

                    </div>

                </div>

            </div>


            {{-- Content --}}
            <div class="space-y-5 p-4 sm:p-6">

                {{-- Informasi KRS --}}
                <dl class="grid grid-cols-1 gap-4 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50 sm:grid-cols-3">

                    <div class="min-w-0">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Tahun Akademik
                        </dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $activeTa?->nama_tahun ?? '-' }}
                        </dd>
                    </div>

                    <div class="min-w-0">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Semester
                        </dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">
                            @php
                            $semesterKe = ($mahasiswa && $activeTa) ? $mahasiswa->semesterPada($activeTa) : null;
                            @endphp
                            {{ $semesterKe ? 'Semester ' . $semesterKe : '-' }}
                        </dd>
                    </div>

                    <div class="min-w-0">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Status
                        </dt>
                        <dd class="mt-1">
                            <x-filament::badge color="danger">
                                Ditolak
                            </x-filament::badge>
                        </dd>
                    </div>

                </dl>


                {{-- Alasan penolakan --}}
                <div class="rounded-xl bg-danger-50 p-4 ring-1 ring-danger-200 dark:bg-danger-950/30 dark:ring-danger-800">

                    <div class="flex gap-3">

                        <x-filament::icon
                            icon="heroicon-o-chat-bubble-left-ellipsis"
                            class="mt-0.5 h-5 w-5 shrink-0 text-danger-600 dark:text-danger-400" />

                        <div class="min-w-0">

                            <p class="font-semibold text-danger-800 dark:text-danger-300">
                                Alasan Penolakan
                            </p>

                            <p class="mt-1 whitespace-pre-line break-words text-sm leading-6 text-danger-700 dark:text-danger-400">
                                {{ filled($rejectionReason) ? $rejectionReason : 'Dosen Wali tidak mencantumkan alasan penolakan.' }}
                            </p>

                            @if(filled($rejectedAt))
                            <p class="mt-2 text-xs text-danger-600/80 dark:text-danger-400/80">
                                Ditolak pada {{ $rejectedAt }}
                            </p>
                            @endif

                        </div>

                    </div>

                </div>


                {{-- Pesan jika perbaikan belum dapat dilakukan --}}
                @if(filled($revisionBlockMessage))

                <div class="rounded-xl bg-warning-50 p-4 ring-1 ring-warning-200 dark:bg-warning-950/30 dark:ring-warning-800">

                    <div class="flex gap-3">

                        <x-filament::icon
                            icon="heroicon-o-exclamation-triangle"
                            class="mt-0.5 h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400" />

                        <div class="min-w-0">

                            <p class="font-semibold text-warning-800 dark:text-warning-300">
                                KRS Belum Dapat Diperbaiki Saat Ini
                            </p>

                            <p class="mt-1 break-words text-sm text-warning-700 dark:text-warning-400">
                                {{ $revisionBlockMessage }}
                            </p>

                        </div>

                    </div>

                </div>

                @endif


                {{-- Aksi --}}
                <div class="flex flex-col gap-3 pt-1 sm:flex-row sm:items-center sm:justify-between">

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Perbaiki mata kuliah atau jadwal sesuai catatan Dosen Wali,
                        lalu ajukan kembali KRS Anda.
                    </p>

                    <x-filament::button
                        wire:click="mulaiRevisi"
                        wire:loading.attr="disabled"
                        wire:target="mulaiRevisi"
                        :disabled="filled($revisionBlockMessage)"
                        icon="heroicon-o-pencil-square"
                        size="lg"
                        class="w-full sm:w-auto">
                        Perbaiki KRS
                    </x-filament::button>

                </div>

            </div>

        </div>

    </div>

    @else

    {{-- ============================================================
             MODE REVISI (setelah klik "Perbaiki KRS")
        ============================================================= --}}

    @if($isRevision)

    <div class="mb-6 overflow-hidden rounded-2xl border border-warning-200 bg-warning-50 dark:border-warning-800 dark:bg-warning-950/30">
        <div class="p-4 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warning-100 text-warning-600 dark:bg-warning-900/60 dark:text-warning-300">
                    <x-heroicon-o-pencil-square class="h-6 w-6" />
                </div>

                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-warning-950 dark:text-warning-100 sm:text-xl">
                        Mode Revisi KRS
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-warning-800 dark:text-warning-200">
                        Pilihan KRS sebelumnya sudah dimuat. Ubah mata kuliah atau jadwal
                        sesuai catatan Dosen Wali, lalu ajukan kembali.
                    </p>
                </div>
            </div>

            @if(filled($rejectionReason))
            <div class="mt-4 rounded-xl bg-white/70 px-4 py-3 ring-1 ring-warning-200 dark:bg-gray-900/40 dark:ring-warning-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-warning-700 dark:text-warning-300">
                    Alasan Penolakan
                </p>

                <p class="mt-1 whitespace-pre-line break-words text-sm leading-6 text-warning-900 dark:text-warning-100">
                    {{ $rejectionReason }}
                </p>
            </div>
            @endif
        </div>
    </div>

    @endif


    {{-- ============================================================
             HEADER
        ============================================================= --}}

    <div class="mb-6 overflow-hidden rounded-2xl border border-primary-200 bg-primary-50 dark:border-primary-800 dark:bg-primary-950/30">
        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/60 dark:text-primary-300">
                    <x-heroicon-o-clipboard-document-check class="h-6 w-6" />
                </div>

                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-primary-950 dark:text-primary-100 sm:text-xl">
                        KRS {{ $activeTa?->nama_tahun }}
                    </h2>

                    @if(($mahasiswa?->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET')
                    <p class="mt-1 text-sm leading-6 text-primary-800 dark:text-primary-200">
                        KRS Anda sudah disiapkan otomatis berdasarkan
                        <strong>kurikulum dan kelas</strong> yang Anda ikuti.
                        Anda <strong>tidak perlu memilih mata kuliah</strong>.
                    </p>
                    @else
                    <p class="mt-1 text-sm leading-6 text-primary-800 dark:text-primary-200">
                        Silakan pilih mata kuliah yang ingin Anda ambil.
                        Sistem akan memeriksa batas SKS, bentrok jadwal,
                        dan kapasitas kelas.
                    </p>
                    @endif
                </div>
            </div>

            @if(($mahasiswa?->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET')
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-white/70 px-4 py-3 ring-1 ring-primary-200 dark:bg-gray-900/40 dark:ring-primary-800">
                <x-heroicon-o-information-circle class="mt-0.5 h-5 w-5 shrink-0 text-primary-600 dark:text-primary-400" />

                <p class="text-sm leading-5 text-primary-800 dark:text-primary-200">
                    <span class="font-semibold">Yang perlu Anda lakukan:</span>
                    periksa mata kuliah, jadwal, dosen, dan ruang.
                    @if($isRevision)
                    Jika sudah benar, lanjutkan dengan
                    <strong>Ajukan Kembali KRS</strong>.
                    @else
                    Jika sudah benar, lanjutkan dengan
                    <strong>Ajukan KRS ke Dosen Wali</strong>.
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>


    {{-- ============================================================
             FORM
        ============================================================= --}}

    <div class="space-y-6">

        {{ $this->form }}

        <div class="mt-6">
            <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-end">

                <x-filament::button
                    wire:click="mountAction('ajukanKrs')"
                    icon="heroicon-o-paper-airplane"
                    size="lg"
                    class="w-full sm:w-auto">
                    @if($isRevision)
                    Ajukan Kembali KRS
                    @else
                    Ajukan KRS ke Dosen Wali
                    @endif
                </x-filament::button>

            </div>

            @if($isRevision)
            <p class="mt-2 text-center text-xs text-gray-500 sm:text-right dark:text-gray-400">
                Periksa kembali perubahan KRS Anda sebelum mengajukan kembali kepada Dosen Wali.
            </p>
            @else
            <p class="mt-2 text-center text-xs text-gray-500 sm:text-right dark:text-gray-400">
                Periksa mata kuliah dan jadwal terlebih dahulu. Setelah diajukan,
                KRS akan diperiksa oleh Dosen Wali.
            </p>
            @endif

        </div>

    </div>

    @endif


    {{-- ================================================================
         FILAMENT ACTION MODALS
    ================================================================= --}}

    <x-filament-actions::modals />

</x-filament-panels::page>