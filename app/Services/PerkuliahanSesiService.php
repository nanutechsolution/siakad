<?php

namespace App\Services;

use App\Enums\StatusKehadiran;
use App\Enums\StatusSesiPerkuliahan;
use App\Models\JadwalKuliah;
use App\Models\PerkuliahanAbsensi;
use App\Models\PerkuliahanSesi;
use App\Models\TrxDosen;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerkuliahanSesiService
{
    public function bukaSesi(JadwalKuliah $jadwal, TrxDosen $dosen): PerkuliahanSesi
    {
        return DB::transaction(function () use ($jadwal) {
            $sesiAktif = $jadwal->sesiPerkuliahan()
                ->where('status_sesi', StatusSesiPerkuliahan::Dibuka->value)
                ->lockForUpdate()
                ->first();

            if ($sesiAktif) {
                return $this->regenerateToken($sesiAktif);
            }

            $sesiHariIni = $jadwal->sesiPerkuliahan()
                ->whereDate('waktu_mulai_rencana', now()->toDateString())
                ->where('status_sesi', StatusSesiPerkuliahan::Terjadwal->value)
                ->orderBy('pertemuan_ke')
                ->first();

            if (! $sesiHariIni) {
                $pertemuanTerakhir = (int) $jadwal->sesiPerkuliahan()->max('pertemuan_ke');

                $sesiHariIni = PerkuliahanSesi::create([
                    'jadwal_kuliah_id' => $jadwal->id,
                    'pertemuan_ke' => $pertemuanTerakhir + 1,
                    'waktu_mulai_rencana' => now(),
                    'status_sesi' => StatusSesiPerkuliahan::Terjadwal->value,
                ]);
            }

            $sesiHariIni->update([
                'status_sesi' => StatusSesiPerkuliahan::Dibuka->value,
                'waktu_mulai_realisasi' => $sesiHariIni->waktu_mulai_realisasi ?? now(),
                'token_sesi' => $this->generateToken(),
                'metode_validasi' => 'QR',
            ]);

            $this->seedAbsensiAwal($sesiHariIni, $jadwal);

            return $sesiHariIni->fresh();
        });
    }

    public function tutupSesi(PerkuliahanSesi $sesi): PerkuliahanSesi
    {
        $sesi->update([
            'status_sesi' => StatusSesiPerkuliahan::Selesai->value,
            'waktu_selesai_realisasi' => now(),
            'token_sesi' => null,
        ]);

        return $sesi->fresh();
    }

    public function regenerateToken(PerkuliahanSesi $sesi): PerkuliahanSesi
    {
        if ($sesi->status_sesi !== StatusSesiPerkuliahan::Dibuka) {
            throw ValidationException::withMessages([
                'sesi' => 'Sesi tidak dalam status dibuka.',
            ]);
        }

        $sesi->update(['token_sesi' => $this->generateToken()]);

        return $sesi->fresh();
    }

    public function updateAbsensiManual(
        PerkuliahanAbsensi $absensi,
        string $statusBaru,
        string $alasan,
        string $modifiedByUserId
    ): PerkuliahanAbsensi {
        $absensi->update([
            'status_kehadiran' => $statusBaru,
            'is_manual_update' => true,
            'modified_by_user_id' => $modifiedByUserId,
            'alasan_perubahan' => $alasan,
        ]);

        return $absensi->fresh();
    }

    protected function seedAbsensiAwal(PerkuliahanSesi $sesi, JadwalKuliah $jadwal): void
    {
        $krsDetailIds = $jadwal->krsDetail()->pluck('id');

        foreach ($krsDetailIds as $krsDetailId) {
            PerkuliahanAbsensi::firstOrCreate(
                [
                    'perkuliahan_sesi_id' => $sesi->id,
                    'krs_detail_id' => $krsDetailId,
                ],
                ['status_kehadiran' => StatusKehadiran::Alpa->value]
            );
        }
    }

    protected function generateToken(): string
    {
        return (string) random_int(100000, 999999);
    }
}
