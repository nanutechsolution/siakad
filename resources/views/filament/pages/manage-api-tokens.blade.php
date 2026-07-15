<x-filament-panels::page>
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            Manajemen Token Integrasi
        </h2>

        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Gunakan tombol di atas untuk membuat token baru untuk sistem PMB.
        </p>

        @if ($newToken)
        <div class="rounded border-l-4 border-yellow-500 bg-yellow-50 p-4 dark:border-yellow-400 dark:bg-yellow-900/20">
            <p class="font-bold text-yellow-800 dark:text-yellow-300">
                PENTING: Simpan token ini sekarang!
            </p>

            <p class="text-xs text-yellow-700 dark:text-yellow-400">
                Token tidak akan ditampilkan lagi setelah halaman ini direfresh.
            </p>

            <input
                type="text"
                value="{{ $newToken }}"
                readonly
                class="mt-2 w-full rounded border border-gray-300 bg-white p-2 font-mono text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        </div>
        @else
        <div class="rounded border border-gray-200 bg-gray-50 p-4 text-sm italic text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
            Belum ada token yang digenerate. Klik "Generate Token Baru".
        </div>
        @endif
    </div>
</x-filament-panels::page>