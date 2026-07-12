<?php

namespace App\Http\Controllers;

use App\Enums\StatusKehadiran;
use App\Enums\StatusSesiPerkuliahan;
use App\Models\KrsDetail;
use App\Models\PerkuliahanAbsensi;
use App\Models\PerkuliahanSesi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PresensiCheckinController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:6'],
        ]);

        $sesi = PerkuliahanSesi::where('token_sesi', $data['token'])
            ->where('status_sesi', StatusSesiPerkuliahan::Dibuka->value)
            ->first();

        if (! $sesi) {
            throw ValidationException::withMessages([
                'token' => 'Kode tidak valid atau sesi sudah ditutup.',
            ]);
        }

        $mahasiswa = $request->user()->person?->mahasiswa;

        abort_unless($mahasiswa, 403, 'Akun Anda tidak terhubung ke data mahasiswa.');

        $krsDetail = KrsDetail::whereHas('krs', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
            ->where('jadwal_kuliah_id', $sesi->jadwal_kuliah_id)
            ->first();

        abort_unless($krsDetail, 403, 'Anda tidak terdaftar di kelas ini.');

        $absensi = PerkuliahanAbsensi::where('perkuliahan_sesi_id', $sesi->id)
            ->where('krs_detail_id', $krsDetail->id)
            ->first();

        abort_unless($absensi, 404, 'Data absensi belum tersedia, hubungi dosen.');

        $absensi->update([
            'status_kehadiran' => StatusKehadiran::Hadir->value,
            'waktu_check_in' => now(),
        ]);

        return response()->json(['message' => 'Presensi berhasil dicatat.']);
    }
}