@php
    $record = $getRecord();

    $details = $record->details()
        ->orderBy('id')
        ->get();

    $totalTagihan = $details->sum('nominal_tagihan');
    $totalDiskon = $details->sum('nominal_diskon');
    $totalTerbayar = $details->sum('nominal_terbayar');
@endphp

<div class="space-y-4">

    @forelse($details as $detail)

        @php

            $nominalTagihan = (float) $detail->nominal_tagihan;
            $nominalTerbayar = (float) $detail->nominal_terbayar;

            $status = 'BELUM';
            $badgeColor = 'danger';

            if ($nominalTerbayar > 0 && $nominalTerbayar < $nominalTagihan) {
                $status = 'CICIL';
                $badgeColor = 'warning';
            }

            if ($nominalTerbayar >= $nominalTagihan) {
                $status = 'LUNAS';
                $badgeColor = 'success';
            }

            $persen = $nominalTagihan > 0
                ? round(($nominalTerbayar / $nominalTagihan) * 100)
                : 0;

        @endphp

        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-start justify-between gap-4">

                <div>

                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        {{ $detail->nama_komponen_snapshot }}
                    </h3>

                    @if($detail->deskripsi)
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $detail->deskripsi }}
                        </p>
                    @endif

                </div>

                <x-filament::badge
                    :color="$badgeColor">

                    {{ $status }}

                </x-filament::badge>

            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-4">

                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500">
                        Nominal
                    </p>

                    <p class="mt-1 font-semibold">
                        Rp {{ number_format($detail->nominal_awal,0,',','.') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500">
                        Diskon
                    </p>

                    <p class="mt-1 font-semibold text-success-600">
                        Rp {{ number_format($detail->nominal_diskon,0,',','.') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500">
                        Terbayar
                    </p>

                    <p class="mt-1 font-semibold text-primary-600">
                        Rp {{ number_format($detail->nominal_terbayar,0,',','.') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500">
                        Tagihan
                    </p>

                    <p class="mt-1 font-bold text-lg">
                        Rp {{ number_format($detail->nominal_tagihan,0,',','.') }}
                    </p>
                </div>

            </div>

            {{-- Progress per komponen --}}

            <div class="mt-5">

                <div class="mb-2 flex justify-between text-xs">

                    <span>
                        Progress Pembayaran
                    </span>

                    <span class="font-semibold">
                        {{ $persen }}%
                    </span>

                </div>

                <div
                    class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">

                    <div
                        class="h-full rounded-full transition-all duration-500
                            {{ $badgeColor === 'success' ? 'bg-success-600' : '' }}
                            {{ $badgeColor === 'warning' ? 'bg-warning-500' : '' }}
                            {{ $badgeColor === 'danger' ? 'bg-danger-600' : '' }}"
                        style="width: {{ $persen }}%">
                    </div>

                </div>

            </div>

        </div>

    @empty

        <div
            class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-500">

            Belum ada rincian komponen tagihan.

        </div>

    @endforelse

    {{-- Ringkasan Footer --}}

    <div
        class="rounded-2xl border border-primary-200 bg-primary-50 p-6 dark:border-primary-900 dark:bg-primary-950/20">

        <div class="grid gap-4 md:grid-cols-3">

            <div>

                <div class="text-sm text-gray-500">
                    Total Tagihan
                </div>

                <div class="mt-1 text-xl font-bold">
                    Rp {{ number_format($totalTagihan,0,',','.') }}
                </div>

            </div>

            <div>

                <div class="text-sm text-gray-500">
                    Total Diskon
                </div>

                <div class="mt-1 text-xl font-bold text-success-600">
                    Rp {{ number_format($totalDiskon,0,',','.') }}
                </div>

            </div>

            <div>

                <div class="text-sm text-gray-500">
                    Total Terbayar
                </div>

                <div class="mt-1 text-xl font-bold text-primary-600">
                    Rp {{ number_format($totalTerbayar,0,',','.') }}
                </div>

            </div>

        </div>

    </div>

</div>