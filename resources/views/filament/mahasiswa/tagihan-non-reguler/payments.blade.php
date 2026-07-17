<div class="space-y-4">

    @forelse($record->pembayarans as $payment)

        @php

            $status = match($payment->status_verifikasi_id) {

                \App\Enums\StatusVerifikasiPembayaran::VERIFIED => [
                    'label' => 'Terverifikasi',
                    'class' => 'bg-success-50 text-success-700 ring-success-600/20',
                    'dot'   => 'bg-success-500',
                ],

                \App\Enums\StatusVerifikasiPembayaran::PENDING => [
                    'label' => 'Menunggu Verifikasi',
                    'class' => 'bg-warning-50 text-warning-700 ring-warning-600/20',
                    'dot'   => 'bg-warning-500',
                ],

                \App\Enums\StatusVerifikasiPembayaran::REJECTED => [
                    'label' => 'Ditolak',
                    'class' => 'bg-danger-50 text-danger-700 ring-danger-600/20',
                    'dot'   => 'bg-danger-500',
                ],

                default => [
                    'label' => 'Tidak Diketahui',
                    'class' => 'bg-gray-50 text-gray-700 ring-gray-500/20',
                    'dot'   => 'bg-gray-400',
                ],

            };

        @endphp



        <div
            class="
            group
            rounded-2xl
            border
            border-horizon-500/20
            bg-white
            p-5
            shadow-sm
            transition-all
            hover:-translate-y-0.5
            hover:shadow-md
            dark:bg-[#131B2E]
            "
        >


            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">


                {{-- LEFT --}}
                <div class="flex items-start gap-4">


                    <div
                        class="
                        flex
                        h-10
                        w-10
                        items-center
                        justify-center
                        rounded-xl
                        bg-horizon-50
                        dark:bg-horizon-500/10
                        "
                    >

                        <x-heroicon-o-banknotes
                            class="h-5 w-5 text-horizon-600"
                        />

                    </div>



                    <div>


                        <div
                            class="
                            font-semibold
                            text-gray-900
                            dark:text-white
                            "
                        >

                            {{ $payment->tanggal_bayar?->format('d M Y, H:i') }}

                        </div>



                        <div
                            class="
                            mt-1
                            text-sm
                            text-gray-500
                            dark:text-gray-400
                            "
                        >

                            {{ $payment->metode_pembayaran?->label() ?? '-' }}

                        </div>


                    </div>


                </div>





                {{-- RIGHT --}}
                <div
                    class="
                    flex
                    flex-col
                    items-start
                    gap-2
                    sm:items-end
                    "
                >


                    <div
                        class="
                        font-mono
                        text-lg
                        font-bold
                        tabular-nums
                        text-crest-600
                        dark:text-horizon-400
                        "
                    >

                        Rp {{ number_format(
                            $payment->nominal_bayar,
                            0,
                            ',',
                            '.'
                        ) }}

                    </div>




                    <span
                        class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-full
                        px-3
                        py-1
                        text-xs
                        font-semibold
                        ring-1
                        ring-inset
                        {{ $status['class'] }}
                        "
                    >

                        <span
                            class="
                            h-2
                            w-2
                            rounded-full
                            {{ $status['dot'] }}
                            "
                        ></span>


                        {{ $status['label'] }}


                    </span>


                </div>


            </div>


        </div>



    @empty


        <div
            class="
            rounded-xl
            border
            border-dashed
            border-gray-300
            p-8
            text-center
            text-gray-500
            dark:border-gray-700
            "
        >

            <x-heroicon-o-banknotes
                class="mx-auto mb-3 h-8 w-8 text-gray-400"
            />

            Belum terdapat riwayat pembayaran.


        </div>


    @endforelse


</div>