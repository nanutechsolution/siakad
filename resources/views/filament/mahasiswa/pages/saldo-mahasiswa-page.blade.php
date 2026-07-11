<x-filament-panels::page>
    <!-- Widget Saldo -->
    <div class="grid grid-cols-1">
        <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Saldo Deposit Saat Ini</h2>
            <div class="mt-2 text-4xl font-extrabold text-primary-600 dark:text-primary-400">
                Rp {{ number_format($this->getSaldoAttribute(), 0, ',', '.') }}
            </div>
            <p class="mt-2 text-sm text-gray-500">
                Saldo ini dapat digunakan untuk membayar tagihan kuliah Anda atau disimpan sebagai deposit.
            </p>
        </div>
    </div>

    <!-- Tabel Histori -->
    <div class="mt-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Riwayat Mutasi Saldo</h3>
        {{ $this->table }}
    </div>
</x-filament-panels::page>