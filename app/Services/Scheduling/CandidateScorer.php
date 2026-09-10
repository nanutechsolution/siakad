<?php

namespace App\Services\Scheduling;

use App\Services\Scheduling\Support\Candidate;
use App\Services\Scheduling\Support\DemandItem;
use App\Services\Scheduling\Support\ScheduleTracker;
use Carbon\Carbon;

class CandidateScorer
{
    /**
     * Bobot default -- bisa dipindah ke config_snapshot kalau nanti mau
     * dikontrol dari form Filament tanpa deploy ulang.
     */
    protected array $bobot = [
        'distribusi_hari' => 3.0,
        'distribusi_slot' => 2.0,
        'distribusi_dosen' => 2.5,
        'fit_kapasitas' => 2.0,
        'distribusi_ruang' => 1.5,
        'fairness_prodi' => 1.5,
        'preferensi_pagi' => 50.0,
    ];

    protected array $slotMulaiList = [];

    public function __construct(
        protected array $hariOperasional,
        protected array $jamOperasional,
    ) {
        $slots = [];

        foreach ($this->jamOperasional as $ops) {
            $start = Carbon::parse($ops['mulai'] ?? '08:00');
            $end = Carbon::parse($ops['selesai'] ?? '16:00');

            while ($start->lessThan($end)) {
                $slots[] = $start->format('H:i');
                $start->addMinutes(15);
            }
        }

        $this->slotMulaiList = array_values(array_unique($slots));
        sort($this->slotMulaiList);
    }

    public function scoreAll(array $candidates, DemandItem $item, ScheduleTracker $tracker): array
    {
        foreach ($candidates as $c) {
            $c->skor = $this->score($c, $item, $tracker);
        }

        usort($candidates, fn(Candidate $a, Candidate $b) => $a->skor <=> $b->skor);

        return $candidates;
    }

    protected function score(Candidate $c, DemandItem $item, ScheduleTracker $tracker): float
    {
        $skor = 0.0;

        // Distribusi hari: makin di atas rata-rata beban hari itu, makin dihindari
        $rataHari = $tracker->rataRataBebanHari($this->hariOperasional);
        $skor += $this->bobot['distribusi_hari'] * $this->deviasiRelatif($tracker->bebanHari($c->hari), $rataHari);

        // Distribusi slot (Sekarang berbasis kelipatan 15 menit)
        $rataSlot = $tracker->rataRataBebanSlot($this->hariOperasional, $this->slotMulaiList);
        $skor += $this->bobot['distribusi_slot'] * $this->deviasiRelatif($tracker->bebanSlot($c->hari, $c->jamMulai), $rataSlot);

        // Distribusi beban dosen per hari (rata-rata dari SEMUA dosen di item ini)
        foreach ($item->dosenIds as $dId) {
            $rataDosen = $tracker->rataRataBebanDosen($dId, $this->hariOperasional);
            $skor += ($this->bobot['distribusi_dosen'] / max(count($item->dosenIds), 1))
                * $this->deviasiRelatif($tracker->bebanDosenHari($dId, $c->hari), $rataDosen);
        }

        // Fit kapasitas: hindari ruang jauh lebih besar dari kebutuhan
        $selisih = $c->kapasitasRuang - $item->kapasitasDibutuhkan;
        $skor += $this->bobot['fit_kapasitas'] * min($selisih / max($item->kapasitasDibutuhkan, 1), 3.0);

        // Distribusi pemakaian ruang
        $rataRuang = $tracker->rataRataBebanRuang([$c->ruangId]);
        $skor += $this->bobot['distribusi_ruang'] * $this->deviasiRelatif($tracker->bebanRuang($c->ruangId), $rataRuang);

        // Fairness antar prodi
        $skor += $this->bobot['fairness_prodi'] * $tracker->bebanProdiHari($item->kelasProdiId, $c->hari) * 0.5;

        
        // ----------------------------------------------------------------------
        // 1. Konversi jam kandidat saat ini menjadi total menit
        $waktuArray = explode(':', $c->jamMulai);
        $menitHariIni = ((int)$waktuArray[0] * 60) + (int)$waktuArray[1];

        // 2. AMBIL JAM BUKA KAMPUS HARI INI DARI FORM UI (Tidak pakai asumsi lagi)
        $jamBukaKampus = $this->jamOperasional[$c->hari]['mulai'] ?? '08:00';
        $bukaArray = explode(':', $jamBukaKampus);
        $menitBuka = ((int)$bukaArray[0] * 60) + (int)$bukaArray[1];

        // 3. Hitung seberapa jauh jam kandidat ini bergeser dari jam buka kampus
        $menitDariPagi = max($menitHariIni - $menitBuka, 0);

        // Setiap 15 menit bergeser dari jam buka, kandidat akan diberi PENALTI skor.
        $skor += $this->bobot['preferensi_pagi'] * ($menitDariPagi / 15);
        // ----------------------------------------------------------------------

        return $skor;
    }

    protected function deviasiRelatif(float $nilai, float $rata): float
    {
        if ($rata <= 0) {
            return $nilai > 0 ? 1.0 : 0.0;
        }
        return max(($nilai - $rata) / $rata, 0);
    }
}
