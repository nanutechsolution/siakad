<x-filament-panels::page>

    @php
    $keanggotaan = $this->keanggotaanAktif;
    @endphp


    @if ($keanggotaan->isEmpty())


    <div class="
rounded-3xl
border
border-dashed
border-gray-300
bg-white
p-8
text-center
dark:border-gray-700
dark:bg-gray-900
">


        <div class="
mx-auto
flex
h-16
w-16
items-center
justify-center
rounded-full
bg-gray-100
dark:bg-gray-800
">

            <x-filament::icon
                icon="heroicon-o-user-group"
                class="h-8 w-8 text-gray-400" />

        </div>


        <h3 class="
mt-4
font-semibold
text-gray-900
dark:text-white
">
            Belum Memiliki Kelas Aktif
        </h3>


        <p class="
mx-auto
mt-2
max-w-md
text-sm
text-gray-500
">
            Anda belum terdaftar pada kelas akademik semester ini.
            Silakan hubungi admin akademik program studi.
        </p>


    </div>



    @else



    @foreach($keanggotaan as $anggota)


    @php

    $kelas = $anggota->kelas;

    $dosenWali = $kelas?->dosenWali?->first();

    $teman = $kelas
    ? $this->temanSekelas($kelas->id)
    : collect();

    @endphp



    @if($kelas)



    {{-- HEADER KELAS --}}
    <div class="
relative
overflow-hidden
rounded-3xl
border
border-horizon-300/30
bg-gradient-to-br
from-white
via-horizon-50
to-white
p-6

dark:from-gray-900
dark:via-gray-900
dark:to-gray-950
">


        <div class="
absolute
right-[-60px]
top-[-60px]
h-56
w-56
rounded-full
bg-horizon-500/10
blur-3xl
"></div>



        <div class="relative">


            <div class="
flex
items-center
gap-4
">


                <div class="
flex
h-14
w-14
items-center
justify-center
rounded-2xl
bg-gradient-to-br
from-crest-600
to-horizon-500
text-white
shadow-lg
">

                    <x-filament::icon
                        icon="heroicon-o-academic-cap"
                        class="h-7 w-7" />

                </div>



                <div class="min-w-0">


                    <h1 class="
truncate
font-display
text-2xl
font-semibold
text-gray-900
dark:text-white
">

                        {{ $kelas->nama_kelas }}

                    </h1>


                    <p class="
mt-1
truncate
text-sm
text-gray-500
">

                        {{ $kelas->prodi?->nama_prodi ?? '-' }}

                        <span class="mx-1">
                            •
                        </span>

                        {{ $kelas->program?->nama_program ?? '-' }}

                    </p>


                </div>


            </div>





            {{-- STAT CARD --}}
            <div class="
mt-6
grid
grid-cols-2
gap-3

lg:grid-cols-4
">


                <div class="
rounded-xl
bg-white
p-4
shadow-sm
dark:bg-gray-800
">

                    <p class="text-xs text-gray-500">
                        Angkatan
                    </p>

                    <p class="
mt-1
font-semibold
text-gray-900
dark:text-white
">
                        {{ $kelas->angkatan_id }}
                    </p>

                </div>



                <div class="
rounded-xl
bg-white
p-4
shadow-sm
dark:bg-gray-800
">

                    <p class="text-xs text-gray-500">
                        Kapasitas
                    </p>

                    <p class="
mt-1
font-semibold
text-gray-900
dark:text-white
">
                        {{ $kelas->kapasitas ?? '-' }}
                    </p>

                </div>



                <div class="
rounded-xl
bg-white
p-4
shadow-sm
dark:bg-gray-800
">

                    <p class="text-xs text-gray-500">
                        Jumlah Anggota
                    </p>

                    <p class="
mt-1
font-semibold
text-gray-900
dark:text-white
">
                        {{ $teman->count()+1 }}
                    </p>

                </div>



                <div class="
rounded-xl
bg-white
p-4
shadow-sm
dark:bg-gray-800
">

                    <p class="text-xs text-gray-500">
                        Bergabung
                    </p>

                    <p class="
mt-1
text-sm
font-semibold
text-gray-900
dark:text-white
">

                        {{ $anggota->tanggal_masuk?->translatedFormat('d M Y') ?? '-' }}

                    </p>

                </div>


            </div>


        </div>


    </div>






    {{-- DOSEN WALI --}}
    <div class="
mt-6
rounded-3xl
border
bg-white
p-5

dark:border-gray-800
dark:bg-gray-900
">


        <h2 class="
font-semibold
text-gray-900
dark:text-white
">
            Dosen Wali / PA
        </h2>



        <div class="
mt-4
flex
items-center
gap-4
rounded-2xl
bg-gray-50
p-4
dark:bg-gray-800/50
">


            <div class="
flex
h-12
w-12
items-center
justify-center
rounded-full
bg-gradient-to-br
from-crest-500
to-horizon-500
font-bold
text-white
">

                {{ strtoupper(substr(
data_get($dosenWali,'person.nama_dengan_gelar','D'),
0,
1
)) }}

            </div>



            <div class="min-w-0">

                <p class="
truncate
font-medium
text-gray-900
dark:text-white
">

                    {{ data_get(
$dosenWali,
'person.nama_dengan_gelar',
'Belum ditentukan'
) }}

                </p>


                <p class="text-sm text-gray-500">
                    Pembimbing Akademik
                </p>


            </div>


        </div>


    </div>






    {{-- TEMAN SEKELAS --}}
    <div class="
mt-6
rounded-3xl
border
bg-white
p-5

dark:border-gray-800
dark:bg-gray-900
">


        <div class="
flex
items-center
justify-between
">


            <h2 class="
font-semibold
text-gray-900
dark:text-white
">
                Teman Sekelas
            </h2>


            <span class="
rounded-full
bg-crest-500/10
px-3
py-1
text-xs
font-medium
text-crest-600
">

                {{ $teman->count()+1 }} Mahasiswa

            </span>


        </div>



        @if($teman->isEmpty())


        <p class="
mt-4
text-sm
text-gray-500
">
            Belum ada teman sekelas.
        </p>


        @else


        <div class="
mt-5
grid
grid-cols-1

sm:grid-cols-2

xl:grid-cols-3

gap-3
">


            @foreach($teman as $mhs)


            <div class="
flex
items-center
gap-3
rounded-xl
bg-gray-50
p-3

dark:bg-gray-800
">


                <div class="
flex
h-9
w-9
shrink-0
items-center
justify-center
rounded-full
bg-gray-200
font-semibold

dark:bg-gray-700
">


                    {{ strtoupper(substr(
$mhs->person?->nama_lengkap ?? $mhs->nim,
0,
1
)) }}


                </div>



                <div class="min-w-0">


                    <p class="
truncate
text-sm
font-medium
text-gray-800

dark:text-gray-200
">

                        {{ $mhs->person?->nama_lengkap ?? $mhs->nim }}

                    </p>


                    <p class="
font-mono
text-xs
text-gray-500
">

                        {{ $mhs->nim }}

                    </p>


                </div>


            </div>


            @endforeach


        </div>


        @endif


    </div>



    @endif


    @endforeach


    @endif


</x-filament-panels::page>