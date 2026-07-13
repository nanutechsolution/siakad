<?php

namespace App\Services;

use App\Models\JadwalKuliah;
use Illuminate\Support\Facades\DB;

class NilaiBaraService
{
    public function lockKelas(JadwalKuliah $jadwalKuliah, ?string $catatan = null): void
    {
        DB::transaction(function () use ($jadwalKuliah, $catatan) {
            // Update seluruh krs detail di kelas ini menjadi locked
            $jadwalKuliah->krsDetails()->update(['is_locked' => true]);

            // Catat log activity Spatie
            activity()
                ->performedOn($jadwalKuliah)
                ->causedBy(auth()->user())
                ->withProperties(['catatan' => $catatan])
                ->log('BARA mengunci (lock) nilai kelas');
        });
    }

    public function unlockKelas(JadwalKuliah $jadwalKuliah, string $alasan): void
    {
        DB::transaction(function () use ($jadwalKuliah, $alasan) {
            // Buka kunci seluruh krs detail di kelas ini
            $jadwalKuliah->krsDetails()->update(['is_locked' => false]);

            // Catat log activity Spatie (wajib ada alasan)
            activity()
                ->performedOn($jadwalKuliah)
                ->causedBy(auth()->user())
                ->withProperties(['alasan' => $alasan])
                ->log('BARA membuka (unlock) nilai kelas');
        });
    }
}