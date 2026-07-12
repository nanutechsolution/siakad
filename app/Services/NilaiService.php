<?php

namespace App\Services;

use App\Models\JadwalKomponenNilai;
use App\Models\JadwalKuliah;
use App\Models\KrsDetail;
use App\Models\KrsDetailNilai;
use App\Models\RefSkalaNilai;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NilaiService
{
    public function simpanNilaiKomponen(KrsDetail $krsDetail, int $komponenId, float $nilaiAngka): KrsDetailNilai
    {
        $this->guardEditable($krsDetail);

        if ($nilaiAngka < 0 || $nilaiAngka > 100) {
            throw ValidationException::withMessages([
                'nilai' => 'Nilai komponen harus di antara 0 dan 100.',
            ]);
        }

        return DB::transaction(function () use ($krsDetail, $komponenId, $nilaiAngka) {
            $nilaiKomponen = KrsDetailNilai::updateOrCreate(
                ['krs_detail_id' => $krsDetail->id, 'komponen_id' => $komponenId],
                ['nilai_angka' => $nilaiAngka]
            );

            $this->hitungUlangNilaiAkhir($krsDetail);

            return $nilaiKomponen;
        });
    }

    public function hitungUlangNilaiAkhir(KrsDetail $krsDetail): KrsDetail
    {
        $bobotKomponen = JadwalKomponenNilai::where('jadwal_kuliah_id', $krsDetail->jadwal_kuliah_id)
            ->get()
            ->keyBy('komponen_id');

        $nilaiKomponen = KrsDetailNilai::where('krs_detail_id', $krsDetail->id)->get();

        $nilaiAkhir = 0.0;

        foreach ($nilaiKomponen as $nk) {
            $bobot = (float) ($bobotKomponen->get($nk->komponen_id)?->bobot_persen ?? 0);
            $nilaiAkhir += ((float) $nk->nilai_angka) * $bobot / 100;
        }

        $skala = RefSkalaNilai::where('nilai_min', '<=', $nilaiAkhir)
            ->where('nilai_max', '>=', $nilaiAkhir)
            ->first();

        $krsDetail->update([
            'nilai_angka' => round($nilaiAkhir, 2),
            'nilai_huruf' => $skala?->huruf,
            'nilai_indeks' => $skala?->bobot_indeks ?? 0,
        ]);

        return $krsDetail->fresh();
    }

    public function publishNilai(JadwalKuliah $jadwal): int
    {
        $tahunAkademik = $jadwal->tahunAkademik;

        if (! $tahunAkademik->buka_input_nilai || $tahunAkademik->is_locked_nilai) {
            throw ValidationException::withMessages([
                'nilai' => 'Periode input nilai untuk tahun akademik ini sudah ditutup.',
            ]);
        }

        return KrsDetail::where('jadwal_kuliah_id', $jadwal->id)
            ->update(['is_published' => true, 'is_locked' => true]);
    }

    protected function guardEditable(KrsDetail $krsDetail): void
    {
        if ($krsDetail->is_locked) {
            throw ValidationException::withMessages([
                'nilai' => 'Nilai mahasiswa ini sudah dikunci dan tidak dapat diubah.',
            ]);
        }

        $tahunAkademik = $krsDetail->jadwalKuliah->tahunAkademik;

        if (! $tahunAkademik->buka_input_nilai || $tahunAkademik->is_locked_nilai) {
            throw ValidationException::withMessages([
                'nilai' => 'Periode input nilai belum dibuka atau sudah ditutup untuk tahun akademik ini.',
            ]);
        }
    }
}