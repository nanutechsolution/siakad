<?php

declare(strict_types=1);

namespace App\Services\Keuangan;

use App\Services\Keuangan\DTO\HasilPerbandinganKomponen;
use Illuminate\Support\Collection;

/**
 * Membandingkan komponen biaya dari skema tarif aktif terhadap komponen
 * yang sudah tercatat di tagihan seorang mahasiswa.
 *
 * Aturan bisnis (lihat dokumen analisis Sinkronisasi Tagihan):
 * 1. Komponen di skema tapi belum ada di tagihan -> toAdd.
 * 2. Komponen ada di keduanya, nominal DASAR beda -> toReview (tidak diubah).
 * 3. Komponen ada di tagihan tapi tidak ada di skema aktif -> toWarn (tidak dihapus).
 * 4. Selain itu -> unchanged, tidak ada aksi.
 *
 * Perbandingan nominal dilakukan terhadap `nominal_dasar` (nominal tarif
 * sebelum diskon beasiswa), BUKAN `nominal_tagihan` (net setelah diskon).
 * Diskon beasiswa itu dinamis per mahasiswa dan tidak relevan untuk
 * menentukan apakah TARIF-nya berubah atau tidak.
 */
class KomponenTagihanComparator
{
    /**
     * @param Collection $detailSkema Baris dari keuangan_detail_tarif (join
     *        keuangan_komponen_biaya), masing-masing minimal punya:
     *        komponen_biaya_id, nominal, nama_komponen.
     * @param Collection $detailExisting Baris tagihan_mahasiswas_details
     *        milik mahasiswa untuk tagihan yang sedang dibandingkan, masing-
     *        masing minimal punya: id, komponen_biaya_id,
     *        nama_komponen_snapshot, nominal_dasar.
     */
    public function bandingkan(Collection $detailSkema, Collection $detailExisting): HasilPerbandinganKomponen
    {
        $skemaByKomponen = $detailSkema->keyBy(
            fn($item) => (int) (is_array($item) ? $item['komponen_biaya_id'] : $item->komponen_biaya_id)
        );

        $existingByKomponen = $detailExisting->keyBy(
            fn($item) => (int) (is_array($item) ? $item['komponen_biaya_id'] : $item->komponen_biaya_id)
        );

        $toAdd = [];
        $toReview = [];
        $unchanged = [];

        foreach ($skemaByKomponen as $komponenId => $skema) {
            $nominalSkema = (float) $this->get($skema, 'nominal');
            $namaKomponen = (string) $this->get($skema, 'nama_komponen');

            $existing = $existingByKomponen->get($komponenId);

            if ($existing === null) {
                $toAdd[] = [
                    'komponen_biaya_id' => $komponenId,
                    'nama_komponen' => $namaKomponen,
                    'nominal' => $nominalSkema,
                ];
                continue;
            }

            $nominalExisting = (float) $this->get($existing, 'nominal_dasar');

            // Perbandingan float nominal rupiah - pakai selisih absolut kecil
            // (0.005) untuk menghindari false-positive akibat pembulatan
            // desimal, bukan perbandingan == langsung.
            if (abs($nominalExisting - $nominalSkema) > 0.005) {
                $toReview[] = [
                    'tagihan_detail_id' => (int) $this->get($existing, 'id'),
                    'komponen_biaya_id' => $komponenId,
                    'nama_komponen' => $namaKomponen,
                    'nominal_existing' => $nominalExisting,
                    'nominal_skema_baru' => $nominalSkema,
                ];
                continue;
            }

            $unchanged[] = ['komponen_biaya_id' => $komponenId];
        }

        // Komponen yang ada di tagihan tapi bukan bagian dari skema aktif
        // saat ini (dihapus/diganti kode/dsb) -> warning, bukan dihapus.
        $toWarn = [];
        foreach ($existingByKomponen as $komponenId => $existing) {
            if (! $skemaByKomponen->has($komponenId)) {
                $toWarn[] = [
                    'tagihan_detail_id' => (int) $this->get($existing, 'id'),
                    'komponen_biaya_id' => $komponenId,
                    'nama_komponen_snapshot' => (string) $this->get($existing, 'nama_komponen_snapshot'),
                    'nominal_existing' => (float) $this->get($existing, 'nominal_dasar'),
                ];
            }
        }

        return new HasilPerbandinganKomponen(
            toAdd: $toAdd,
            toReview: $toReview,
            toWarn: $toWarn,
            unchanged: $unchanged,
        );
    }

    private function get(mixed $row, string $key): mixed
    {
        return is_array($row) ? ($row[$key] ?? null) : ($row->{$key} ?? null);
    }
}
