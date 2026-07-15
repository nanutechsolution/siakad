<x-filament-panels::page>

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl border border-horizon-300/30 
        bg-gradient-to-br from-white via-horizon-50 to-white 
        dark:from-gray-900 dark:via-gray-900 dark:to-gray-950 
        p-6 shadow-sm">

        <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full 
            bg-horizon-500/10 blur-3xl"></div>

        <div class="relative">
            <h1 class="font-display text-2xl font-semibold text-gray-900 dark:text-white">
                Evaluasi Dosen
            </h1>

            <p class="mt-2 max-w-2xl text-sm text-gray-600 dark:text-gray-400">
                Berikan penilaian terhadap proses pembelajaran yang telah berlangsung.
                Masukan Anda membantu meningkatkan kualitas akademik universitas.
            </p>
        </div>
    </div>


    {{-- Cards --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

        @forelse($kelasEvaluasi as $item)

        <div
            class="group relative overflow-hidden rounded-2xl 
            border border-gray-200 dark:border-gray-800
            bg-white dark:bg-gray-900
            shadow-sm hover:shadow-xl
            transition-all duration-300
            hover:-translate-y-1"
        >

            {{-- Accent --}}
            <div class="absolute inset-x-0 top-0 h-1 
                bg-gradient-to-r from-crest-500 via-horizon-500 to-horizon-300">
            </div>


            <div class="p-6">

                {{-- Code --}}
                <div class="flex items-center justify-between">

                    <span class="
                        inline-flex items-center
                        rounded-lg
                        bg-gray-100 dark:bg-gray-800
                        px-3 py-1
                        font-mono text-xs
                        text-gray-600 dark:text-gray-300
                    ">
                        {{ $item['mata_kuliah_kode'] }}
                    </span>


                    @if($item['is_completed'])

                        <span class="
                            inline-flex items-center gap-1
                            rounded-full
                            bg-emerald-100
                            px-3 py-1
                            text-xs font-medium
                            text-emerald-700
                            dark:bg-emerald-950
                            dark:text-emerald-300
                        ">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Selesai
                        </span>

                    @else

                        <span class="
                            inline-flex items-center gap-1
                            rounded-full
                            bg-amber-100
                            px-3 py-1
                            text-xs font-medium
                            text-amber-700
                            dark:bg-amber-950
                            dark:text-amber-300
                        ">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            Belum
                        </span>

                    @endif

                </div>


                {{-- Mata Kuliah --}}
                <h3 class="
                    mt-5
                    font-display
                    text-xl
                    font-semibold
                    leading-tight
                    text-gray-900
                    dark:text-white
                ">
                    {{ $item['mata_kuliah_nama'] }}
                </h3>


                {{-- Dosen --}}
                <div class="
                    mt-4
                    flex items-center gap-3
                    rounded-xl
                    bg-gray-50
                    p-3
                    dark:bg-gray-800/50
                ">

                    <div class="
                        flex h-10 w-10 items-center justify-center
                        rounded-full
                        bg-gradient-to-br
                        from-crest-500
                        to-horizon-500
                        text-sm
                        font-bold
                        text-white
                    ">
                        {{ strtoupper(substr($item['dosen_nama'],0,1)) }}
                    </div>


                    <div>
                        <p class="text-xs text-gray-500">
                            Dosen Pengampu
                        </p>

                        <p class="
                            text-sm
                            font-medium
                            text-gray-800
                            dark:text-gray-200
                        ">
                            {{ $item['dosen_nama'] }}
                        </p>
                    </div>

                </div>



                {{-- Footer --}}
                <div class="
                    mt-6
                    flex items-center justify-between
                    border-t
                    border-gray-100
                    pt-4
                    dark:border-gray-800
                ">


                    @if(!$item['is_completed'])

                        <a
                        href="{{ route('filament.mahasiswa.pages.isi-edom', [
                            'jadwal_id'=>$item['jadwal_kuliah_id'],
                            'dosen_id'=>$item['dosen_id']
                        ]) }}"
                        class="
                        group/button
                        inline-flex
                        items-center
                        gap-2
                        rounded-xl
                        bg-crest-600
                        px-5 py-2.5
                        text-sm
                        font-medium
                        text-white
                        shadow-md
                        transition
                        hover:bg-crest-700
                        "
                        >

                            Isi Evaluasi

                            <svg 
                            class="h-4 w-4 transition-transform group-hover/button:translate-x-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                                <path 
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"/>

                            </svg>

                        </a>


                    @else

                        <div class="
                            flex items-center gap-2
                            text-sm
                            text-emerald-600
                            dark:text-emerald-400
                        ">

                            <svg class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path 
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"/>

                            </svg>

                            Terima kasih atas evaluasi Anda

                        </div>

                    @endif


                </div>


            </div>

        </div>


        @empty

        <div class="
            col-span-full
            rounded-2xl
            border
            border-dashed
            p-10
            text-center
        ">

            <div class="text-4xl">
                📚
            </div>

            <h3 class="mt-3 font-semibold text-gray-800 dark:text-white">
                Belum Ada Evaluasi
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Tidak ada mata kuliah yang tersedia untuk dievaluasi.
            </p>

        </div>

        @endforelse

    </div>

</x-filament-panels::page>