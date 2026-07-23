<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\Enums\StatusKuliah;
use App\Models\JadwalKuliah;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\RiwayatStatusMahasiswa;
use Illuminate\Support\Facades\DB;

class RiwayatStatusService
{
    /**
     * Sinkronkan satu semester mahasiswa.
     */
    public function sinkronkanSemester(
        string $mahasiswaId,
        int $tahunAkademikId,
    ): void {

        $detailsSemester = KrsDetail::query()
            ->whereHas('krs', function ($q) use ($mahasiswaId, $tahunAkademikId) {
                $q->where('mahasiswa_id', $mahasiswaId)
                    ->where('tahun_akademik_id', $tahunAkademikId);
            })
            ->where('is_published', true)
            ->get();

        if ($detailsSemester->isEmpty()) {
            return;
        }

        $totalBobotSemester = 0;
        $totalSksSemester = 0;

        foreach ($detailsSemester as $detail) {
            $totalBobotSemester +=
                (float) $detail->nilai_indeks *
                (int) $detail->sks_snapshot;

            $totalSksSemester += (int) $detail->sks_snapshot;
        }

        $ips = $totalSksSemester > 0
            ? round($totalBobotSemester / $totalSksSemester, 2)
            : 0;

        /*
         |--------------------------------------------------------------------------
         | Hitung IPK dari seluruh semester
         |--------------------------------------------------------------------------
         */

        $semuaDetail = KrsDetail::query()
            ->whereHas('krs', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            })
            ->where('is_published', true)
            ->get();

        $totalBobot = 0;
        $totalSks = 0;

        foreach ($semuaDetail as $detail) {
            $totalBobot +=
                (float) $detail->nilai_indeks *
                (int) $detail->sks_snapshot;

            $totalSks += (int) $detail->sks_snapshot;
        }

        $ipk = $totalSks > 0
            ? round($totalBobot / $totalSks, 2)
            : 0;

        RiwayatStatusMahasiswa::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'tahun_akademik_id' => $tahunAkademikId,
            ],
            [
                'status_kuliah' => StatusKuliah::AKTIF->value,
                'ips' => $ips,
                'ipk' => $ipk,
                'sks_semester' => $totalSksSemester,
                'sks_total' => $totalSks,
            ]
        );
    }

    /**
     * Hitung ulang seluruh semester mahasiswa.
     */
    public function sinkronkanMahasiswa(string $mahasiswaId): void
    {
        DB::transaction(function () use ($mahasiswaId) {

            $semester = Krs::query()
                ->where('mahasiswa_id', $mahasiswaId)
                ->distinct()
                ->pluck('tahun_akademik_id');

            foreach ($semester as $tahunAkademikId) {
                $this->sinkronkanSemester(
                    $mahasiswaId,
                    (int) $tahunAkademikId
                );
            }
        });
    }

    /**
     * Hitung ulang seluruh mahasiswa pada satu kelas.
     */
    public function sinkronkanKelas(JadwalKuliah $jadwal): void
    {
        $mahasiswaIds = $jadwal->krsDetails()
            ->with('krs')
            ->get()
            ->pluck('krs.mahasiswa_id')
            ->filter()
            ->unique();

        foreach ($mahasiswaIds as $mahasiswaId) {
            $this->sinkronkanMahasiswa($mahasiswaId);
        }
    }
}
