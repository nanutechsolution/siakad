<x-filament-panels::page>
    <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
        <h2 class="text-lg font-semibold">Manajemen Token Integrasi</h2>
        <p class="text-sm text-gray-500 mb-4">Gunakan tombol di atas untuk membuat token baru untuk sistem PMB.</p>

        @if ($newToken)
            <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                <p class="font-bold text-yellow-800">PENTING: Simpan token ini sekarang!</p>
                <p class="text-xs text-yellow-700">Token tidak akan ditampilkan lagi setelah halaman ini direfresh.</p>
                <input type="text" value="{{ $newToken }}" readonly class="w-full mt-2 p-2 bg-white border border-gray-300 rounded font-mono text-sm">
            </div>
        @else
            <div class="p-4 bg-gray-50 text-gray-400 text-sm italic">
                Belum ada token yang digenerate. Klik "Generate Token Baru".
            </div>
        @endif
    </div>
</x-filament-panels::page>