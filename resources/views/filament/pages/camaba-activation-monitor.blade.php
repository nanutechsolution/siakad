<x-filament-panels::page>

    <div class="space-y-6">

        <x-filament::section>
            <div class="flex items-start justify-between gap-6">

                <div>
                    <h2 class="text-xl font-semibold tracking-tight">
                        Generate Nomor Induk Mahasiswa
                    </h2>

                    <p class="mt-2 max-w-3xl text-sm text-gray-500">
                        Halaman ini digunakan untuk memantau calon mahasiswa yang masih
                        menggunakan NIM sementara (PMB). Setelah seluruh persyaratan
                        akademik dan pembayaran terpenuhi, mahasiswa dapat diberikan
                        Nomor Induk Mahasiswa resmi.
                    </p>
                </div>

                @if ($tahunAkademikAktif)
                <x-filament::badge color="primary" size="lg">
                    {{ $tahunAkademikAktif->nama_tahun }}
                </x-filament::badge>
                @endif

            </div>
        </x-filament::section>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

            <x-filament::section compact>

                <p class="text-sm text-gray-500">
                    Total Camaba
                </p>

                <p class="mt-2 text-3xl font-bold">
                    {{ number_format($totalCamaba) }}
                </p>

            </x-filament::section>

            <x-filament::section compact>

                <p class="text-sm text-gray-500">
                    Siap Generate
                </p>

                <p class="mt-2 text-3xl font-bold text-success-600">
                    {{ number_format($siapGenerate) }}
                </p>

            </x-filament::section>

            <x-filament::section compact>

                <p class="text-sm text-gray-500">
                    Belum Memenuhi
                </p>

                <p class="mt-2 text-3xl font-bold text-warning-600">
                    {{ number_format($belumSiap) }}
                </p>

            </x-filament::section>

            <x-filament::section compact>

                <p class="text-sm text-gray-500">
                    Total Tunggakan
                </p>

                <p class="mt-2 text-xl font-bold text-danger-600">
                    Rp {{ number_format($totalTunggakan, 0, ',', '.') }}
                </p>

            </x-filament::section>

        </div>

        <x-filament::section
            heading="Progress Aktivasi">

            <div class="space-y-3">

                <div class="flex items-center justify-between">

                    <span class="text-sm text-gray-500">
                        Mahasiswa yang memenuhi syarat untuk Generate NIM
                    </span>

                    <span class="font-semibold">
                        {{ $progressAktivasi }}%
                    </span>

                </div>

                <progress
                    class="progress progress-success w-full"
                    value="{{ $progressAktivasi }}"
                    max="100">
                </progress>

            </div>
        </x-filament::section>
        {{ $this->table }}
    </div>
</x-filament-panels::page>