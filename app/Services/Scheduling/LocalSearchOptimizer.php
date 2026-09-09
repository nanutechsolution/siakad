<?php

namespace App\Services\Scheduling;

use App\Services\Scheduling\Support\ScheduleTracker;

class LocalSearchOptimizer
{
    public function __construct(
        protected CandidateGenerator $generator,
        protected CandidateScorer $scorer,
        protected int $maxIterasi = 3,
        protected int $jumlahDicobaPerIterasi = 10,
    ) {
    }

    /**
     * @param array $assigned list of ['item' => DemandItem, 'candidate' => Candidate]
     * @return array assigned yang sudah diperbaiki (array yang sama, beberapa 'candidate' mungkin berubah)
     */
    public function optimize(array $assigned, ScheduleTracker $tracker): array
    {
        if (empty($assigned)) {
            return $assigned;
        }

        for ($iterasi = 0; $iterasi < $this->maxIterasi; $iterasi++) {
            usort($assigned, fn ($a, $b) => $b['candidate']->skor <=> $a['candidate']->skor);

            $adaPerbaikan = false;

            foreach (array_slice($assigned, 0, $this->jumlahDicobaPerIterasi) as $idx => $entry) {
                $item = $entry['item'];
                $kandidatLama = $entry['candidate'];

                // Lepas sementara reservasi lama supaya slot lamanya ikut jadi opsi lagi
                $tracker->unreserve(
                    $item->dosenIds,
                    $item->kelasId,
                    $kandidatLama->ruangId,
                    $kandidatLama->hari,
                    $kandidatLama->jamMulai,
                    $kandidatLama->jamSelesai,
                    $item->kelasProdiId
                );

                $hasil = $this->generator->generate($item, $tracker);
                $kandidatBaruTerbaik = null;

                if (!empty($hasil['candidates'])) {
                    $scored = $this->scorer->scoreAll($hasil['candidates'], $item, $tracker);
                    $kandidatBaruTerbaik = $scored[0];
                }

                if ($kandidatBaruTerbaik && $kandidatBaruTerbaik->skor < $kandidatLama->skor) {
                    // Perbaikan ditemukan -- pasang di slot baru
                    $tracker->reserve(
                        $item->dosenIds,
                        $item->kelasId,
                        $kandidatBaruTerbaik->ruangId,
                        $kandidatBaruTerbaik->hari,
                        $kandidatBaruTerbaik->jamMulai,
                        $kandidatBaruTerbaik->jamSelesai,
                        $item->kelasProdiId
                    );
                    $assigned[$idx]['candidate'] = $kandidatBaruTerbaik;
                    $adaPerbaikan = true;
                } else {
                    // Tidak ada perbaikan -- kembalikan reservasi lama persis seperti semula
                    $tracker->reserve(
                        $item->dosenIds,
                        $item->kelasId,
                        $kandidatLama->ruangId,
                        $kandidatLama->hari,
                        $kandidatLama->jamMulai,
                        $kandidatLama->jamSelesai,
                        $item->kelasProdiId
                    );
                }
            }

            if (!$adaPerbaikan) {
                break; // konvergen, tidak perlu iterasi lagi
            }
        }

        return $assigned;
    }
}
