<?php

declare(strict_types=1);

namespace App\Services\LaporanPerkuliahan;

use App\Models\JadwalKuliah;
use App\Models\KurikulumMataKuliah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class JadwalKuliahReportService
{
    /**
     * @param array{
     *     tahun_akademik_id?: int,
     *     fakultas_id?: int,
     *     prodi_id?: int,
     *     dosen_id?: string|int,
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
            ->orderByRaw(
                "FIELD(
                    hari,
                    'Senin',
                    'Selasa',
                    'Rabu',
                    'Kamis',
                    'Jumat',
                    'Sabtu',
                    'Minggu'
                )"
            )
            ->orderBy('jam_mulai');
    }

    /**
     * Data khusus untuk kebutuhan export.
     *
     * Semester diambil dari:
     * kurikulum_mata_kuliah.semester_paket
     *
     * berdasarkan pasangan:
     * jadwal_kuliah.kurikulum_id
     * + jadwal_kuliah.mata_kuliah_id
     */
    public function exportRows(array $filters = []): Collection
    {
        $jadwals = $this->query($filters)->get();

        /*
         * Ambil semua pasangan kurikulum + mata kuliah
         * yang diperlukan oleh jadwal.
         *
         * Ini menghindari query satu per satu (N+1).
         */
        $pairs = $jadwals
            ->filter(
                fn(JadwalKuliah $jadwal) =>
                filled($jadwal->kurikulum_id)
                    && filled($jadwal->mata_kuliah_id)
            )
            ->map(
                fn(JadwalKuliah $jadwal) => [
                    'kurikulum_id' => $jadwal->kurikulum_id,
                    'mata_kuliah_id' => $jadwal->mata_kuliah_id,
                ]
            )
            ->unique(
                fn(array $pair) =>
                $pair['kurikulum_id'] . ':' . $pair['mata_kuliah_id']
            )
            ->values();

        /*
         * Ambil semester_paket secara batch.
         */
        $semesterMap = collect();

        if ($pairs->isNotEmpty()) {
            $semesterMap = KurikulumMataKuliah::query()
                ->where(function (Builder $query) use ($pairs) {
                    foreach ($pairs as $pair) {
                        $query->orWhere(function (Builder $q) use ($pair) {
                            $q->where(
                                'kurikulum_id',
                                $pair['kurikulum_id']
                            )->where(
                                'mata_kuliah_id',
                                $pair['mata_kuliah_id']
                            );
                        });
                    }
                })
                ->get([
                    'kurikulum_id',
                    'mata_kuliah_id',
                    'semester_paket',
                ])
                ->mapWithKeys(
                    fn(KurikulumMataKuliah $item) => [
                        $item->kurikulum_id . ':' . $item->mata_kuliah_id
                        => $item->semester_paket,
                    ]
                );
        }

        return $jadwals->map(
            function (JadwalKuliah $jadwal) use ($semesterMap): array {
                $semesterKey = filled($jadwal->kurikulum_id)
                    && filled($jadwal->mata_kuliah_id)
                    ? $jadwal->kurikulum_id . ':' . $jadwal->mata_kuliah_id
                    : null;

                return [
                    'hari' => $jadwal->hari,

                    'jam_mulai' => $jadwal->jam_mulai
                        ? substr((string) $jadwal->jam_mulai, 0, 5)
                        : '-',

                    'jam_selesai' => $jadwal->jam_selesai
                        ? substr((string) $jadwal->jam_selesai, 0, 5)
                        : '-',

                    'kode_mk' => $jadwal->mataKuliah?->kode_mk ?? '-',

                    'nama_mk' => $jadwal->mataKuliah?->nama_mk ?? '-',

                    'dosen' => $jadwal->dosenPengajars
                        ->map(
                            fn($d) =>
                            $d->dosen?->person?->nama_lengkap
                        )
                        ->filter()
                        ->implode(', '),

                    /*
                     * Gunakan kode internal prodi,
                     * bukan nama prodi yang panjang.
                     */
                    'prodi_kode' =>
                    $jadwal->kelas?->prodi?->kode_prodi_internal ?? '-',

                    /*
                     * Semester mata kuliah dari kurikulum.
                     */
                    'semester' => $semesterKey !== null
                        ? ($semesterMap->get($semesterKey) ?? '-')
                        : '-',

                    'ruang' => $jadwal->ruang?->nama_ruang ?? '-',

                    'kelas' => $jadwal->kelas?->nama_kelas ?? '-',
                ];
            }
        );
    }
}
