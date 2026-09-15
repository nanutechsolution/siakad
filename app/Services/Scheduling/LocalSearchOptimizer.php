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
    ) {}

    /**
     * @param array $assigned list of ['item' => DemandItem, 'candidate' => Candidate]
     * @return array assigned yang sudah diperbaiki
     */
    public function optimize(array $assigned, ScheduleTracker $tracker): array
    {
        if (empty($assigned)) {
            return $assigned;
        }

        for ($iterasi = 0; $iterasi < $this->maxIterasi; $iterasi++) {
            usort(
                $assigned,
                fn($a, $b) => $b['candidate']->skor <=> $a['candidate']->skor
            );

            $adaPerbaikan = false;

            foreach (
                array_keys(
                    array_slice(
                        $assigned,
                        0,
                        $this->jumlahDicobaPerIterasi,
                        true
                    )
                ) as $idx
            ) {
                $entry = $assigned[$idx];

                $item = $entry['item'];
                $kandidatLama = $entry['candidate'];

                /*
                 * Lepas sementara reservasi lama.
                 *
                 * Penting:
                 * gunakan kampus aktual dari ruang lama,
                 * bukan kampus asal kelas.
                 */
                $tracker->unreserve(
                    $item->dosenIds,
                    $item->kelasId,
                    $kandidatLama->ruangId,
                    $kandidatLama->hari,
                    $kandidatLama->jamMulai,
                    $kandidatLama->jamSelesai,
                    $item->kelasProdiId,
                    $kandidatLama->assignedKampusId
                );

                $hasil = $this->generator->generate(
                    $item,
                    $tracker
                );

                $kandidatBaruTerbaik = null;

                if (!empty($hasil['candidates'])) {
                    $scored = $this->scorer->scoreAll(
                        $hasil['candidates'],
                        $item,
                        $tracker
                    );

                    $kandidatBaruTerbaik = $scored[0] ?? null;
                }

                /*
                 * Jangan ubah arah perbandingan skor dulu.
                 * Ini mengikuti kode yang sekarang sampai CandidateScorer
                 * dikonfirmasi.
                 */
                if (
                    $kandidatBaruTerbaik
                    && $kandidatBaruTerbaik->skor < $kandidatLama->skor
                ) {
                    $tracker->reserve(
                        $item->dosenIds,
                        $item->kelasId,
                        $kandidatBaruTerbaik->ruangId,
                        $kandidatBaruTerbaik->hari,
                        $kandidatBaruTerbaik->jamMulai,
                        $kandidatBaruTerbaik->jamSelesai,
                        $item->kelasProdiId,
                        $kandidatBaruTerbaik->assignedKampusId
                    );

                    $assigned[$idx]['candidate'] = $kandidatBaruTerbaik;
                    $adaPerbaikan = true;
                } else {
                    /*
                     * Rollback assignment lama.
                     */
                    $tracker->reserve(
                        $item->dosenIds,
                        $item->kelasId,
                        $kandidatLama->ruangId,
                        $kandidatLama->hari,
                        $kandidatLama->jamMulai,
                        $kandidatLama->jamSelesai,
                        $item->kelasProdiId,
                        $kandidatLama->assignedKampusId
                    );
                }
            }

            if (!$adaPerbaikan) {
                break;
            }
        }

        return $assigned;
    }
}
