@props([
'nominalBayar',
'nominalTerbilang',
'sisaTagihan',
'sisaSetelahBayar',
'kelebihan',
'namaBankLengkap',
'waktuTransfer',
'catatan'
])

<div class="space-y-4">
    {{-- KOTAK 1: RINCIAN NOMINAL --}}
    <div class="bg-gray-50 dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="text-center border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
            <span class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                Nominal Yang Akan Dilaporkan
            </span>
            <strong class="text-4xl font-extrabold text-primary-600 dark:text-primary-400">
                Rp {{ number_format($nominalBayar, 0, ',', '.') }}
            </strong>
            <div class="text-sm text-gray-600 dark:text-gray-400 font-medium italic mt-2">
                ({{ $nominalTerbilang }})
            </div>
        </div>

        <div class="flex justify-between items-center mb-2 text-sm text-gray-700 dark:text-gray-300">
            <span>Sisa Tagihan Saat Ini:</span>
            <strong>Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</strong>
        </div>

        @if($kelebihan > 0)
        <div class="p-3 bg-warning-50 dark:bg-warning-500/10 border border-warning-200 dark:border-warning-500 rounded-lg mt-3">
            <strong class="text-warning-700 dark:text-warning-400 flex items-center gap-2 text-sm">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                Peringatan: Anda Membayar Lebih
            </strong>
            <p class="text-warning-600 dark:text-warning-500 text-xs mt-1 leading-relaxed">
                Nominal ini melebihi tagihan. Kelebihan <strong>Rp {{ number_format($kelebihan, 0, ',', '.') }}</strong> akan masuk ke saldo/kredit Anda.
            </p>
        </div>
        @else
        <div class="flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-3 mt-3 text-sm text-gray-700 dark:text-gray-300">
            <span>Sisa Tagihan Nanti:</span>
            <strong class="text-success-600 dark:text-success-400">
                Rp {{ number_format($sisaSetelahBayar, 0, ',', '.') }}
            </strong>
        </div>
        @endif
    </div>

    {{-- KOTAK 2: DETAIL TUJUAN --}}
    <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <h4 class="font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-2 mb-3">
            Detail Tujuan Transfer
        </h4>
        <div class="space-y-3 text-sm">
            <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span class="text-gray-500">Dikirim ke Rekening:</span>
                <strong class="text-right text-gray-900 dark:text-gray-100">{!! $namaBankLengkap !!}</strong>
            </div>
            <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span class="text-gray-500">Waktu Transaksi:</span>
                <strong class="text-right text-gray-900 dark:text-gray-100">{{ $waktuTransfer }} WIB</strong>
            </div>
            <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span class="text-gray-500">Catatan Anda:</span>
                <strong class="text-right italic text-gray-600 dark:text-gray-400">"{{ $catatan }}"</strong>
            </div>
        </div>
    </div>

    {{-- KOTAK 3: REMINDER --}}
    <div class="text-center text-xs text-gray-500 dark:text-gray-400 mt-2 px-4">
        Pastikan nominal, tanggal, dan bank tujuan <strong>sama persis</strong> dengan foto bukti transfer yang Anda unggah sebelum menekan tombol Kirim.
    </div>
</div>