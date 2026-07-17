@php
$record = $getRecord();

$payments = $record->pembayaran()
->orderByDesc('tanggal_bayar')
->get();
@endphp

<div class="space-y-4">
    @forelse($payments as $payment)
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex justify-between items-start">
            <div>
                <div class="font-semibold text-lg">
                    Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    {{ \Carbon\Carbon::parse($payment->tanggal_bayar)->translatedFormat('d F Y H:i') }}
                </div>
            </div>

            {{-- 1. PERBAIKAN BADGE: Langsung panggil method dari Enum Anda --}}
            <x-filament::badge :color="$payment->status_verifikasi_id?->badgeColor() ?? 'gray'">
                {{ $payment->status_verifikasi_id?->label() ?? 'Menunggu Verifikasi' }}
            </x-filament::badge>
        </div>

        <div class="grid md:grid-cols-2 gap-5 mt-5">
            <div>
                <div class="text-xs uppercase text-gray-500">
                    Metode
                </div>
                <div class="font-medium mt-1">
                    {{ $payment->metode_bayar ?? 'Transfer Bank' }}
                </div>
            </div>
            <div>
                <div class="text-xs uppercase text-gray-500">
                    Nomor Referensi
                </div>
                <div class="font-medium mt-1">
                    {{ $payment->nomor_referensi ?? '-' }}
                </div>
            </div>
        </div>

        @if($payment->catatan_verifikasi)
        <div class="mt-5 rounded-xl bg-danger-50 dark:bg-danger-950/30 p-4 border border-danger-200 dark:border-danger-900/50">
            <div class="text-xs font-semibold uppercase text-danger-700 dark:text-danger-400">
                Alasan Penolakan dari Admin
            </div>
            <div class="mt-2 text-sm text-danger-600 dark:text-danger-300">
                {{ $payment->catatan_verifikasi }}
            </div>
        </div>
        @endif

        {{-- Catatan tambahan dari mahasiswa saat upload (jika ada) --}}
        @if($payment->catatan)
        <div class="mt-3 text-xs text-gray-500 italic">
            *Catatan Anda: {{ $payment->catatan }}
        </div>
        @endif

        @if($payment->bukti_bayar_path)
        <div class="mt-5">
            <a href="{{ route('pembayaran.bukti.download', $payment->id) }}" target="_blank">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-photo">
                    Lihat Bukti Transfer
                </x-filament::button>
            </a>
        </div>
        @endif
    </div>
    @empty
    <div class="rounded-xl border border-dashed border-gray-300 p-12 text-center">
        <div class="text-gray-500">
            Belum ada riwayat pembayaran.
        </div>
    </div>
    @endforelse
</div>