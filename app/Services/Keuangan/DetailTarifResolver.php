<?php

declare(strict_types=1);

namespace App\Services\Keuangan;

use App\Domain\Akademik\Resolvers\MahasiswaAcademicResolver;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DetailTarifResolver
{
    public function resolve(
        Mahasiswa $mahasiswa,
        int $skemaTarifId,
        ?RefTahunAkademik $tahunAkademik = null,
    ): Collection {
        $academicResolver = new MahasiswaAcademicResolver($mahasiswa);

        $semesterBerjalan = $academicResolver->semesterBerjalan(
            $tahunAkademik
        );

        $detailTarif = DB::table('keuangan_detail_tarif')
            ->join(
                'keuangan_komponen_biaya',
                'keuangan_komponen_biaya.id',
                '=',
                'keuangan_detail_tarif.komponen_biaya_id'
            )
            ->where(
                'keuangan_detail_tarif.skema_tarif_id',
                $skemaTarifId
            )
            ->select(
                'keuangan_detail_tarif.*',
                'keuangan_komponen_biaya.nama_komponen'
            )
            ->get();

        /*
         * Jika semester mahasiswa tidak diketahui,
         * jangan berani menerapkan tarif ONCE.
         */
        if ($semesterBerjalan === null) {
            return $detailTarif
                ->filter(fn ($tarif) =>
                    $tarif->penerapan === 'FLAT'
                    && $tarif->berlaku_semester === null
                )
                ->values();
        }

        return $detailTarif
            ->filter(function ($tarif) use ($semesterBerjalan) {

                // Tarif setiap semester
                if ($tarif->penerapan === 'FLAT') {
                    return $tarif->berlaku_semester === null
                        || (int) $tarif->berlaku_semester === $semesterBerjalan;
                }

                // Tarif sekali pada semester tertentu
                if ($tarif->penerapan === 'ONCE') {
                    return $tarif->berlaku_semester !== null
                        && (int) $tarif->berlaku_semester === $semesterBerjalan;
                }

                return false;
            })
            ->values();
    }
}