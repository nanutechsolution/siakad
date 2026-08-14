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
     * Sinkronkan status akademik mahasiswa untuk satu semester.
     *
     * Riwayat hanya dibuat jika terdapat nilai yang sudah dipublish.
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
            ->get([
                'id',
                'nilai_indeks',
                'sks_snapshot',
            ]);

        /*
         * Belum ada nilai published.
         *
         * Jangan membuat riwayat akademik dengan IPS/IPK = 0.
         */
        if ($detailsSemester->isEmpty()) {
            return;
        }

        /*
         * ================================================================
         * IPS SEMESTER
         * ================================================================
         */

        $totalBobotSemester = 0.0;
        $totalSksSemester = 0;

        foreach ($detailsSemester as $detail) {
            $sks = (int) $detail->sks_snapshot;
            $indeks = (float) $detail->nilai_indeks;

            $totalBobotSemester += $indeks * $sks;
            $totalSksSemester += $sks;
        }

        $ips = $totalSksSemester > 0
            ? round($totalBobotSemester / $totalSksSemester, 2)
            : 0.0;

        /*
         * ================================================================
         * IPK KUMULATIF
         * ================================================================
         */

        $semuaDetail = KrsDetail::query()
            ->whereHas('krs', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            })
            ->where('is_published', true)
            ->get([
                'id',
                'nilai_indeks',
                'sks_snapshot',
            ]);

        $totalBobot = 0.0;
        $totalSks = 0;

        foreach ($semuaDetail as $detail) {
            $sks = (int) $detail->sks_snapshot;
            $indeks = (float) $detail->nilai_indeks;

            $totalBobot += $indeks * $sks;
            $totalSks += $sks;
        }

        $ipk = $totalSks > 0
            ? round($totalBobot / $totalSks, 2)
            : 0.0;

        /*
         * ================================================================
         * SIMPAN RIWAYAT
         * ================================================================
         */

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
     * Hitung ulang seluruh semester mahasiswa
     * berdasarkan KRS yang benar-benar memiliki nilai published.
     */
    public function sinkronkanMahasiswa(string $mahasiswaId): void
    {
        DB::transaction(function () use ($mahasiswaId) {

            $semester = KrsDetail::query()
                ->whereHas('krs', function ($q) use ($mahasiswaId) {
                    $q->where('mahasiswa_id', $mahasiswaId);
                })
                ->where('is_published', true)
                ->join('krs', 'krs.id', '=', 'krs_detail.krs_id')
                ->distinct()
                ->pluck('krs.tahun_akademik_id');

            foreach ($semester as $tahunAkademikId) {
                $this->sinkronkanSemester(
                    $mahasiswaId,
                    (int) $tahunAkademikId
                );
            }
        });
    }

    /**
     * Hitung ulang seluruh mahasiswa pada satu kelas/jadwal.
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
