<?php

declare(strict_types=1);

namespace App\Services\Keuangan;

use App\Enums\Keuangan\TipeDiskonBeasiswa;
use App\Models\KeuanganKomponenBiaya;
use App\Models\KeuanganMahasiswaBeasiswa;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;

class BeasiswaDiskonService
{
    /**
     * Menghitung total nilai diskon beasiswa untuk satu komponen tagihan.
     * Tidak akan mengembalikan nilai melebihi nominal dasar (tagihan tidak bisa negatif).
     */
    public function hitungDiskonUntukKomponen(
        Mahasiswa $mahasiswa,
        KeuanganKomponenBiaya $komponen,
        RefTahunAkademik $tahunAkademik,
        float $nominalDasar
    ): float {
        if ($nominalDasar <= 0.0) {
            return 0.0;
        }

        $beasiswaAktifs = KeuanganMahasiswaBeasiswa::with(['beasiswa.details'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('is_active', true)
            ->get();

        if ($beasiswaAktifs->isEmpty()) {
            return 0.0;
        }

        $totalDiskon = 0.0;

        foreach ($beasiswaAktifs as $pemberian) {
            if (! $this->isTahunAkademikValid($pemberian, $tahunAkademik)) {
                continue;
            }

            if (! $pemberian->beasiswa || ! $pemberian->beasiswa->is_active) {
                continue;
            }

            $aturanDiskon = $pemberian->beasiswa->details->firstWhere('komponen_biaya_id', $komponen->id);

            if ($aturanDiskon) {
                $totalDiskon += $this->kalkulasiNominal($aturanDiskon, $nominalDasar);
            }
        }

        // Capping: Total diskon tidak boleh melebihi tagihan asli
        return min($totalDiskon, $nominalDasar);
    }

    /**
     * Validasi periode berlakunya beasiswa terhadap tahun akademik tagihan.
     * Asumsi: ID tahun akademik bersifat inkremental merepresentasikan urutan kronologis.
     */
    private function isTahunAkademikValid(KeuanganMahasiswaBeasiswa $pemberian, RefTahunAkademik $tahunAkademikTagihan): bool
    {
        if ($pemberian->tahun_akademik_mulai_id > $tahunAkademikTagihan->id) {
            return false;
        }

        if ($pemberian->tahun_akademik_akhir_id !== null && $pemberian->tahun_akademik_akhir_id < $tahunAkademikTagihan->id) {
            return false;
        }

        return true;
    }

    /**
     * Hitung nilai diskon parsial berdasarkan aturan yang ditetapkan.
     */
    private function kalkulasiNominal(mixed $aturanDiskon, float $nominalDasar): float
    {
        if ($aturanDiskon->tipe_diskon === TipeDiskonBeasiswa::PERSENTASE) {
            return $nominalDasar * ((float) $aturanDiskon->nilai_diskon / 100);
        }

        if ($aturanDiskon->tipe_diskon === TipeDiskonBeasiswa::NOMINAL) {
            return (float) $aturanDiskon->nilai_diskon;
        }

        return 0.0;
    }
}
