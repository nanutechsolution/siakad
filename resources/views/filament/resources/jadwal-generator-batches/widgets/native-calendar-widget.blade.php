<x-filament-widgets::widget>
    <x-filament::section heading="📅 Kalender Visual Jadwal" description="Tampilan jadwal perkuliahan yang berhasil di-generate.">
        @if($record && $record->status === 'PREVIEW')
        @php
        // Ambil data jadwal sukses dari database
        $jadwals = $record->results()->where('is_success', true)->with(['mataKuliah', 'kelas', 'ruang'])->get();
        $haris = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        @endphp

        <!-- Grid 6 Kolom untuk 6 Hari -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            @foreach($haris as $hari)
            <div class="flex flex-col bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">

                <!-- Header Nama Hari -->
                <div class="bg-primary-600 dark:bg-primary-500 text-white font-bold text-center py-2 shadow-md">
                    {{ $hari }}
                </div>

                <!-- Isi Jadwal Per Hari -->
                <div class="p-2 flex flex-col gap-3 min-h-[150px]">
                    @php
                    $jadwalHariIni = $jadwals->where('hari', $hari)->sortBy('jam_mulai');
                    @endphp

                    @if($jadwalHariIni->isEmpty())
                    <div class="text-center text-xs text-gray-400 py-4 font-medium italic">Tidak ada kelas</div>
                    @else
                    @foreach($jadwalHariIni as $jadwal)
                    <!-- Kartu Jadwal Satuan -->
                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">

                        <!-- Jam -->
                        <div class="text-xs font-bold text-primary-600 dark:text-primary-400 mb-1 flex items-center gap-1">
                            <x-heroicon-o-clock class="w-4 h-4" />
                            {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                        </div>

                        <!-- Mata Kuliah -->
                        <div class="font-semibold text-sm leading-tight mb-2 text-gray-900 dark:text-gray-100">
                            {{ $jadwal->mataKuliah?->nama_mk ?? 'Matkul ?' }}
                        </div>

                        <!-- Footer Kartu: Kelas & Ruang -->
                        <div class="text-xs text-gray-600 dark:text-gray-400 flex justify-between items-center mt-2 pt-2 border-t dark:border-gray-700">
                            <span class="font-medium bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-primary-600 dark:text-primary-400">
                                Kls {{ $jadwal->kelas?->nama_kelas ?? '?' }}
                            </span>
                            <span class="font-medium bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded truncate max-w-[80px]" title="{{ $jadwal->ruang?->nama_ruang ?? '?' }}">
                                🚪 {{ $jadwal->ruang?->nama_ruang ?? '?' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center text-gray-500 py-8 flex flex-col items-center gap-2">
            <x-heroicon-o-calendar class="w-12 h-12 text-gray-400" />
            <p>Silakan Generate Jadwal terlebih dahulu untuk melihat kalender visual.</p>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>