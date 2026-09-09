<?php

namespace App\Services\Scheduling;

use App\Models\DosenKetersediaan;
use App\Models\DosenPengampu;
use App\Models\JadwalGeneratorBatch;
use App\Models\JadwalGeneratorResult;
use App\Models\JadwalKuliah;
use App\Services\Scheduling\Support\ScheduleTracker;

class ConstraintContextLoader
{
    /**
     * @return array{
     *     tracker: ScheduleTracker,
     *     limitasiWaktuDosen: array
     * }
     */
    public function load(JadwalGeneratorBatch $batch): array
    {
        $tracker = new ScheduleTracker();
        $targetKampusId = $batch->kampus_id;

        $this->loadProduction($tracker, $batch, $targetKampusId);
        $this->loadPreviewAktifDariBatchLain($tracker, $batch, $targetKampusId);

        $limitasiWaktuDosen = $this->loadAvailability();

        return [
            'tracker' => $tracker,
            'limitasiWaktuDosen' => $limitasiWaktuDosen,
        ];
    }

    /**
     * Sumber 1: jadwal yang sudah dipublish ke production.
     * Diambil GLOBAL untuk tahun akademik ini (semua kampus, semua prodi) --
     * karena bentrok dosen/kelas/ruang harus dicek lintas prodi, dan karantina
     * lintas kampus butuh tahu jadwal dosen di kampus lain.
     */
    protected function loadProduction(ScheduleTracker $tracker, JadwalGeneratorBatch $batch, ?int $targetKampusId): void
    {
        $existingJadwal = JadwalKuliah::with(['dosenPengampus', 'ruang', 'kelas'])
            ->where('tahun_akademik_id', $batch->tahun_akademik_id)
            ->get();

        foreach ($existingJadwal as $jadwal) {
            if (!$jadwal->hari || !$jadwal->jam_mulai || !$jadwal->jam_selesai || !$jadwal->ruang_id) {
                // Data jadwal belum lengkap (mis. hasil intervensi manual yang belum
                // disimpan penuh) -- lewati daripada mengotori tracker dengan key kosong.
                continue;
            }

            $hari = $jadwal->hari;
            $mulai = substr($jadwal->jam_mulai, 0, 5);
            $selesai = substr($jadwal->jam_selesai, 0, 5);
            $dosenIds = $jadwal->dosenPengampus->pluck('dosen_id')->all();
            $prodiId = $jadwal->kelas->prodi_id ?? null;

            $tracker->reserve($dosenIds, $jadwal->kelas_id, $jadwal->ruang_id, $hari, $mulai, $selesai, $prodiId);

            $jadwalKampusId = $jadwal->ruang->kampus_id ?? null;
            if ($targetKampusId && $jadwalKampusId && $jadwalKampusId != $targetKampusId) {
                foreach ($dosenIds as $dId) {
                    $tracker->markKarantina($dId, $hari);
                }
            }
        }
    }

    /**
     * Sumber 2 (BARU -- perbaikan bug B1): jadwal hasil batch LAIN yang masih
     * aktif (PREVIEW atau RUNNING, artinya belum di-publish dan belum
     * ditolak/reset) untuk tahun akademik yang sama. Batch yang sudah
     * COMMITTED tidak perlu diambil di sini karena datanya sudah pindah ke
     * JadwalKuliah (sumber 1). Batch FAILED tidak relevan.
     *
     * Inilah yang membuat "TI digenerate dulu, lalu Manajemen" saling melihat
     * satu sama lain walau TI belum dipublish (lihat poin J).
     */
    protected function loadPreviewAktifDariBatchLain(ScheduleTracker $tracker, JadwalGeneratorBatch $batch, ?int $targetKampusId): void
    {
        $batchLain = JadwalGeneratorBatch::where('tahun_akademik_id', $batch->tahun_akademik_id)
            ->where('id', '!=', $batch->id)
            ->whereIn('status', ['PREVIEW', 'RUNNING'])
            ->get(['id', 'kampus_id']);

        if ($batchLain->isEmpty()) {
            return;
        }

        $results = JadwalGeneratorResult::with(['kelas'])
            ->whereIn('batch_id', $batchLain->pluck('id'))
            ->where('is_success', true)
            ->get();

        $kampusPerBatch = $batchLain->pluck('kampus_id', 'id');

        foreach ($results as $result) {
            if (!$result->hari || !$result->jam_mulai || !$result->jam_selesai || !$result->ruang_id) {
                continue;
            }

            $hari = $result->hari;
            $mulai = substr($result->jam_mulai, 0, 5);
            $selesai = substr($result->jam_selesai, 0, 5);

            $dosenPengampuIds = is_string($result->dosen_pengampu_ids)
                ? json_decode($result->dosen_pengampu_ids, true)
                : $result->dosen_pengampu_ids;
            $dosenIds = DosenPengampu::whereIn('id', $dosenPengampuIds ?? [])->pluck('dosen_id')->all();

            $prodiId = $result->kelas->prodi_id ?? null;

            $tracker->reserve($dosenIds, $result->kelas_id, $result->ruang_id, $hari, $mulai, $selesai, $prodiId);

            $jadwalKampusId = $kampusPerBatch[$result->batch_id] ?? null;
            if ($targetKampusId && $jadwalKampusId && $jadwalKampusId != $targetKampusId) {
                foreach ($dosenIds as $dId) {
                    $tracker->markKarantina($dId, $hari);
                }
            }
        }
    }

    protected function loadAvailability(): array
    {
        $limitasiWaktuDosen = [];
        $availabilities = DosenKetersediaan::all()->groupBy('dosen_id');
        foreach ($availabilities as $dosenId => $avails) {
            foreach ($avails as $avail) {
                $limitasiWaktuDosen[$dosenId][$avail->hari][] = [
                    'mulai' => substr($avail->jam_mulai, 0, 5),
                    'selesai' => substr($avail->jam_selesai, 0, 5),
                ];
            }
        }
        return $limitasiWaktuDosen;
    }
}
