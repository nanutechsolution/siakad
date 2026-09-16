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
     *     limitasiWaktuDosen: array,
     *     ketersediaanDosenKhusus: array
     * }
     */
    public function load(JadwalGeneratorBatch $batch): array
    {
        $tracker = new ScheduleTracker();

        // Production selalu menjadi constraint GLOBAL.
        $this->loadProduction($tracker, $batch);

        // Preview/RUNNING batch lain juga menjadi constraint GLOBAL.
        $this->loadPreviewAktifDariBatchLain($tracker, $batch);

        $availability = $this->loadAvailability();

        return [
            'tracker' => $tracker,
            'limitasiWaktuDosen' => $availability['limitasiWaktuDosen'],
            'ketersediaanDosenKhusus' => $availability['ketersediaanDosenKhusus'],
        ];
    }

    /**
     * Sumber 1: jadwal yang sudah dipublish ke production.
     * Diambil GLOBAL untuk tahun akademik ini (semua kampus, semua prodi) --
     * karena bentrok dosen/kelas/ruang harus dicek lintas prodi, dan karantina
     * lintas kampus butuh tahu jadwal dosen di kampus lain.
     */
    protected function loadProduction(
        ScheduleTracker $tracker,
        JadwalGeneratorBatch $batch
    ): void {
        $existingJadwal = JadwalKuliah::with([
            'dosenPengampus',
            'ruang',
            'kelas',
        ])
            ->where('tahun_akademik_id', $batch->tahun_akademik_id)
            ->get();

        foreach ($existingJadwal as $jadwal) {
            if (
                !$jadwal->hari ||
                !$jadwal->jam_mulai ||
                !$jadwal->jam_selesai ||
                !$jadwal->ruang_id
            ) {
                // Jadwal belum lengkap, jangan dimasukkan ke tracker.
                continue;
            }

            $hari = $jadwal->hari;
            $mulai = substr($jadwal->jam_mulai, 0, 5);
            $selesai = substr($jadwal->jam_selesai, 0, 5);

            $dosenIds = $jadwal->dosenPengampus
                ->pluck('dosen_id')
                ->all();

            $prodiId = $jadwal->kelas->prodi_id ?? null;

            // KAMPUS AKTUAL = kampus lokasi ruang.
            $jadwalKampusId = $jadwal->ruang->kampus_id ?? null;

            $tracker->reserve(
                $dosenIds,
                $jadwal->kelas_id,
                $jadwal->ruang_id,
                $hari,
                $mulai,
                $selesai,
                $prodiId,
                $jadwalKampusId
            );
        }
    }

    /**
     * aktif (PREVIEW atau RUNNING, artinya belum di-publish dan belum
     * ditolak/reset) untuk tahun akademik yang sama. Batch yang sudah
     * COMMITTED tidak perlu diambil di sini karena datanya sudah pindah ke
     * JadwalKuliah (sumber 1). Batch FAILED tidak relevan.
     *
     * Inilah yang membuat "TI digenerate dulu, lalu Manajemen" saling melihat
     * satu sama lain walau TI belum dipublish (lihat poin J).
     */
    protected function loadPreviewAktifDariBatchLain(
        ScheduleTracker $tracker,
        JadwalGeneratorBatch $batch
    ): void {
        $batchLain = JadwalGeneratorBatch::query()
            ->where('tahun_akademik_id', $batch->tahun_akademik_id)
            ->where('id', '!=', $batch->id)
            ->whereIn('status', ['PREVIEW', 'RUNNING'])
            ->get(['id']);

        if ($batchLain->isEmpty()) {
            return;
        }

        $results = JadwalGeneratorResult::with(['kelas'])
            ->whereIn('batch_id', $batchLain->pluck('id'))
            ->where('is_success', true)
            ->get();

        if ($results->isEmpty()) {
            return;
        }

        /*
     * Kampus aktual tidak boleh diambil dari:
     *
     *   batch.kampus_id
     *
     * karena sebuah hasil bisa saja menggunakan ruang LAB
     * di kampus lain.
     *
     * Kampus aktual selalu:
     *
     *   jadwal_generator_results.ruang_id
     *              ↓
     *   ref_ruang.kampus_id
     */
        $ruangIds = $results
            ->pluck('ruang_id')
            ->filter()
            ->unique()
            ->values();

        $ruangKampus = \App\Models\RefRuang::query()
            ->whereIn('id', $ruangIds)
            ->pluck('kampus_id', 'id');

        foreach ($results as $result) {
            if (
                !$result->hari ||
                !$result->jam_mulai ||
                !$result->jam_selesai ||
                !$result->ruang_id
            ) {
                continue;
            }

            $hari = $result->hari;
            $mulai = substr($result->jam_mulai, 0, 5);
            $selesai = substr($result->jam_selesai, 0, 5);

            $dosenPengampuIds = is_string($result->dosen_pengampu_ids)
                ? json_decode($result->dosen_pengampu_ids, true)
                : $result->dosen_pengampu_ids;

            $dosenIds = DosenPengampu::query()
                ->whereIn('id', $dosenPengampuIds ?? [])
                ->pluck('dosen_id')
                ->all();

            $prodiId = $result->kelas->prodi_id ?? null;

            // Kampus aktual berdasarkan lokasi ruang.
            $jadwalKampusId = $ruangKampus[$result->ruang_id] ?? null;

            $tracker->reserve(
                $dosenIds,
                $result->kelas_id,
                $result->ruang_id,
                $hari,
                $mulai,
                $selesai,
                $prodiId,
                $jadwalKampusId
            );
        }
    }
    protected function loadAvailability(): array
    {
        $limitasiWaktuDosen = [];
        $ketersediaanDosenKhusus = [];

        $availabilities = DosenKetersediaan::query()
            ->get()
            ->groupBy('dosen_id');

        foreach ($availabilities as $dosenId => $avails) {
            foreach ($avails as $avail) {
                $hari = $avail->hari;

                $data = [
                    'mulai' => substr($avail->jam_mulai, 0, 5),
                    'selesai' => substr($avail->jam_selesai, 0, 5),
                ];

                /*
             * allow_outside_operational_hours = TRUE
             *
             * Ini adalah JAM TAMBAHAN.
             *
             * Jangan masukkan ke limitasiWaktuDosen,
             * karena jam normal tetap mengikuti Wizard.
             *
             * Contoh:
             *
             * Wizard       : 08:00-16:00
             * Special      : 18:00-20:00
             *
             * Maka dosen boleh:
             *   08:00-16:00
             *   18:00-20:00
             */
                if ((bool) $avail->allow_outside_operational_hours) {
                    $ketersediaanDosenKhusus[$dosenId][$hari][] = $data;

                    continue;
                }

                /*
             * allow_outside_operational_hours = FALSE
             *
             * Record ini tetap dianggap sebagai availability
             * pembatas normal.
             *
             * Jadi jika dosen mempunyai:
             *
             * Senin 10:00-14:00
             *
             * maka pada Senin dia hanya boleh ditempatkan
             * dalam window tersebut.
             */
                $limitasiWaktuDosen[$dosenId][$hari][] = $data;
            }
        }

        return [
            'limitasiWaktuDosen' => $limitasiWaktuDosen,
            'ketersediaanDosenKhusus' => $ketersediaanDosenKhusus,
        ];
    }
}
