<?php

declare(strict_types=1);

namespace App\Services\Absensi;

use App\DataTransferObjects\Absensi\AbsensiDocumentData;
use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\PerkuliahanSesi;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AbsensiDocumentService
{
    public const MODE_MANUAL = 'manual';

    public const MODE_ONLINE = 'online';

    public const MODE_TEMPLATE = 'template';

    public function scheduleOptions(
        int $tahunAkademikId,
        ?int $prodiId = null,
        ?int $kampusId = null,
        ?int $kelasId = null,
        ?User $user = null,
    ): Collection {
        $query = JadwalKuliah::query()
            ->with(['mataKuliah', 'kelas', 'dosenPengampu.person'])
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->when($prodiId, fn (Builder $query) => $query->whereHas('kelas', fn (Builder $q) => $q->where('prodi_id', $prodiId)))
            ->when($kampusId, fn (Builder $query) => $query->whereHas('kelas', fn (Builder $q) => $q->where('kampus_id', $kampusId)))
            ->when($kelasId, fn (Builder $query) => $query->where('kelas_id', $kelasId));

        if ($user) {
            $query->visibleTo($user);
        }

        return $query
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Opsi kelas untuk dropdown, dikelompokkan per prodi agar mudah dipilih.
     * Label mengikuti konvensi project: "Kelas A — Angkatan 2024".
     *
     * @return array<string, array<int|string, string>>
     */
    public function kelasOptions(?int $prodiId = null, ?int $kampusId = null, ?User $user = null): array
    {
        $query = Kelas::query()->with('prodi')->orderBy('angkatan_id')->orderBy('nama_kelas');

        if ($prodiId) {
            $query->where('prodi_id', $prodiId);
        }

        if ($kampusId) {
            $query->where('kampus_id', $kampusId);
        }

        if ($user) {
            $query->visibleTo($user);
        }

        return $query
            ->get()
            ->groupBy(fn (Kelas $kelas) => $kelas->prodi?->nama_prodi ?? 'Tanpa Prodi')
            ->map(fn (Collection $kelas) => $kelas
                ->mapWithKeys(fn (Kelas $item) => [
                    $item->id => "{$item->nama_kelas} — Angkatan {$item->angkatan_id}",
                ])->all()
            )->all();
    }

    public function sessionOptions(string $jadwalKuliahId): Collection
    {
        return PerkuliahanSesi::query()
            ->whereHas('jadwalKuliah', fn (Builder $query) => $query->whereKey($jadwalKuliahId)->visibleTo(auth()->user()))
            ->orderBy('pertemuan_ke')
            ->get(['id', 'pertemuan_ke', 'waktu_mulai_rencana', 'status_sesi']);
    }

    public function resolve(
        string $mode,
        int $tahunAkademikId,
        string $jadwalKuliahId,
        ?string $sesiId = null,
        ?string $tanggal = null,
        ?User $user = null,
    ): AbsensiDocumentData {
        if (! in_array($mode, [self::MODE_MANUAL, self::MODE_ONLINE, self::MODE_TEMPLATE], true)) {
            throw ValidationException::withMessages(['mode' => 'Mode dokumen tidak valid.']);
        }

        $jadwalQuery = JadwalKuliah::query()
            ->with([
                'tahunAkademik', 'kelas.prodi', 'kelas.kampus', 'mataKuliah', 'ruang.kampus',
                'dosenPengampu.person',
            ])
            ->whereKey($jadwalKuliahId)
            ->where('tahun_akademik_id', $tahunAkademikId);

        if ($user) {
            $jadwalQuery->visibleTo($user);
        }

        $jadwal = $jadwalQuery->first();

        if (! $jadwal) {
            throw ValidationException::withMessages(['jadwal_kuliah_id' => 'Jadwal tidak ditemukan pada tahun akademik terpilih.']);
        }

        $sesi = null;
        if ($mode === self::MODE_ONLINE) {
            $sessionQuery = PerkuliahanSesi::query()
                ->whereKey($sesiId)
                ->where('jadwal_kuliah_id', $jadwal->id);

            if ($user) {
                $sessionQuery->whereHas('jadwalKuliah', fn (Builder $query) => $query->visibleTo($user));
            }

            $sesi = $sessionQuery->first();

            if (! $sesi) {
                throw ValidationException::withMessages(['perkuliahan_sesi_id' => 'Pertemuan tidak ditemukan pada jadwal terpilih.']);
            }
        }

        $rows = match ($mode) {
            self::MODE_ONLINE => $this->onlineRows($jadwal, $sesi),
            default => $this->rosterRows($jadwal),
        };

        $meeting = $sesi?->pertemuan_ke;
        $meetingDate = $sesi?->waktu_mulai_realisasi ?? $sesi?->waktu_mulai_rencana;
        if ($tanggal) {
            $meetingDate = Carbon::parse($tanggal);
        }

        $presentCount = collect($rows)->where('status', 'Hadir')->count();
        $summary = [
            'jumlah_mahasiswa' => count($rows),
            'hadir' => $presentCount,
            'izin' => collect($rows)->where('status', 'Izin')->count(),
            'sakit' => collect($rows)->where('status', 'Sakit')->count(),
            'alpa' => collect($rows)->where('status', 'Alpa')->count(),
            'belum_presensi' => collect($rows)->where('belum_presensi', true)->count(),
        ];

        $scheduleDosen = $jadwal->dosenPengampu;
        $dosenLabel = $scheduleDosen->map(fn ($dosen) => $dosen->person?->nama_lengkap ?? $dosen->nidn ?? '')->filter()->implode(', ');
        $academic = [
            'tahun_akademik' => $jadwal->tahunAkademik?->nama_tahun ?? '',
            'semester' => match ((int) $jadwal->tahunAkademik?->semester) {
                1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek', default => ''
            },
            'prodi' => $jadwal->kelas?->prodi?->nama_prodi ?? '',
            'kampus' => $jadwal->kelas?->kampus?->nama_kampus ?? $jadwal->ruang?->kampus?->nama_kampus ?? '',
            'mata_kuliah' => $jadwal->mataKuliah?->nama_mk ?? '',
            'kode_mk' => $jadwal->mataKuliah?->kode_mk ?? '',
            'sks' => $jadwal->mataKuliah?->sks_default ?? '',
            'kelas' => $jadwal->kelas?->nama_kelas ?? '',
            'dosen' => $dosenLabel,
            'ruang' => $jadwal->ruang?->nama_ruang ?? '',
            'hari' => $jadwal->hari ?? '',
            'jam' => trim(substr($jadwal->jam_mulai ?? '', 0, 5).' - '.substr($jadwal->jam_selesai ?? '', 0, 5), ' -'),
            'pertemuan' => $meeting,
            'tanggal' => $meetingDate?->format('d/m/Y') ?? '',
            'tanggal_iso' => $meetingDate?->format('Y-m-d') ?? '',
        ];

        $meetingCount = (int) $jadwal->dosenPengampu->max(fn ($dosen) => (int) $dosen->pivot?->rencana_tatap_muka);
        $meetingCount = min(max($meetingCount, 1), 16);
        $meetings = range(1, $meetingCount);

        return new AbsensiDocumentData($mode, $academic, $rows, $summary, $meetings);
    }

    /**
     * Roster resmi absensi berasal dari KRS yang sudah disetujui pada tahun
     * akademik jadwal tersebut, bukan sekadar anggota kelas aktif.
     * Ini menjaga manual/template konsisten dengan presensi online.
     *
     * @return list<array<string, mixed>>
     */
    private function rosterRows(JadwalKuliah $jadwal): array
    {
        return $jadwal->krsDetails()
            ->with(['krs.mahasiswa.person'])
            ->whereHas('krs', fn (Builder $query) => $query
                ->where('tahun_akademik_id', $jadwal->tahun_akademik_id)
                ->berlaku())
            ->where('status_ambil', '!=', 'K')
            ->get()
            ->sortBy(fn ($detail) => $detail->krs?->mahasiswa?->nim ?? '')
            ->values()
            ->map(fn ($detail, $index) => [
                'no' => $index + 1,
                'nim' => $detail->krs?->mahasiswa?->nim ?? '',
                'nama' => $detail->krs?->mahasiswa?->person?->nama_lengkap ?? '',
                'status' => '',
                'waktu' => '',
                'keterangan' => '',
                'belum_presensi' => false,
            ])->all();
    }

    /** @return list<array<string, mixed>> */
    private function onlineRows(JadwalKuliah $jadwal, PerkuliahanSesi $sesi): array
    {
        $students = $jadwal->krsDetails()
            ->with(['krs.mahasiswa.person'])
            ->whereHas('krs', fn (Builder $query) => $query
                ->where('tahun_akademik_id', $jadwal->tahun_akademik_id)
                ->berlaku())
            ->where('status_ambil', '!=', 'K')
            ->orderBy('id')
            ->get();
        $attendanceByDetail = $sesi->absensi()->with('krsDetail')->get()->keyBy('krs_detail_id');

        return $students->values()->map(function ($detail, $index) use ($attendanceByDetail) {
            $attendance = $attendanceByDetail->get($detail->id);
            $status = $attendance?->status_kehadiran;
            $checkIn = $attendance?->waktu_check_in;

            return [
                'no' => $index + 1,
                'nim' => $detail->krs?->mahasiswa?->nim ?? '',
                'nama' => $detail->krs?->mahasiswa?->person?->nama_lengkap ?? '',
                'status' => $status?->getLabel() ?? 'Belum Tercatat',
                'waktu' => $checkIn?->format('H:i') ?? '',
                'keterangan' => $attendance?->alasan_perubahan ?? ($attendance && ! $checkIn ? 'Belum Absen' : ''),
                'belum_presensi' => ! $attendance || ! $checkIn,
            ];
        })->all();
    }
}
