<x-filament-panels::page>

    @if(!$isEligible)

    <div class="mx-auto max-w-3xl">

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- Header --}}
            <div class="border-b border-gray-200 bg-danger-50 px-6 py-5 dark:border-white/10 dark:bg-danger-950/30">

                <div class="flex items-start gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-danger-100 dark:bg-danger-900/50">

                        <x-filament::icon
                            icon="heroicon-o-exclamation-triangle"
                            class="h-7 w-7 text-danger-600 dark:text-danger-400" />

                    </div>

                    <div>

                        <h2 class="text-xl font-bold text-gray-950 dark:text-white">

                            {{
                                    str_contains(
                                        $eligibilityMessage,
                                        'Penawaran mata kuliah belum lengkap'
                                    )
                                        ? 'KRS Belum Dapat Dibuka'
                                        : 'Akses Pengisian KRS Terkunci'
                                }}

                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Tahun Akademik {{ $activeTa?->nama_tahun }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Content --}}
            <div class="space-y-5 p-6">

                @if(
                str_contains(
                $eligibilityMessage,
                'Penawaran mata kuliah belum lengkap'
                )
                )

                {{-- Alert utama --}}
                <div class="rounded-xl bg-warning-50 p-4 ring-1 ring-warning-200 dark:bg-warning-950/30 dark:ring-warning-800">

                    <div class="flex gap-3">

                        <x-filament::icon
                            icon="heroicon-o-information-circle"
                            class="mt-0.5 h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400" />

                        <div>

                            <p class="font-semibold text-warning-800 dark:text-warning-300">
                                Beberapa Mata Kuliah Belum Memiliki Jadwal
                            </p>

                            <p class="mt-1 text-sm text-warning-700 dark:text-warning-400">
                                KRS akan tersedia setelah seluruh mata kuliah wajib semester Anda
                                memiliki jadwal untuk kelas yang Anda ikuti.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Daftar MK --}}
                <div>

                    <div class="mb-3">

                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                            Mata Kuliah Belum Memiliki Jadwal
                        </h3>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Mata kuliah berikut belum dapat diambil karena jadwalnya belum tersedia.
                        </p>

                    </div>


                    <div class="divide-y divide-gray-200 overflow-hidden rounded-xl border border-gray-200 dark:divide-white/10 dark:border-white/10">

                        @php

                        $message = $eligibilityMessage;

                        $daftarMk = str_contains(
                        $message,
                        ': '
                        )
                        ? explode(
                        ': ',
                        $message,
                        2
                        )[1]
                        : null;

                        $daftarMk = $daftarMk
                        ? preg_replace(
                        '/\. KRS belum dapat diajukan.*$/',
                        '',
                        $daftarMk
                        )
                        : null;

                        $mataKuliah = $daftarMk
                        ? array_filter(
                        array_map(
                        'trim',
                        explode(
                        ', ',
                        $daftarMk
                        )
                        )
                        )
                        : [];

                        @endphp


                        @foreach($mataKuliah as $mk)

                        @php

                        [$kode, $nama] = array_pad(
                        explode(
                        ' - ',
                        $mk,
                        2
                        ),
                        2,
                        ''
                        );

                        @endphp

                        <div class="flex items-center gap-4 bg-white px-4 py-3 dark:bg-gray-900">

                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">

                                <x-filament::icon
                                    icon="heroicon-o-book-open"
                                    class="h-4 w-4 text-gray-500 dark:text-gray-400" />

                            </div>

                            <div class="min-w-0">

                                <p class="text-sm font-medium text-gray-950 dark:text-white">
                                    {{ $nama }}
                                </p>

                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $kode }}
                                </p>

                            </div>

                        </div>

                        @endforeach

                    </div>

                </div>


                {{-- Footer --}}
                <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">

                    <div class="flex gap-3">

                        <x-filament::icon
                            icon="heroicon-o-information-circle"
                            class="mt-0.5 h-5 w-5 shrink-0 text-gray-500 dark:text-gray-400" />

                        <p class="text-sm text-gray-600 dark:text-gray-400">

                            Jika jadwal belum diperbarui, silakan hubungi

                            <span class="font-semibold text-gray-900 dark:text-white">
                                Admin Prodi
                            </span>.

                        </p>

                    </div>

                </div>

                @else

                <div class="text-center">

                    <p class="mx-auto max-w-xl text-gray-600 dark:text-gray-400">
                        {{ $eligibilityMessage }}
                    </p>

                </div>

                @endif


                {{-- Tombol keuangan --}}
                @if(
                str_contains($eligibilityMessage, 'tunggakan')
                ||
                str_contains($eligibilityMessage, 'Syarat pembayaran')
                )

                <div class="flex justify-center pt-2">

                    <x-filament::button
                        tag="a"
                        href="/mahasiswa/tagihan-mahasiswas"
                        color="warning"
                        icon="heroicon-o-banknotes">
                        Selesaikan Tagihan Anda
                    </x-filament::button>

                </div>

                @endif

            </div>

        </div>

    </div>

    @else

    {{-- ============================================================
             HEADER
        ============================================================= --}}

    <div class="mb-4 rounded-xl bg-primary-50 p-4 ring-1 ring-primary-200 dark:bg-primary-900/30 dark:ring-primary-800">

        <h3 class="text-lg font-bold text-primary-800 dark:text-primary-300">
            Pengisian KRS — {{ $activeTa?->nama_tahun }}
        </h3>

        <p class="mt-1 text-sm text-primary-700 dark:text-primary-400">
            Periksa mata kuliah dan jadwal yang ditawarkan untuk semester ini.
            Sistem akan memeriksa batas SKS, bentrok jadwal, dan kapasitas kelas saat diajukan.
        </p>

    </div>


    {{-- ============================================================
             FORM
        ============================================================= --}}

    <div class="space-y-6">

        {{ $this->form }}


        {{-- ========================================================
                 TOMBOL AJUKAN
            ========================================================= --}}

        <div class="mt-6">

            <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-end">

                <x-filament::button
                    wire:click="mountAction('ajukanKrs')"
                    icon="heroicon-o-paper-airplane"
                    size="lg">
                    Ajukan KRS ke Dosen Wali
                </x-filament::button>

            </div>

            <p class="mt-2 text-center text-xs text-gray-500 sm:text-right dark:text-gray-400">
                Pastikan mata kuliah dan jadwal sudah benar sebelum mengajukan KRS.
            </p>

        </div>

    </div>

    @endif


    {{-- ================================================================
         FILAMENT ACTION MODALS
    ================================================================= --}}

    <x-filament-actions::modals />

</x-filament-panels::page>