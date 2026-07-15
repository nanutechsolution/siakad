<x-filament-panels::page>

    {{-- HERO PROFILE --}}
    <div class="
relative overflow-hidden
rounded-2xl
border border-horizon-300/30
bg-gradient-to-br
from-white via-horizon-50 to-white
p-5
shadow-sm

dark:from-gray-900
dark:via-gray-900
dark:to-gray-950

sm:p-8
">


        <div class="
absolute
right-[-80px]
top-[-80px]
h-56
w-56
rounded-full
bg-horizon-500/10
blur-3xl
">
        </div>



        <div class="
relative
flex
flex-col
items-center
gap-5

sm:flex-row
sm:items-start
">


            {{-- FOTO --}}
            <div class="shrink-0">


                <div class="
relative
h-24
w-24

sm:h-32
sm:w-32

overflow-hidden
rounded-full
border-4
border-white
bg-gradient-to-br
from-crest-600
to-horizon-500
shadow-xl

dark:border-gray-800
">


                    @if($this->mahasiswa->person->photo_path)

                    <img
                        src="{{ asset('storage/'.$this->mahasiswa->person->photo_path) }}"
                        class="
h-full
w-full
object-cover
" />

                    @else

                    <span class="
flex
h-full
items-center
justify-center
text-3xl
font-bold
text-white
">
                        {{ strtoupper(substr($this->mahasiswa->person->nama_lengkap,0,1)) }}
                    </span>

                    @endif


                </div>


            </div>




            {{-- INFO --}}
            <div class="
min-w-0
text-center

sm:text-left
">


                <h1 class="
truncate
font-display
text-2xl
font-semibold
text-gray-900

sm:text-3xl

dark:text-white
">

                    {{ $this->mahasiswa->person->nama_lengkap }}

                </h1>



                <p class="
mt-1
truncate
text-sm
text-gray-500
dark:text-gray-400
">

                    {{ $this->mahasiswa->prodi->nama_prodi ?? '-' }}

                </p>



                {{-- BADGES --}}
                <div class="
mt-4
flex
flex-wrap
justify-center
gap-2

sm:justify-start
">


                    <div class="
rounded-xl
bg-gray-100
px-3
py-2
dark:bg-gray-800
">

                        <p class="text-[10px] text-gray-500">
                            NIM
                        </p>

                        <p class="
font-mono
text-sm
font-semibold
">
                            {{ $this->mahasiswa->nim }}
                        </p>


                    </div>



                    <div class="
rounded-xl
bg-gray-100
px-3
py-2
dark:bg-gray-800
">

                        <p class="text-[10px] text-gray-500">
                            Angkatan
                        </p>

                        <p class="text-sm font-semibold">
                            {{ $this->mahasiswa->angkatan_id }}
                        </p>


                    </div>



                    <div class="
rounded-xl
bg-emerald-50
px-3
py-2
dark:bg-emerald-950/30
">

                        <p class="
text-[10px]
text-emerald-600
">
                            Status
                        </p>

                        <p class="
text-sm
font-semibold
text-emerald-700
dark:text-emerald-400
">
                            Aktif
                        </p>


                    </div>


                </div>



            </div>


        </div>


    </div>




    {{-- FORM --}}
    <form
        wire:submit="save"
        class="mt-6 sm:mt-8">


        <div class="
rounded-2xl
border
border-gray-200
bg-white

p-4

sm:p-6

shadow-sm

dark:border-gray-800
dark:bg-gray-900
">


            {{ $this->form }}


        </div>





        {{-- BUTTON --}}
        <div class="
mt-6
flex

justify-center

sm:justify-end
">


            <button
                type="submit"
                class="
flex
w-full

sm:w-auto

items-center
justify-center
gap-2

rounded-xl

bg-gradient-to-r
from-crest-600
to-crest-500

px-6
py-3

text-sm
font-semibold
text-white

shadow-lg

transition

hover:scale-[1.02]
active:scale-95
">


                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7" />

                </svg>


                Simpan Perubahan


            </button>


        </div>



    </form>


</x-filament-panels::page>