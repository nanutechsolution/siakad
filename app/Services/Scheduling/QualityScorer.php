<?php

namespace App\Services\Scheduling;

class QualityScorer
{
    /**
     * Ubah skor mentah (makin kecil makin baik, tidak berbatas atas) jadi
     * skor 0-100 (makin besar makin baik) yang gampang dibaca operator.
     * Skala referensi diambil dari skor maksimum yang muncul di batch ini
     * sendiri, supaya tetap masuk akal walau bobot scoring berubah nanti.
     */
    public function skorAssignment(float $skorMentah, float $skorMentahMaksDiBatchIni): int
    {
        if ($skorMentahMaksDiBatchIni <= 0) {
            return 100;
        }
        $relatif = 1 - min($skorMentah / $skorMentahMaksDiBatchIni, 1.0);
        return (int) round($relatif * 100);
    }

    /**
     * @param array $assigned list of ['item' => DemandItem, 'candidate' => Candidate]
     * @param array $failed list of ['item' => DemandItem, 'reason' => string]
     */
    public function ringkasanBatch(array $assigned, array $failed): array
    {
        $total = count($assigned) + count($failed);
        $tingkatKeberhasilan = $total > 0 ? count($assigned) / $total : 0.0;

        $skorList = array_map(fn ($a) => $a['candidate']->skor, $assigned);
        $rataRataSkorMentah = empty($skorList) ? 0.0 : array_sum($skorList) / count($skorList);

        $mean = $rataRataSkorMentah;
        $variance = empty($skorList) ? 0.0 : array_sum(array_map(fn ($s) => ($s - $mean) ** 2, $skorList)) / count($skorList);
        $stdDev = sqrt($variance);

        // Skor ringkasan 0-100: gabungan tingkat keberhasilan (bobot besar)
        // dan kualitas rata-rata (stdDev rendah = distribusi lebih rata = lebih baik)
        $skorKualitas = $stdDev > 0 ? max(0, 100 - ($stdDev * 10)) : 100;
        $skorAkhir = round(($tingkatKeberhasilan * 70) + ($skorKualitas * 0.3), 2);

        return [
            'quality_score' => $skorAkhir,
            'quality_summary' => [
                'total_demand' => $total,
                'total_berhasil' => count($assigned),
                'total_gagal' => count($failed),
                'tingkat_keberhasilan_persen' => round($tingkatKeberhasilan * 100, 1),
                'rata_rata_skor_mentah' => round($rataRataSkorMentah, 3),
                'std_dev_skor' => round($stdDev, 3),
            ],
        ];
    }
}
