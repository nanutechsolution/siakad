<x-filament-panels::page>
    @php
        $totalSesi = $sesiList->count();
        $circumference = 2 * M_PI * 52;
        $offset = $circumference - ($rataRataKehadiran / 100) * $circumference;
        $heroColor = $rataRataKehadiran >= $ambangBatasPersen ? 'success' : 'danger';
    @endphp

    {{-- ============ HERO ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-1 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-6 flex flex-col items-center justify-center">
            <div class="relative w-32 h-32">
                <svg viewBox="0 0 120 120" class="w-32 h-32 -rotate-90">
                    <circle cx="60" cy="60" r="52" stroke-width="10" fill="none"
                        class="text-gray-100 dark:text-white/5" stroke="currentColor" />
                    <circle cx="60" cy="60" r="52" stroke-width="10" fill="none" stroke-linecap="round"
                        class="text-{{ $heroColor }}-500" stroke="currentColor"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $offset }}"
                        style="transition: stroke-dashoffset .6s ease" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold text-gray-950 dark:text-white">{{ $rataRataKehadiran }}%</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 text-center leading-tight">rata-rata<br>kehadiran</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Sesi Selesai</p>
                <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ $totalSesi }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Mahasiswa Terdaftar</p>
                <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ $mahasiswaList->count() }}</p>
            </div>
            <div class="rounded-xl border {{ $mahasiswaBerisiko > 0 ? 'border-danger-200 dark:border-danger-500/30 bg-danger-50/60 dark:bg-danger-500/5' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900' }} p-4">
                <p class="text-xs font-medium {{ $mahasiswaBerisiko > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-500 dark:text-gray-400' }}">
                    Di Bawah {{ $ambangBatasPersen }}%
                </p>
                <p class="mt-1 text-2xl font-bold {{ $mahasiswaBerisiko > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-950 dark:text-white' }}">
                    {{ $mahasiswaBerisiko }}
                </p>
            </div>
        </div>
    </div>

    {{-- ============ GRID / TABLE ============ --}}
    <div x-data="{ search: '' }" class="mt-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-b border-gray-100 dark:border-white/10">
            <div class="relative w-full sm:w-64">
                <input
                    type="text"
                    x-model="search"
                    placeholder="Cari nama atau NIM..."
                    class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm focus:ring-primary-500 focus:border-primary-500"
                />
            </div>

            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                @foreach (\App\Enums\StatusKehadiranEnum::cases() as $case)
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm" style="background-color: rgb(var(--{{ $case->getColor() }}-500) / 0.7)"></span>
                        {{ $case->getLabel() }}
                    </span>
                @endforeach
            </div>
        </div>

        @if ($totalSesi === 0)
            <div class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                Belum ada sesi perkuliahan yang berstatus <strong>selesai</strong> untuk direkap.
            </div>
        @elseif ($mahasiswaList->isEmpty())
            <div class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                Belum ada mahasiswa yang mengambil KRS pada jadwal ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/10">
                            <th class="sticky left-0 z-10 bg-white dark:bg-gray-900 text-left font-medium text-gray-500 dark:text-gray-400 px-4 py-3 min-w-[220px]">
                                Mahasiswa
                            </th>
                            @foreach ($sesiList as $sesi)
                                <th class="text-center font-medium text-gray-500 dark:text-gray-400 px-1.5 py-3 min-w-[34px]" title="{{ \Illuminate\Support\Carbon::parse($sesi->waktu_mulai_rencana)->translatedFormat('d M Y') }}">
                                    P{{ $sesi->pertemuan_ke }}
                                </th>
                            @endforeach
                            <th class="text-left font-medium text-gray-500 dark:text-gray-400 px-4 py-3 min-w-[120px]">Pola</th>
                            <th class="text-center font-medium text-gray-500 dark:text-gray-400 px-4 py-3 min-w-[80px]">Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($matrix as $row)
                            @php
                                $nim = $row['mahasiswa']->nim;
                                $nama = $row['mahasiswa']->person->nama_lengkap ?? '-';
                                $berisiko = $row['persentase_hadir'] < $ambangBatasPersen;
                                $initial = Str::of($nama)->substr(0, 1)->upper();
                            @endphp
                            <tr
                                x-show="!search || {{ \Illuminate\Support\Js::from(Str::lower($nim.' '.$nama)) }}.includes(search.toLowerCase())"
                                class="border-b border-gray-50 dark:border-white/5 {{ $berisiko ? 'bg-danger-50/40 dark:bg-danger-500/5' : '' }} hover:bg-gray-50 dark:hover:bg-white/5"
                            >
                                <td class="sticky left-0 z-10 {{ $berisiko ? 'bg-danger-50/40 dark:bg-danger-500/5' : 'bg-white dark:bg-gray-900' }} px-4 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex-none w-7 h-7 rounded-full bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 text-xs font-semibold flex items-center justify-center">
                                            {{ $initial }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-950 dark:text-white truncate">{{ $nama }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $nim }}</p>
                                        </div>
                                    </div>
                                </td>

                                @foreach ($sesiList as $sesi)
                                    @php $status = $row['sesi'][$sesi->id]; @endphp
                                    <td class="text-center px-1.5 py-2.5">
                                        <span
                                            title="Pertemuan {{ $sesi->pertemuan_ke }} — {{ $status->getLabel() }}"
                                            class="inline-flex items-center justify-center w-6 h-6 rounded text-[10px] font-bold cursor-default"
                                            style="background-color: rgb(var(--{{ $status->getColor() }}-500) / 0.15); color: rgb(var(--{{ $status->getColor() }}-600))"
                                        >
                                            {{ $status->value }}
                                        </span>
                                    </td>
                                @endforeach

                                <td class="px-4 py-2.5">
                                    <div class="flex h-2 w-full rounded-full overflow-hidden bg-gray-100 dark:bg-white/5">
                                        @foreach (\App\Enums\StatusKehadiranEnum::cases() as $case)
                                            @php $count = $row['rekap'][$case->value]; @endphp
                                            @if ($count > 0)
                                                <span
                                                    style="width: {{ $totalSesi > 0 ? ($count / $totalSesi * 100) : 0 }}%; background-color: rgb(var(--{{ $case->getColor() }}-500))"
                                                    title="{{ $case->getLabel() }}: {{ $count }}"
                                                ></span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-4 py-2.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold"
                                        style="background-color: rgb(var(--{{ $berisiko ? 'danger' : 'success' }}-500) / 0.12); color: rgb(var(--{{ $berisiko ? 'danger' : 'success' }}-600))">
                                        {{ $row['persentase_hadir'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>