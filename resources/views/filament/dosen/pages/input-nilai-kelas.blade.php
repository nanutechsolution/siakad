<x-filament-panels::page>
    {{-- Banner Informasi Status Periode Input --}}
    @if (! $isInputOpen)
    <div class="p-4 mb-4 text-sm text-amber-800 rounded-lg bg-amber-50 dark:bg-gray-800 dark:text-amber-400 border border-amber-200 dark:border-amber-900 flex items-center gap-3">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600 flex-shrink-0" />
        <div>
            <span class="font-semibold">Periode Input Nilai Ditutup / Kelas Dikunci:</span>
            Saat ini pengisian nilai langsung tidak diizinkan. Jika kelas sudah dipublish, gunakan tombol <strong>Revisi Nilai</strong> pada baris mahasiswa untuk mengajukan perubahan.
        </div>
    </div>
    @else
    <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 border border-blue-200 dark:border-blue-900 flex items-center gap-3">
        <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 flex-shrink-0" />
        <div>
            <strong>Petunjuk Pengisian:</strong> Isikan nilai komponen langsung pada kolom tabel. Nilai terinput tersimpan secara otomatis. Setelah selesai mengisi seluruh komponen, klik tombol <strong>1. Hitung Nilai Akhir</strong> lalu <strong>2. Submit & Publish Kelas</strong>.
        </div>
    </div>
    @endif

    {{ $this->table }}
</x-filament-panels::page>