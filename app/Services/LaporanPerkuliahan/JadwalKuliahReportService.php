<?php

declare(strict_types=1);

namespace App\Services\LaporanPerkuliahan;

use App\Models\JadwalKuliah;
use Illuminate\Database\Eloquent\Builder;

class JadwalKuliahReportService
{
    /**
     * @param array{
     *     tahun_akademik_id?: int,
     *     fakultas_id?: int,
     *     prodi_id?: int,
     *     dosen_id?: string,
     *     mata_kuliah_id?: int,
     *     ruang_id?: int
     * } $filters
     */
    public function query(array $filters = []): Builder
    {
        return JadwalKuliah::query()
            ->with([
                'tahunAkademik',
                'mataKuliah',
                'ruang',
                'kelas.prodi.fakultas',
                'dosenPengajars.dosen.person',
            ])
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->where('tahun_akademik_id', $value)
            )
            ->when(
                $filters['fakultas_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->whereHas(
                    'kelas.prodi',
                    fn(Builder $q) =>
                    $q->where('fakultas_id', $value)
                )
            )
            ->when(
                $filters['prodi_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->whereHas(
                    'kelas',
                    fn(Builder $q) =>
                    $q->where('prodi_id', $value)
                )
            )
            ->when(
                $filters['dosen_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->whereHas(
                    'dosenPengajars',
                    fn(Builder $q) =>
                    $q->where('dosen_id', $value)
                )
            )
            ->when(
                $filters['mata_kuliah_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->where('mata_kuliah_id', $value)
            )
            ->when(
                $filters['ruang_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->where('ruang_id', $value)
            )
            ->orderByRaw("
                FIELD(
                    hari,
                    'Senin',
                    'Selasa',
                    'Rabu',
                    'Kamis',
                    'Jumat',
                    'Sabtu',
                    'Minggu'
                )
            ")
            ->orderBy('jam_mulai');
    }

    /**
     * Hitung semester kelas berdasarkan:
     *
     * angkatan 2026
     * 20261 = semester 1
     * 20262 = semester 2
     * 20271 = semester 3
     * 20272 = semester 4
     *
     * @param int $angkatan
     * @param string|null $kodeTahun
     */
    private function hitungSemester(
        int $angkatan,
        ?string $kodeTahun
    ): ?int {
        if (!$angkatan || !$kodeTahun) {
            return null;
        }

        /*
         * Format kode tahun akademik:
         *
         * 20261 = 2026/2027 Ganjil
         * 20262 = 2026/2027 Genap
         * 20271 = 2027/2028 Ganjil
         * 20272 = 2027/2028 Genap
         */

        if (!preg_match('/^(\d{4})([123])$/', $kodeTahun, $matches)) {
            return null;
        }

        $tahunMulai = (int) $matches[1];
        $periode = (int) $matches[2];

        // Semester 3 = semester Pendek tidak dihitung sebagai
        // semester reguler baru.
        if ($periode === 3) {
            return null;
        }

        $selisihTahun = $tahunMulai - $angkatan;

        if ($selisihTahun < 0) {
            return null;
        }

        return ($selisihTahun * 2) + $periode;
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)
            ->get()
            ->map(function (JadwalKuliah $jadwal) {

                $angkatan = $jadwal->kelas?->angkatan_id;

                $kodeTahun = $jadwal->tahunAkademik?->kode_tahun;

                $semester = $this->hitungSemester(
                    (int) $angkatan,
                    $kodeTahun
                );

                return [
                    'hari' => $jadwal->hari,

                    'jam_mulai' => $jadwal->jam_mulai
                        ? substr($jadwal->jam_mulai, 0, 5)
                        : '-',

                    'jam_selesai' => $jadwal->jam_selesai
                        ? substr($jadwal->jam_selesai, 0, 5)
                        : '-',

                    'kode_mk' => $jadwal->mataKuliah?->kode_mk ?? '-',

                    'nama_mk' => $jadwal->mataKuliah?->nama_mk ?? '-',

                    'dosen' => $jadwal->dosenPengajars
                        ->map(
                            fn($dosenPengajar) =>
                            $dosenPengajar->dosen?->person?->nama_lengkap
                        )
                        ->filter()
                        ->implode(', '),

                    /*
                     * Gunakan kode internal prodi:
                     *
                     * TI
                     * ARS
                     * SI
                     * dst.
                     */
                    'prodi_kode' =>
                    $jadwal->kelas?->prodi?->kode_prodi_internal ?? '-',

                    /*
                     * Semester kelas berdasarkan angkatan +
                     * tahun akademik jadwal.
                     */
                    'semester' => $semester ?? '-',

                    'ruang' =>
                    $jadwal->ruang?->nama_ruang ?? '-',

                    'kelas' =>
                    $jadwal->kelas?->nama_kelas ?? '-',

                    'angkatan' =>
                    $angkatan ?? '-',

                    /*
                     * Siap dipakai langsung untuk tampilan:
                     * TI/1/C
                     */
                    'prodi_semester_kelas' => sprintf(
                        '%s/%s/%s',
                        $jadwal->kelas?->prodi?->kode_prodi_internal ?? '-',
                        $semester ?? '-',
                        $jadwal->kelas?->nama_kelas ?? '-',
                    ),
                ];
            });
    }
}
