<x-filament-panels::page>

{{-- HEADER --}}
<div class="
    relative overflow-hidden
    rounded-2xl
    border border-horizon-300/30
    bg-gradient-to-br
    from-white via-horizon-50 to-white
    dark:from-gray-900 dark:via-gray-900 dark:to-gray-950
    p-6
    shadow-sm
">

    <div class="
        absolute -right-24 -top-24
        h-64 w-64
        rounded-full
        bg-horizon-500/10
        blur-3xl
    "></div>


    <div class="relative">

        <div class="flex gap-4 items-start">

            <div class="
                flex h-14 w-14
                items-center justify-center
                rounded-2xl
                bg-gradient-to-br
                from-crest-600
                to-horizon-500
                text-white
                shadow-lg
            ">
                <svg class="h-7 w-7"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 10h8M8 14h5m-7 6h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12l3-2z"/>

                </svg>
            </div>


            <div>

                <h1 class="
                    font-display
                    text-2xl
                    font-semibold
                    text-gray-900
                    dark:text-white
                ">
                    Formulir Evaluasi Dosen
                </h1>


                <p class="
                    mt-1
                    text-sm
                    text-gray-600
                    dark:text-gray-400
                ">
                    Berikan penilaian secara objektif untuk membantu peningkatan mutu pembelajaran.
                </p>


            </div>

        </div>


        {{-- INFO --}}
        <div class="
            mt-6
            grid
            grid-cols-1
            md:grid-cols-2
            gap-4
        ">


            <div class="
                rounded-xl
                border
                border-gray-100
                bg-white/70
                p-4
                dark:border-gray-800
                dark:bg-gray-800/50
            ">

                <p class="text-xs text-gray-500">
                    Mata Kuliah
                </p>

                <p class="
                    mt-1
                    font-semibold
                    text-gray-900
                    dark:text-white
                ">
                    {{ $jadwal->mataKuliah->nama_mata_kuliah }}
                </p>

            </div>



            <div class="
                rounded-xl
                border
                border-gray-100
                bg-white/70
                p-4
                dark:border-gray-800
                dark:bg-gray-800/50
            ">

                <p class="text-xs text-gray-500">
                    Dosen Pengampu
                </p>

                <p class="
                    mt-1
                    font-semibold
                    text-gray-900
                    dark:text-white
                ">
                    {{ $this->dosen->person->nama_dengan_gelar }}
                </p>

            </div>


        </div>


        {{-- ANONIM --}}
        <div class="
            mt-5
            flex gap-3
            rounded-xl
            border border-emerald-200
            bg-emerald-50
            p-4
            dark:border-emerald-900
            dark:bg-emerald-950/30
        ">

            <svg class="
                h-5 w-5
                text-emerald-600
            "
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24">

            <path stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 11c0-1.1.9-2 2-2h1a2 2 0 012 2v5a2 2 0 01-2 2h-1a2 2 0 01-2-2v-5z"/>

            </svg>


            <p class="
                text-sm
                text-emerald-700
                dark:text-emerald-300
            ">
                Evaluasi bersifat <strong>ANONIM</strong>.
                Dosen hanya melihat hasil agregat, bukan identitas mahasiswa.
            </p>

        </div>


    </div>

</div>



{{-- EMPTY STATE --}}
@if($totalPertanyaan === 0)


<div class="
    mt-6
    rounded-2xl
    border
    border-dashed
    border-amber-300
    bg-amber-50
    p-10
    text-center
    dark:border-amber-800
    dark:bg-amber-950/20
">


<div class="
    mx-auto
    flex h-16 w-16
    items-center justify-center
    rounded-full
    bg-amber-100
    text-amber-600
    dark:bg-amber-900/40
">

⚠️

</div>


<h3 class="
    mt-4
    text-lg
    font-semibold
    text-amber-800
    dark:text-amber-400
">
    Pertanyaan EDOM Belum Tersedia
</h3>


