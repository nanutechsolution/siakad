<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

    {{-- Reference --}}
    <div class="siakad-summary-card">

        <div class="siakad-summary-label">
            <x-heroicon-o-qr-code class="siakad-summary-icon" />
            Nomor Referensi
        </div>


        <div class="siakad-summary-value font-mono">
            {{ $record->kode_transaksi }}
        </div>

    </div>



    {{-- Description --}}
    <div class="siakad-summary-card">

        <div class="siakad-summary-label">
            <x-heroicon-o-document-text class="siakad-summary-icon" />
            Keterangan
        </div>


        <div class="siakad-summary-value">
            {{ $record->deskripsi }}
        </div>

    </div>



    {{-- Amount --}}
    <div
        class="
        siakad-summary-card
        border-horizon-500/40
        bg-gradient-to-br
        from-horizon-50
        to-white
        dark:from-[#182238]
        dark:to-[#131B2E]
        "
    >

        <div class="siakad-summary-label">

            <x-heroicon-o-banknotes class="siakad-summary-icon"/>

            Nilai Tagihan

        </div>


        <div
            class="
            mt-3
            font-mono
            text-xl
            font-bold
            tracking-tight
            text-horizon-700
            dark:text-horizon-400
            "
        >
            Rp {{ number_format($record->total_tagihan,0,',','.') }}
        </div>

    </div>




    {{-- Status --}}
    <div class="siakad-summary-card">


        <div class="siakad-summary-label">

            <x-heroicon-o-shield-check class="siakad-summary-icon"/>

            Status Pembayaran

        </div>



        @php
            $status = match($record->status_bayar) {

                'LUNAS' => [
                    'label'=>'Lunas',
                    'class'=>'bg-success-50 text-success-700 ring-success-600/20'
                ],

                'CICIL' => [
                    'label'=>'Cicilan',
                    'class'=>'bg-warning-50 text-warning-700 ring-warning-600/20'
                ],

                default => [
                    'label'=>'Belum Lunas',
                    'class'=>'bg-danger-50 text-danger-700 ring-danger-600/20'
                ],

            };
        @endphp



        <div class="mt-3">

            <span
                class="
                inline-flex
                items-center
                rounded-full
                px-4 py-1.5
                text-sm
                font-semibold
                ring-1 ring-inset
                {{ $status['class'] }}
                "
            >

                <span class="mr-2 h-2 w-2 rounded-full bg-current"></span>

                {{ $status['label'] }}

            </span>

        </div>

    </div>


</div>