
    <div class="overflow-x-auto">

        <table class="w-full min-w-[700px] text-sm">

            <thead>

                <tr
                    class="
                    border-b
                    border-horizon-500/20
                    bg-horizon-50/50
                    dark:bg-[#182238]
                    "
                >

                    <th
                        class="
                        px-5 py-4
                        text-left
                        font-semibold
                        text-gray-700
                        dark:text-gray-200
                        "
                    >
                        Komponen Biaya
                    </th>


                    <th
                        class="
                        px-5 py-4
                        text-right
                        font-semibold
                        text-gray-700
                        dark:text-gray-200
                        "
                    >
                        Tagihan
                    </th>


                    <th
                        class="
                        px-5 py-4
                        text-right
                        font-semibold
                        text-gray-700
                        dark:text-gray-200
                        "
                    >
                        Terbayar
                    </th>


                    <th
                        class="
                        px-5 py-4
                        text-right
                        font-semibold
                        text-gray-700
                        dark:text-gray-200
                        "
                    >
                        Sisa
                    </th>

                </tr>

            </thead>



            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">


                @forelse($record->details as $detail)


                    @php

                        $sisa =
                            $detail->nominal_tagihan -
                            $detail->nominal_terbayar;

                    @endphp


                    <tr
                        class="
                        transition
                        hover:bg-horizon-50/40
                        dark:hover:bg-white/5
                        "
                    >


                        {{-- Komponen --}}
                        <td class="px-5 py-4">

                            <div
                                class="
                                font-semibold
                                text-gray-900
                                dark:text-white
                                "
                            >
                                {{ $detail->nama_komponen_snapshot }}
                            </div>


                            <div class="mt-1 text-xs text-gray-500">
                                Komponen biaya akademik
                            </div>

                        </td>




                        {{-- Tagihan --}}
                        <td
                            class="
                            px-5 py-4
                            text-right
                            font-mono
                            tabular-nums
                            text-gray-700
                            dark:text-gray-300
                            "
                        >

                            Rp {{ number_format(
                                $detail->nominal_tagihan,
                                0,
                                ',',
                                '.'
                            ) }}

                        </td>





                        {{-- Terbayar --}}
                        <td
                            class="
                            px-5 py-4
                            text-right
                            font-mono
                            tabular-nums
                            text-success-600
                            dark:text-success-400
                            "
                        >

                            Rp {{ number_format(
                                $detail->nominal_terbayar,
                                0,
                                ',',
                                '.'
                            ) }}

                        </td>






                        {{-- Sisa --}}
                        <td
                            class="
                            px-5 py-4
                            text-right
                            "
                        >

                            @if($sisa <= 0)

                                <span
                                    class="
                                    inline-flex
                                    items-center
                                    rounded-full
                                    bg-success-50
                                    px-3 py-1
                                    text-xs
                                    font-semibold
                                    text-success-700
                                    ring-1 ring-success-600/20
                                    "
                                >

                                    Lunas

                                </span>


                            @else


                                <div
                                    class="
                                    font-mono
                                    font-semibold
                                    tabular-nums
                                    text-danger-600
                                    "
                                >

                                    Rp {{ number_format(
                                        $sisa,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </div>


                            @endif


                        </td>



                    </tr>


                @empty


                    <tr>

                        <td
                            colspan="4"
                            class="
                            px-5 py-10
                            text-center
                            text-gray-500
                            "
                        >

                            Belum terdapat rincian komponen biaya.

                        </td>

                    </tr>


                @endforelse



            </tbody>


        </table>


    </div>

