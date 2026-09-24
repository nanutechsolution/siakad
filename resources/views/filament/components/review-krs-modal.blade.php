<div class="space-y-6">
    <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Informasi Mahasiswa</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-4">
                <div class="h-16 w-16 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden">
                    @if($mahasiswa->person->photo_path)
                    <img src="{{ Storage::url($mahasiswa->person->photo_path) }}" class="h-full w-full object-cover">
                    @else
                    <x-heroicon-s-user class="h-full w-full text-gray-400 p-2" />
                    @endif
                </div>
                <div>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $mahasiswa->person->nama_lengkap }}</p>
                    <p class="text-sm text-gray-500">{{ $mahasiswa->nim }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-y-2 text-sm">
                <span class="text-gray-500">Program Studi:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $mahasiswa->prodi->nama_prodi }}</span>

                <span class="text-gray-500">Angkatan:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $mahasiswa->angkatan_id }}</span>

                <span class="text-gray-500">Total SKS Diambil:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $krs->details->sum('sks_snapshot') }} SKS</span>
            </div>
        </div>
    </div>

    @php
        // Badge ringkasan: ambil dari hasil validasi LIVE, bukan flag kolom
        // is_financial_verified — keduanya bisa tidak sinkron dan membingungkan.
        $gateKeuangan = collect($hasilValidasi ?? [])
            ->firstWhere('gateCode', 'GATE_KEUANGAN');
        $semuaLolos = collect($hasilValidasi ?? [])->every(fn($h) => $h->passed);
        $jumlahGagal = collect($hasilValidasi ?? [])->filter(fn($h) => ! $h->passed)->count();

        // Judul ramah untuk tiap gate — kode sistem tetap ditampilkan kecil
        // hanya untuk staf yang butuh menelusuri log/kode error.
        $judulGate = [
            'GATE_PERIODE' => 'Periode KRS masih terbuka',
            'GATE_KONTINUITAS' => 'Status keberlanjutan kuliah',
            'GATE_KEUANGAN' => 'Ketentuan pembayaran',
            'GATE_PENAWARAN_MK' => 'Kelengkapan mata kuliah yang ditawarkan',
            'GATE_SKS' => 'Jumlah beban SKS semester ini',
            'GATE_PRASYARAT' => 'Prasyarat mata kuliah',
            'GATE_JADWAL' => 'Bentrok jadwal kuliah',
            'GATE_KUOTA' => 'Ketersediaan tempat di kelas',
        ];
    @endphp

    <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-4">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Kesiapan KRS</h3>
                <p class="text-xs text-gray-500">Ringkasan pemeriksaan otomatis. Blok merah berarti KRS belum boleh disetujui.</p>
            </div>
            @if($semuaLolos)
                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-300">
                    <x-heroicon-o-check-circle class="h-4 w-4" /> Semua pemeriksaan lolos
                </span>
            @else
                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-danger-50 px-2.5 py-1 text-xs font-semibold text-danger-700 dark:bg-danger-500/10 dark:text-danger-300">
                    <x-heroicon-o-x-circle class="h-4 w-4" /> {{ $jumlahGagal }} pemeriksaan terblokir
                </span>
            @endif
        </div>

        @if(empty($hasilValidasi))
            <p class="text-sm text-gray-500">Pemeriksaan belum tersedia karena periode akademik tidak ditemukan.</p>
        @else
            {{-- Yang lolos diringkas jadi satu baris hijau — lebih ringkas untuk user awam --}}
            @php
                $lolos = collect($hasilValidasi)->filter(fn($h) => $h->passed);
                // Pesan "OK" tidak informatif, jadi disembunyikan. Yang berisi
                // informasi nyata (mis. jumlah mata kuliah) tetap ditampilkan.
                $lolosBernut = $lolos->filter(fn($h) => trim($h->message) !== 'OK');
            @endphp
            @if($lolos->isNotEmpty())
                <div class="mb-2 rounded-lg bg-success-50 px-3 py-2 dark:bg-success-500/10">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <x-heroicon-o-check-circle class="h-4 w-4 shrink-0 text-success-600" />
                        @foreach($lolos as $h)
                            <span class="text-xs font-medium text-success-800">{{ $judulGate[$h->gateCode] ?? $h->gateCode }}</span>
                            @unless($loop->last)
                                <span class="text-success-400">·</span>
                            @endunless
                        @endforeach
                    </div>
                    @foreach($lolosBernut as $h)
                        <p class="mt-1.5 pl-6 text-xs text-success-700 dark:text-success-200">{{ $h->message }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Yang gagal ditampilkan penuh, satu per satu, dengan tindakan yang jelas --}}
            @foreach(collect($hasilValidasi)->filter(fn($h) => ! $h->passed) as $h)
                <div class="mb-2 rounded-lg bg-danger-50 px-3 py-2 dark:bg-danger-500/10">
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-x-circle class="mt-0.5 h-4 w-4 shrink-0 text-danger-600" />
                        <div>
                            <p class="text-xs font-semibold text-danger-800 dark:text-danger-300">
                                {{ $judulGate[$h->gateCode] ?? $h->gateCode }}
                            </p>
                            <p class="text-xs text-danger-700 dark:text-danger-200">{{ $h->message }}</p>
                            <p class="mt-1 text-[11px] uppercase tracking-wide text-danger-500/80">{{ $h->gateCode }}</p>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($gateKeuangan && ! $gateKeuangan->passed && $krs->status_krs === \App\Enums\KrsStatusEnum::DIAJUKAN)
                <p class="mt-1 text-xs text-gray-500">
                    Jika pembayaran mahasiswa sudah dilakukan di luar sistem, gunakan
                    <strong>Override Keuangan</strong> pada menu aksi baris KRS ini.
                </p>
            @endif
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="rounded-lg border p-3 {{ ($statusRisiko?->getColor() ?? 'gray') === 'danger' ? 'border-danger-300 bg-danger-50' : (($statusRisiko?->getColor() ?? 'gray') === 'warning' ? 'border-warning-300 bg-warning-50' : 'border-gray-200') }}">
            <p class="text-xs text-gray-500">Status Risiko Akademik</p>
            <p class="font-semibold">{{ $statusRisiko?->getLabel() ?? 'Belum Ada Data' }}</p>
            @if ($riwayatIpk->isNotEmpty())
            <p class="text-xs text-gray-500 mt-1">IPK terakhir: {{ $riwayatIpk->last()->ipk }}</p>
            @endif
        </div>

        <div class="rounded-lg border p-3 {{ $totalTunggakan > 0 ? 'border-danger-300 bg-danger-50' : 'border-success-300 bg-success-50' }}">
            <p class="text-xs text-gray-500">Status Keuangan</p>
            <p class="font-semibold">
                @if ($totalTunggakan > 0)
                Ada Tunggakan: Rp {{ number_format($totalTunggakan, 0, ',', '.') }}
                @else
                Lunas / Tidak Ada Tunggakan
                @endif
            </p>
        </div>
    </div>

    {{--
        BARU: Riwayat catatan dosen / alasan penolakan.
        Hanya tampil kalau KRS sudah pernah diproses (DISETUJUI/DITOLAK)
        dan datanya dikirim dari MahasiswaBimbingansTable::makeReviewAction().
        Ditaruh di sini (sebelum daftar mata kuliah) supaya dosen langsung
        lihat konteks keputusan sebelumnya, tanpa perlu scroll dulu.
    --}}
    @if(!empty($catatanTersimpan))
    <div class="rounded-lg border p-4 {{ $krs->status_krs === \App\Enums\KrsStatusEnum::DITOLAK ? 'border-danger-300 bg-danger-50 dark:bg-danger-500/10' : 'border-success-300 bg-success-50 dark:bg-success-500/10' }}">
        <div class="flex items-center gap-2 mb-1">
            @if($krs->status_krs === \App\Enums\KrsStatusEnum::DITOLAK)
            <x-heroicon-o-x-circle class="h-4 w-4 text-danger-600" />
            @else
            <x-heroicon-o-check-circle class="h-4 w-4 text-success-600" />
            @endif
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                {{ $krs->status_krs === \App\Enums\KrsStatusEnum::DITOLAK ? 'Alasan Penolakan' : 'Catatan Dosen Wali' }}
            </p>
        </div>
        <p class="text-sm text-gray-800 dark:text-gray-200">{{ $catatanTersimpan }}</p>
        @if(!empty($direviewPada))
        <p class="text-xs text-gray-400 mt-2">
            Diproses pada {{ \Carbon\Carbon::parse($direviewPada)->translatedFormat('d F Y, H:i') }}
        </p>
        @endif
    </div>
    @endif

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="bg-gray-50 dark:bg-white/5 px-4 py-3 border-b border-gray-200 dark:border-white/10">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Daftar Mata Kuliah</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2 font-medium">Kode</th>
                        <th class="px-4 py-2 font-medium">Mata Kuliah</th>
                        <th class="px-4 py-2 font-medium">SKS</th>
                        <th class="px-4 py-2 font-medium">Waktu & Ruang</th>
                        <th class="px-4 py-2 font-medium">Pengampu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($krs->details as $detail)
                    <tr>
                        <td class="px-4 py-3">{{ $detail->kode_mk_snapshot ?? $detail->mataKuliah?->kode_mk }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $detail->nama_mk_snapshot ?? $detail->mataKuliah?->nama_mk }}</td>
                        <td class="px-4 py-3">{{ $detail->sks_snapshot ?? $detail->mataKuliah?->sks_default }}</td>
                        <td class="px-4 py-3">
                            @if($detail->jadwalKuliah)
                            <div class="text-xs">
                                <span class="font-bold">{{ $detail->jadwalKuliah->hari }}</span>,
                                {{ \Carbon\Carbon::parse($detail->jadwalKuliah->jam_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($detail->jadwalKuliah->jam_selesai)->format('H:i') }}
                                <br>
                                <span class="text-gray-500">Kls: {{ $detail->jadwalKuliah->kelas?->nama_kelas }} | Rgn: {{ $detail->jadwalKuliah->ruang?->nama_ruang }}</span>
                            </div>
                            @else
                            <span class="text-xs text-gray-400 italic">Jadwal belum diatur</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            @if($detail->jadwalKuliah && $detail->jadwalKuliah->dosenPengampu->isNotEmpty())
                            {{ $detail->jadwalKuliah->dosenPengampu
    ->map(fn($dosen) => $dosen->person?->nama_dengan_gelar)
    ->filter()
    ->join(', ') }}
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada mata kuliah yang diambil.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-white/10 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total MK</p>
            <p class="text-2xl font-bold text-primary-600">{{ $krs->details->count() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-white/10 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total SKS</p>
            <p class="text-2xl font-bold text-primary-600">{{ $krs->details->sum('sks_snapshot') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-white/10 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider">MK Wajib</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $krs->details->filter(fn($d) => $d->mataKuliah?->sifat_mk === 'W')->count() }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-white/10 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider">MK Pilihan</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $krs->details->filter(fn($d) => $d->mataKuliah?->sifat_mk === 'P')->count() }}
            </p>
        </div>
    </div>
</div>