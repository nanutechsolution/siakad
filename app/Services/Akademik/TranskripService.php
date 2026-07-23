<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\Models\AkademikTranskrip;
use App\Models\JadwalKuliah;
use App\Models\KrsDetail;
use Illuminate\Support\Facades\DB;

class TranskripService
{
    /**
     * Sinkronkan satu KRS Detail ke Transkrip Akademik.
     */
    public function sinkronkanKrsDetail(KrsDetail $detail): void
    {
        if (! $detail->is_published) {
            return;
        }

        $detail->loadMissing([
            'krs.mahasiswa',
            'jadwalKuliah.mataKuliah',
        ]);

        $mahasiswa = $detail->krs?->mahasiswa;
        $mataKuliah = $detail->jadwalKuliah?->mataKuliah;

        if (! $mahasiswa || ! $mataKuliah) {
            return;
        }

        DB::transaction(function () use ($detail, $mahasiswa, $mataKuliah) {

            $existing = AkademikTranskrip::where([
                'mahasiswa_id'   => $mahasiswa->id,
                'mata_kuliah_id' => $mataKuliah->id,
            ])->first();

            if ($existing) {

                if (! $this->shouldReplace($existing, $detail)) {
                    return;
                }

                $existing->update([
                    'krs_detail_id'      => $detail->id,
                    'sks_diakui'         => $detail->sks_snapshot,
                    'nilai_angka_final'  => $detail->nilai_angka,
                    'nilai_huruf_final'  => $detail->nilai_huruf,
                    'nilai_indeks_final' => $detail->nilai_indeks,
                    'is_konversi'        => false,
                ]);

                return;
            }

            AkademikTranskrip::create([
                'mahasiswa_id'       => $mahasiswa->id,
                'mata_kuliah_id'     => $mataKuliah->id,
                'krs_detail_id'      => $detail->id,
                'sks_diakui'         => $detail->sks_snapshot,
                'nilai_angka_final'  => $detail->nilai_angka,
                'nilai_huruf_final'  => $detail->nilai_huruf,
                'nilai_indeks_final' => $detail->nilai_indeks,
                'is_konversi'        => false,
            ]);
        });
    }

    /**
     * Sinkronkan seluruh peserta dalam satu kelas.
     */
    public function sinkronkanKelas(JadwalKuliah $jadwal): void
    {
        $jadwal->loadMissing([
            'krsDetails.krs.mahasiswa',
            'krsDetails.jadwalKuliah.mataKuliah',
        ]);

        foreach ($jadwal->krsDetails as $detail) {
            $this->sinkronkanKrsDetail($detail);
        }
    }

    /**
     * Sinkronkan seluruh transkrip mahasiswa.
     */
    public function sinkronkanMahasiswa(string $mahasiswaId): void
    {
        $details = KrsDetail::query()
            ->whereHas('krs', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            })
            ->where('is_published', true)
            ->with([
                'krs.mahasiswa',
                'jadwalKuliah.mataKuliah',
            ])
            ->get();

        foreach ($details as $detail) {
            $this->sinkronkanKrsDetail($detail);
        }
    }

    /**
     * Sinkronkan seluruh mahasiswa dalam sistem.
     * Berguna ketika migrasi atau perbaikan data.
     */
    public function sinkronkanSemua(): void
    {
        KrsDetail::query()
            ->where('is_published', true)
            ->with([
                'krs.mahasiswa',
                'jadwalKuliah.mataKuliah',
            ])
            ->chunkById(100, function ($details) {
                foreach ($details as $detail) {
                    $this->sinkronkanKrsDetail($detail);
                }
            });
    }

    /**
     * Menentukan apakah record transkrip lama boleh diganti.
     *
     * Saat ini menggunakan kebijakan:
     * Nilai TERAKHIR menggantikan nilai lama.
     *
     * Jika kampus menginginkan nilai terbaik,
     * cukup ubah logika method ini.
     */
    private function shouldReplace(
        AkademikTranskrip $existing,
        KrsDetail $baru,
    ): bool {
        return true;
    }
}
