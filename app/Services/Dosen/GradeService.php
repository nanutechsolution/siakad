<?php

declare(strict_types=1);

namespace App\Services\Dosen;

use App\Models\AkademikGradeRevisionLog;
use App\Models\JadwalKuliah;
use App\Models\KrsDetail;
use App\Models\RefSkalaNilai;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GradeService
{
    /**
     * Hitung nilai akhir (angka, huruf, indeks) satu baris KrsDetail
     * berdasarkan nilai per komponen x bobot masing-masing, lalu simpan.
     *
     * @param  Collection<int, \App\Models\JadwalKomponenNilai>  $komponenAktif
     */
    public function calculateFinalGrade(KrsDetail $krsDetail, Collection $komponenAktif): KrsDetail
    {
        if ($krsDetail->is_locked) {
            return $krsDetail;
        }

        $krsDetail->loadMissing('detailNilai');

        $nilaiAkhir = 0.0;

        foreach ($komponenAktif as $komponen) {
            $nilaiKomponen = $krsDetail->getNilaiKomponen((int) $komponen->komponen_id) ?? 0.0;
            $bobot = (float) $komponen->bobot_persen;

            $nilaiAkhir += ($nilaiKomponen * $bobot) / 100;
        }

        $nilaiAkhir = round($nilaiAkhir, 2);
        $skala = RefSkalaNilai::forNilai($nilaiAkhir);

        $krsDetail->update([
            'nilai_angka' => $nilaiAkhir,
            'nilai_huruf' => $skala?->huruf ?? 'E',
            'nilai_indeks' => $skala?->bobot_indeks ?? 0.00,
        ]);

        return $krsDetail->fresh();
    }

    /**
     * Hitung ulang nilai akhir semua mahasiswa di satu kelas sekaligus.
     * Pakai chunkById supaya aman untuk kelas dengan peserta banyak
     * (tidak menarik semua baris ke memori sekaligus).
     */
    public function calculateFinalGradesForClass(JadwalKuliah $jadwalKuliah): void
    {
        $komponenAktif = \App\Models\KurikulumKomponenNilai::with('komponen')
            ->where('kurikulum_id', $jadwalKuliah->kurikulum_id)
            ->get();

        $jadwalKuliah->krsDetails()
            ->with('detailNilai')
            ->where('is_locked', false)
            ->chunkById(50, function (Collection $chunk) use ($komponenAktif) {
                foreach ($chunk as $krsDetail) {
                    $this->calculateFinalGrade($krsDetail, $komponenAktif);
                }
            });
    }
    /**
     * Publish + kunci seluruh nilai mahasiswa di satu kelas sekaligus.
     * Baris yang sudah published sebelumnya tidak disentuh lagi (idempotent).
     *
     * @return int  Jumlah baris KrsDetail yang berhasil di-publish.
     */
    public function publishClassGrades(JadwalKuliah $jadwalKuliah): int
    {
        return DB::transaction(function () use ($jadwalKuliah) {
            return $jadwalKuliah->krsDetails()
                ->where('is_published', false)
                ->update([
                    'is_published' => true,
                    'is_locked' => true,
                ]);
        });
    }

    /**
     * Terapkan revisi nilai pada baris yang sudah locked/published,
     * sekaligus mencatat audit trail ke akademik_grade_revision_logs.
     * Nilai lama & baru + alasan wajib tercatat sebelum krs_detail diubah.
     */
    public function applyRevision(
        KrsDetail $krsDetail,
        float $nilaiAngkaBaru,
        string $alasanPerbaikan,
        ?string $nomorSkPerbaikan,
        string $executedByUserId,
    ): AkademikGradeRevisionLog {
        return DB::transaction(function () use (
            $krsDetail,
            $nilaiAngkaBaru,
            $alasanPerbaikan,
            $nomorSkPerbaikan,
            $executedByUserId,
        ) {
            $oldAngka = (float) $krsDetail->nilai_angka;
            $oldHuruf = (string) ($krsDetail->nilai_huruf ?? '');

            $skala = RefSkalaNilai::forNilai($nilaiAngkaBaru);
            $newHuruf = $skala?->huruf ?? 'E';

            $log = AkademikGradeRevisionLog::create([
                'krs_detail_id' => $krsDetail->id,
                'old_nilai_angka' => $oldAngka,
                'old_nilai_huruf' => $oldHuruf,
                'new_nilai_angka' => $nilaiAngkaBaru,
                'new_nilai_huruf' => $newHuruf,
                'alasan_perbaikan' => $alasanPerbaikan,
                'nomor_sk_perbaikan' => $nomorSkPerbaikan,
                'executed_by' => $executedByUserId,
            ]);

            $krsDetail->update([
                'nilai_angka' => $nilaiAngkaBaru,
                'nilai_huruf' => $newHuruf,
                'nilai_indeks' => $skala?->bobot_indeks ?? 0.00,
            ]);

            return $log;
        });
    }
}
