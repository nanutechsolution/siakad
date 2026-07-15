<?php

namespace App\Services;

use App\Enums\StatusKehadiranEnum;
use App\Enums\StatusSesiPerkuliahan;
use App\Models\KrsDetail;
use App\Models\PerkuliahanAbsensi;
use App\Models\PerkuliahanSesi;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PresensiMahasiswaService
{
    public function checkin(User $user, string $token, ?string $ipAddress = null, ?string $deviceFingerprint = null): PerkuliahanAbsensi
    {
        $token = strtoupper(trim($token));

        $sesi = PerkuliahanSesi::where('token_sesi', $token)
            ->where('status_sesi', StatusSesiPerkuliahan::Dibuka->value)
            ->first();

        if (! $sesi) {
            throw ValidationException::withMessages([
                'token' => 'Token tidak valid atau sesi sudah ditutup.',
            ]);
        }

        $mahasiswa = $user->person?->mahasiswa;

        abort_unless($mahasiswa, 403, 'Akun Anda tidak terhubung ke data mahasiswa.');

        $krsDetail = KrsDetail::whereHas('krs', fn($q) => $q->where('mahasiswa_id', $mahasiswa->id))
            ->where('jadwal_kuliah_id', $sesi->jadwal_kuliah_id)
            ->first();

        if (! $krsDetail) {
            throw ValidationException::withMessages([
                'token' => 'Anda tidak terdaftar di kelas ini.',
            ]);
        }

        $absensi = PerkuliahanAbsensi::where('perkuliahan_sesi_id', $sesi->id)
            ->where('krs_detail_id', $krsDetail->id)
            ->first();

        if (! $absensi) {
            throw ValidationException::withMessages([
                'token' => 'Data presensi belum tersedia, hubungi dosen Anda.',
            ]);
        }

        if ($absensi->status_kehadiran === StatusKehadiranEnum::HADIR) {
            return $absensi;
        }

        $isDuplikat = $this->cekDuplikat($sesi->id, $absensi->id, $ipAddress, $deviceFingerprint);

        $absensi->update([
            'status_kehadiran' => StatusKehadiranEnum::HADIR->value,
            'waktu_check_in' => now(),
            'is_manual_update' => false,
            'ip_address' => $ipAddress,
            'device_fingerprint' => $deviceFingerprint,
            'is_flagged_duplikat' => $isDuplikat,
        ]);

        if ($isDuplikat) {
            $this->tandaiPasanganDuplikat($sesi->id, $ipAddress, $deviceFingerprint);
        }

        return $absensi->fresh();
    }

    protected function cekDuplikat(string $sesiId, string $absensiIdSaatIni, ?string $ipAddress, ?string $deviceFingerprint): bool
    {
        if (blank($ipAddress) && blank($deviceFingerprint)) {
            return false;
        }

        return PerkuliahanAbsensi::where('perkuliahan_sesi_id', $sesiId)
            ->where('id', '!=', $absensiIdSaatIni)
            ->where('status_kehadiran', StatusKehadiranEnum::HADIR->value)
            ->where(function ($q) use ($ipAddress, $deviceFingerprint) {
                if (filled($deviceFingerprint)) {
                    $q->orWhere('device_fingerprint', $deviceFingerprint);
                }
                if (filled($ipAddress)) {
                    $q->orWhere('ip_address', $ipAddress);
                }
            })
            ->exists();
    }

    protected function tandaiPasanganDuplikat(string $sesiId, ?string $ipAddress, ?string $deviceFingerprint): void
    {
        PerkuliahanAbsensi::where('perkuliahan_sesi_id', $sesiId)
            ->where('status_kehadiran', StatusKehadiranEnum::HADIR->value)
            ->where(function ($q) use ($ipAddress, $deviceFingerprint) {
                if (filled($deviceFingerprint)) {
                    $q->orWhere('device_fingerprint', $deviceFingerprint);
                }
                if (filled($ipAddress)) {
                    $q->orWhere('ip_address', $ipAddress);
                }
            })
            ->update(['is_flagged_duplikat' => true]);
    }
}
