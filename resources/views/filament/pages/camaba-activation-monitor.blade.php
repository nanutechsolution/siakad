<x-filament-panels::page>

    {{-- Hero --}}
    <x-filament::section>
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight">
                    Generate Nomor Induk Mahasiswa
                </h1>

                <p class="max-w-3xl text-sm text-gray-500 dark:text-gray-400">
                    Halaman ini menampilkan seluruh calon mahasiswa yang masih
                    menggunakan <strong>NIM sementara (PMB)</strong>.
                    Setelah proses administrasi selesai, lakukan generate NIM resmi
                    agar mahasiswa dapat mengikuti seluruh proses akademik.
                </p>
            </div>

            <div class="flex items-center gap-3">

                <div class="rounded-xl border bg-primary-50 px-5 py-4 dark:bg-primary-950/20">
                    <div class="text-xs uppercase tracking-wide text-primary-600">
                        Total Camaba
                    </div>

                    <div class="mt-1 text-3xl font-bold text-primary-700 dark:text-primary-400">
                        {{ \App\Models\Mahasiswa::where('nim', 'like', 'PMB%')->count() }}
                    </div>
                </div>

            </div>

        </div>
    </x-filament::section>

    {{-- Informasi --}}
    <x-filament::section
        icon="heroicon-o-information-circle"
        heading="Informasi">
        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <p>
                • Mahasiswa pada halaman ini masih menggunakan
                <strong>NIM sementara</strong>.
            </p>
            <p>
                • Pastikan seluruh data mahasiswa telah valid sebelum
                melakukan generate NIM.
            </p>
            <p>
                • Setelah NIM resmi dibuat, data akan otomatis berpindah
                ke daftar mahasiswa aktif.
            </p>
        </div>
    </x-filament::section>
    {{ $this->table }}
</x-filament-panels::page>