<p class="
    mx-auto
    mt-2
    max-w-lg
    text-sm
    text-amber-700
    dark:text-amber-500
">
    Belum ada kuesioner aktif.
    Silakan hubungi bagian akademik atau LPM untuk melakukan konfigurasi pertanyaan.
</p>


<a
href="{{ route('filament.mahasiswa.pages.daftar-edom') }}"
class="
inline-flex
mt-6
rounded-xl
bg-gray-900
px-5 py-2.5
text-sm
font-medium
text-white
dark:bg-white
dark:text-gray-900
">
Kembali
</a>


</div>


@else



<form wire:submit.prevent="simpan" class="mt-6 space-y-6">


@foreach($kelompokPertanyaan as $kelompok)


<div class="
rounded-2xl
overflow-hidden
border
border-gray-200
bg-white
shadow-sm
dark:border-gray-800
dark:bg-gray-900
">


<div class="
flex items-center gap-3
border-b
bg-gray-50
px-6 py-4
dark:border-gray-800
dark:bg-gray-800/50
">


<div class="
flex h-9 w-9
items-center justify-center
rounded-xl
bg-crest-600
text-sm
font-bold
text-white
">
{{ $loop->iteration }}
</div>


<h3 class="
font-semibold
text-gray-900
dark:text-white
">
{{ $kelompok->nama_kelompok }}
</h3>


</div>



<div class="divide-y divide-gray-100 dark:divide-gray-800">


@foreach($kelompok->pertanyaans as $index=>$pertanyaan)


<div class="p-6">


<p class="
font-medium
text-gray-900
dark:text-gray-100
">

{{ $index+1 }}.
{{ $pertanyaan->bunyi_pertanyaan }}

@if($pertanyaan->is_required)
<span class="text-red-500">*</span>
@endif

</p>



@if($pertanyaan->jenis_input === 'RATING_4')


<div class="
mt-5
grid
grid-cols-2
gap-3
md:grid-cols-4
">


@foreach([
1=>'Kurang',
2=>'Cukup',
3=>'Baik',
4=>'Sangat Baik'
] as $val=>$label)


<label class="cursor-pointer">


<input
type="radio"
wire:model.defer="ratings.{{ $pertanyaan->id }}"
value="{{ $val }}"
class="peer hidden"
>


<div class="
rounded-xl
border
p-4
text-center
transition

peer-checked:border-crest-500
peer-checked:bg-crest-500/10

hover:border-horizon-500
">


<div class="
text-xl
font-bold
text-gray-900
dark:text-white
">
{{ $val }}
</div>


<div class="text-xs text-gray-500">
{{ $label }}
</div>


</div>


</label>


@endforeach


</div>


@endif


@error('ratings.'.$pertanyaan->id)

<p class="mt-2 text-sm text-red-500">
{{ $message }}
</p>

@enderror


</div>


@endforeach


</div>


</div>


@endforeach



{{-- SARAN --}}
<div class="
rounded-2xl
border
bg-white
p-6
dark:border-gray-800
dark:bg-gray-900
">


<h3 class="
font-semibold
text-lg
text-gray-900
dark:text-white
">
Saran dan Masukan
</h3>


<textarea
wire:model.defer="saran"
rows="5"
class="
mt-4
w-full
rounded-xl
dark:bg-gray-950
"
placeholder="Tuliskan masukan Anda..."
></textarea>


</div>




<div class="
flex
justify-end
gap-3
">


<a
href="{{ route('filament.mahasiswa.pages.daftar-edom') }}"
class="
rounded-xl
bg-gray-100
px-5 py-3
text-sm
font-medium
dark:bg-gray-800
">
Batal
</a>


<button
type="submit"
class="
rounded-xl
bg-gradient-to-r
from-crest-600
to-crest-500
px-7 py-3
text-sm
font-semibold
text-white
shadow-lg
hover:scale-[1.02]
transition
">
Kirim Evaluasi
</button>


</div>


</form>


@endif


</x-filament-panels::page>