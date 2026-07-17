<x-filament-panels::page>

    {{ $this->form }}


    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-white/10">

        <x-filament::actions
            :actions="$this->getFormActions()"
            alignment="end"
        />

    </div>



   @if($preview)

<x-filament::section
    class="mt-6"
>

    <x-slot name="heading">
        Preview Tagihan Non Reguler
    </x-slot>

    <x-slot name="description">
        Periksa kembali rincian biaya sebelum tagihan diterbitkan kepada mahasiswa.
    </x-slot>


    <div class="space-y-6">


        {{-- SUMMARY --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">


            <div class="rounded-xl border p-4 bg-gray-50 dark:bg-gray-900">

                <div class="text-sm text-gray-500">
                    Jenis Tagihan
                </div>

                <div class="mt-1 font-bold text-lg">
                    Non Reguler
                </div>

            </div>



            <div class="rounded-xl border p-4 bg-gray-50 dark:bg-gray-900">

                <div class="text-sm text-gray-500">
                    Deskripsi
                </div>

                <div class="mt-1 font-bold">
                    {{ $preview['deskripsi'] }}
                </div>

            </div>



            <div class="rounded-xl border p-4 bg-gray-50 dark:bg-gray-900">

                <div class="text-sm text-gray-500">
                    Tenggat Pembayaran
                </div>

                <div class="mt-1 font-bold">

                    {{ $preview['tenggat_waktu'] ?? '-' }}

                </div>

            </div>


        </div>



        {{-- TABLE DETAIL --}}

        <div>

            <h3 class="font-semibold text-lg mb-3">
                Rincian Komponen Biaya
            </h3>


            <div class="overflow-hidden rounded-xl border">


                <table class="w-full text-sm">


                    <thead class="bg-gray-100 dark:bg-gray-800">

                        <tr>

                            <th class="px-4 py-3 text-left">
                                Komponen
                            </th>


                            <th class="px-4 py-3 text-right">
                                Nominal Dasar
                            </th>


                            <th class="px-4 py-3 text-right">
                                Diskon
                            </th>


                            <th class="px-4 py-3 text-right">
                                Tagihan
                            </th>


                        </tr>

                    </thead>



                    <tbody>


                    @foreach($preview['items'] as $item)


                        <tr class="border-t">


                            <td class="px-4 py-3 font-medium">

                                {{ $item['nama_komponen'] }}

                            </td>



                            <td class="px-4 py-3 text-right">

                                Rp
                                {{ number_format($item['nominal_dasar'],0,',','.') }}

                            </td>



                            <td class="px-4 py-3 text-right text-danger-600">

                                @if($item['nominal_diskon'] > 0)

                                    - Rp
                                    {{ number_format($item['nominal_diskon'],0,',','.') }}

                                @else

                                    -

                                @endif

                            </td>



                            <td class="px-4 py-3 text-right font-semibold">

                                Rp
                                {{ number_format($item['nominal_tagihan'],0,',','.') }}

                            </td>


                        </tr>


                    @endforeach


                    </tbody>



                </table>


            </div>


        </div>




        {{-- TOTAL CARD --}}

        <div class="flex justify-end">


            <div class="rounded-xl border p-5 w-full md:w-96">


                <div class="flex justify-between text-sm">

                    <span>
                        Jumlah Komponen
                    </span>


                    <span class="font-semibold">

                        {{ count($preview['items']) }}

                    </span>


                </div>



                <div class="my-3 border-t"></div>



                <div class="flex justify-between items-center">


                    <span class="font-semibold text-lg">
                        Total Tagihan
                    </span>


                    <span class="text-2xl font-bold">

                        Rp
                        {{ number_format($preview['total_tagihan'],0,',','.') }}

                    </span>


                </div>


            </div>


        </div>




        {{-- ALERT --}}

        <div class="rounded-xl border p-4 bg-warning-50 dark:bg-warning-950/30">


            <div class="flex gap-3">


                <x-heroicon-o-exclamation-triangle
                    class="w-6 h-6 text-warning-600"/>


                <div>


                    <div class="font-semibold">
                        Konfirmasi Penerbitan Tagihan
                    </div>


                    <div class="text-sm text-gray-600 dark:text-gray-300">

                        Setelah proses generate selesai, tagihan akan muncul pada akun mahasiswa
                        dan dapat diproses untuk pembayaran.

                    </div>


                </div>


            </div>


        </div>


    </div>


</x-filament::section>


@endif


</x-filament-panels::page>