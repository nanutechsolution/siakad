<?php

declare(strict_types=1);

namespace App\Application\Migration\Services;

use App\Domain\Akademik\Enums\StatusAmbil;
use App\Models\KrsDetail;
use App\Models\RefTahunAkademik;

/**
 * Menentukan status_ambil (BARU/ULANG) untuk satu baris migrasi nilai
 * berdasarkan urutan kronologis tahun_akademik.
 *
 * Aturan: jika mahasiswa sudah memiliki krs_detail untuk mata_kuliah yang sama
 * pada tahun_akademik yang LEBIH AWAL (kode_tahun lebih kecil), maka baris ini
 * dianggap ULANG. Jika tidak ada riwayat sebelumnya, dianggap BARU.
 *
 * PENTING: service ini membaca state database secara real-time. Agar akurat,
 * ImportGradeService (Fase 4) WAJIB memproses baris per mahasiswa+mata_kuliah
 * dalam urutan tahun_akademik ASCENDING sebelum memanggil resolve(), karena
 * urutan pemrosesan menentukan apakah suatu baris "sudah punya riwayat lebih awal"
 * pada saat itu dicek.
 */
final class StatusAmbilResolverService
{
    public function resolve(string $mahasiswaId, int $mataKuliahId, RefTahunAkademik $tahunAkademikSaatIni): StatusAmbil
    {
        $adaRiwayatLebihAwal = KrsDetail::query()
            ->where('mata_kuliah_id', $mataKuliahId)
            ->whereHas('krs', function ($query) use ($mahasiswaId, $tahunAkademikSaatIni) {
                $query->where('mahasiswa_id', $mahasiswaId)
                    ->whereHas('tahunAkademik', function ($subQuery) use ($tahunAkademikSaatIni) {
                        $subQuery->where('kode_tahun', '<', $tahunAkademikSaatIni->kode_tahun);
                    });
            })
            ->exists();

        return $adaRiwayatLebihAwal ? StatusAmbil::ULANG : StatusAmbil::BARU;
    }
}
