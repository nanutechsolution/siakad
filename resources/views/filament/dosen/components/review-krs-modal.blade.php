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
                <span class="font-medium text-gray-900 dark:text-white">{{ $krs->krsDetails->sum('sks_snapshot') }} SKS</span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="bg-gray-50 dark:bg-white/5 px-4 py-3 border-b border-gray-200 dark:border-white/10">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Validasi Sistem</h3>
        </div>
        <div class="p-4 space-y-3">
            @foreach($hasilValidasi as $validasi)
            <div class="flex items-start gap-3">
                <div class="mt-0.5">
                    @if($validasi->passed)
                    <x-heroicon-c-check-circle class="w-5 h-5 text-success-500" />
                    @else
                    <x-heroicon-c-x-circle class="w-5 h-5 text-danger-500" />
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium {{ $validasi->passed ? 'text-gray-900 dark:text-white' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ str_replace('_', ' ', $validasi->gateCode) }}
                    </p>
                    @if(!$validasi->passed || $validasi->message !== 'OK')
                    <p class="text-xs text-gray-500 mt-0.5">{{ $validasi->message }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="rounded-lg border p-3 {{ $statusRisiko->getColor() === 'danger' ? 'border-danger-300 bg-danger-50' : ($statusRisiko->getColor() === 'warning' ? 'border-warning-300 bg-warning-50' : 'border-gray-200') }}">
            <p class="text-xs text-gray-500">Status Risiko Akademik</p>
            <p class="font-semibold">{{ $statusRisiko->getLabel() }}</p>
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
                    @forelse($krs->krsDetails as $detail)
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
                            @if($detail->jadwalKuliah && $detail->jadwalKuliah->dosenPengampus->isNotEmpty())
                            {{ $detail->jadwalKuliah->dosenPengampus->map(fn($p) => $p->dosen->person->nama_lengkap)->join(', ') }}
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
            <p class="text-2xl font-bold text-primary-600">{{ $krs->krsDetails->count() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-white/10 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total SKS</p>
            <p class="text-2xl font-bold text-primary-600">{{ $krs->krsDetails->sum('sks_snapshot') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-white/10 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider">MK Wajib</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $krs->krsDetails->filter(fn($d) => $d->mataKuliah?->sifat_mk === 'W')->count() }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-white/10 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider">MK Pilihan</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $krs->krsDetails->filter(fn($d) => $d->mataKuliah?->sifat_mk === 'P')->count() }}
            </p>
        </div>
    </div>
</div>