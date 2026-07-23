<?php

declare(strict_types=1);

namespace App\Application\Migration\Services;

use App\Models\AkademikTranskrip;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\MasterMataKuliah;

/**
 * Menjaga tabel akademik_transkrip tetap sinkron dengan krs_detail hasil migrasi.
 *
 * ASUMSI (mohon dikonfirmasi/disesuaikan bila kebijakan akademik UNMARIS berbeda):
 * nilai yang diakui di transkrip resmi adalah nilai_indeks TERTINGGI di antara
 * seluruh percobaan (BARU/ULANG) untuk mata kuliah yang sama — bukan nilai
 * percobaan terakhir. Jika kebijakan Anda "nilai terakhir selalu menggantikan",
 * hapus blok early-return di bawah.
 */
final class AkademikTranskripSyncService
{
    public function sync(Mahasiswa $mahasiswa, MasterMataKuliah $mataKuliah, KrsDetail $krsDetail): void
    {
        $existing = AkademikTranskrip::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->first();

        if (
            $existing instanceof AkademikTranskrip
            && (float) $existing->nilai_indeks_final >= (float) $krsDetail->nilai_indeks
        ) {
            return;
        }

        AkademikTranskrip::query()->updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswa->id,
                'mata_kuliah_id' => $mataKuliah->id,
            ],
            [
                'krs_detail_id' => $krsDetail->id,
                'sks_diakui' => $mataKuliah->sks_default,
                'nilai_angka_final' => $krsDetail->nilai_angka,
                'nilai_huruf_final' => $krsDetail->nilai_huruf,
                'nilai_indeks_final' => $krsDetail->nilai_indeks,
                'is_konversi' => false,
            ],
        );
    }
}
