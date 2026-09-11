<x-filament-panels::page>

    {{-- =========================================================
        RINGKASAN
    ========================================================== --}}

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mb-6">

        {{-- Total Kelas --}}
        <x-filament::section>
            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Kelas
                    </div>

                    <div class="mt-1 text-3xl font-bold text-gray-950 dark:text-white">
                        {{ number_format($this->getTotalKelas()) }}
                    </div>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 dark:bg-primary-500/10">
                    <x-heroicon-o-academic-cap
                        class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                </div>

            </div>
        </x-filament::section>


        {{-- Mahasiswa Tanpa Kelas --}}
        <x-filament::section>
            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Mahasiswa Belum Punya Kelas
                    </div>

                    <div class="mt-1 text-3xl font-bold text-danger-600">
                        {{ number_format($this->getTotalMahasiswaTanpaKelas()) }}
                    </div>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-danger-50 dark:bg-danger-500/10">
                    <x-heroicon-o-user-group
                        class="h-6 w-6 text-danger-600 dark:text-danger-400" />
                </div>

            </div>
        </x-filament::section>

    </div>

 

    {{-- =========================================================
        MAHASISWA BELUM PUNYA KELAS
    ========================================================== --}}

    {{ $this->table }}

</x-filament-panels::page>