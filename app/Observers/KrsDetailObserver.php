<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\KrsDetail;
use Illuminate\Support\Facades\DB;

class KrsDetailObserver
{
    public function created(KrsDetail $krsDetail): void
    {
        DB::transaction(function () use ($krsDetail) {
            if ($krsDetail->jadwal_kuliah_id) {
                DB::table('jadwal_kuliah')
                    ->where('id', $krsDetail->jadwal_kuliah_id)
                    ->lockForUpdate()
                    ->increment('isi_kelas');
            }

            $this->recalculateKrsSks($krsDetail->krs_id);
        });
    }

    public function updating(KrsDetail $krsDetail): void
    {
        // Tangani pertukaran kelas di event updating untuk mencegah race conditions
        if ($krsDetail->isDirty('jadwal_kuliah_id')) {
            $oldJadwalId = $krsDetail->getOriginal('jadwal_kuliah_id');
            $newJadwalId = $krsDetail->jadwal_kuliah_id;

            DB::transaction(function () use ($oldJadwalId, $newJadwalId) {
                if ($oldJadwalId) {
                    DB::table('jadwal_kuliah')
                        ->where('id', $oldJadwalId)
                        ->lockForUpdate()
                        ->decrement('isi_kelas');
                }

                if ($newJadwalId) {
                    DB::table('jadwal_kuliah')
                        ->where('id', $newJadwalId)
                        ->lockForUpdate()
                        ->increment('isi_kelas');
                }
            });
        }
    }

    public function updated(KrsDetail $krsDetail): void
    {
        if ($krsDetail->isDirty('sks_snapshot') || $krsDetail->isDirty('status_ambil')) {
            DB::transaction(function () use ($krsDetail) {
                $this->recalculateKrsSks($krsDetail->krs_id);
            });
        }
    }

    public function deleted(KrsDetail $krsDetail): void
    {
        DB::transaction(function () use ($krsDetail) {
            if ($krsDetail->jadwal_kuliah_id) {
                DB::table('jadwal_kuliah')
                    ->where('id', $krsDetail->jadwal_kuliah_id)
                    ->lockForUpdate()
                    ->decrement('isi_kelas');
            }

            $this->recalculateKrsSks($krsDetail->krs_id);
        });
    }

    private function recalculateKrsSks(string $krsId): void
    {
        $totalSks = (int) DB::table('krs_detail')
            ->where('krs_id', $krsId)
            ->sum('sks_snapshot');

        DB::table('krs')->where('id', $krsId)->update(['total_sks_diambil' => $totalSks]);
    }
}
