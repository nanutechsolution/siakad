@php
$record = $getRecord();

$persen = $record->persentase_pembayaran;

$sisa = $record->sisa_tagihan;

$warnaStatus = match ($record->status_bayar) {
'LUNAS' => 'success',
'CICIL' => 'warning',
default => 'danger',
};

$warnaProgress = match ($record->status_bayar) {
'LUNAS' => 'bg-success-600',
'CICIL' => 'bg-warning-500',
default => 'bg-danger-600',
};
@endphp

<div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

    {{-- Header --}}
    <div
        class="flex flex-col gap-4 border-b border-gray-100 p-6 md:flex-row md:items-center md:justify-between dark:border-gray-800">

        <div>

            <p class="text-sm text-gray-500">
                Invoice
            </p>

            <h2 class="text-2xl font-bold tracking-tight">
                {{ $record->kode_transaksi }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $record->tahunAkademik->nama_tahun }}
            </p>

        </div>

        <div>

            <x-filament::badge
                :color="$warnaStatus"
                size="lg">

                {{ $record->status_bayar }}

            </x-filament::badge>

        </div>

    </div>

    {{-- Statistik --}}
    <div class="grid gap-4 p-6 md:grid-cols-3">

        <div class="rounded-xl bg-primary-50 p-5 dark:bg-primary-900/20">

            <div class="text-sm text-gray-500">
                Total Tagihan
            </div>

            <div class="mt-2 text-2xl font-bold">

                Rp {{ number_format($record->total_tagihan,0,',','.') }}

            </div>

        </div>

        <div class="rounded-xl bg-success-50 p-5 dark:bg-success-900/20">

            <div class="text-sm text-gray-500">
                Sudah Dibayar
            </div>

            <div class="mt-2 text-2xl font-bold text-success-600">

                Rp {{ number_format($record->total_bayar,0,',','.') }}

            </div>

        </div>

        <div class="rounded-xl bg-danger-50 p-5 dark:bg-danger-900/20">

            <div class="text-sm text-gray-500">
                Sisa Tagihan
            </div>

            <div class="mt-2 text-2xl font-bold text-danger-600">

                Rp {{ number_format($sisa,0,',','.') }}

            </div>

        </div>

    </div>

    {{-- Progress --}}
    <div class="px-6 pb-6">

        <div class="mb-2 flex items-center justify-between">

            <span class="text-sm font-medium">

                Progress Pembayaran

            </span>

            <span class="text-sm font-bold">

                {{ $persen }}%

            </span>

        </div>

        <div
            class="h-3 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">

            <div
                class="{{ $warnaProgress }} h-full rounded-full transition-all duration-500"
                style="width: {{ $persen }}%">
            </div>

        </div>

        <div
            class="mt-3 flex items-center justify-between text-xs text-gray-500">

            <span>

                Dibayar

                <strong>

                    Rp {{ number_format($record->total_bayar,0,',','.') }}

                </strong>

            </span>

            <span>

                Total

                <strong>

                    Rp {{ number_format($record->total_tagihan,0,',','.') }}

                </strong>

            </span>

        </div>

    </div>

</div>