<?php

namespace App\Services\Scheduling;

use App\Services\Scheduling\Support\Candidate;
use App\Services\Scheduling\Support\DemandItem;
use App\Services\Scheduling\Support\ScheduleTracker;
use Carbon\Carbon;

class CandidateGenerator
{
    public function __construct(
        protected array $hariOperasional,
        protected array $jamOperasional,
        protected array $jamIstirahat,
        protected string $modeWaktu,
        protected int $menitPerSks,
        protected int $menitTransisi,
        protected array $ruangTersedia,
        protected array $limitasiWaktuDosen,
    ) {}

    public function generate(DemandItem $item, ScheduleTracker $tracker): array
    {
        $candidates = [];
        $failureCodes = [];
        $alasanTerakhir = null;

        foreach ($this->hariOperasional as $hari) {

            // 1. CEK KARANTINA DOSEN
            foreach ($item->dosenIds as $dId) {
                if ($tracker->isDosenKarantinaDiHari($dId, $hari)) {
                    $failureCodes[] = 'DOSEN_KARANTINA';
                    $alasanTerakhir = 'Dosen sedang mengajar di kampus lain pada hari ini.';
                    continue 2;
                }
            }

            // 2. CABANG ALGORITMA PENJADWALAN
            if ($this->modeWaktu === 'statis') {
                /*
                 * ========================================================
                 * MODE STATIS: Looping hanya pada Blok Jam yang di-set di UI
                 * ========================================================
                 */
                $slotsHariIni = $this->jamOperasional[$hari]['slots'] ?? [];

                foreach ($slotsHariIni as $slot) {
                    $jamMulai = $slot['mulai'];
                    $jamSelesai = $slot['selesai'];

                    $jamSelesaiTransisi = Carbon::parse($jamSelesai)->addMinutes($this->menitTransisi)->format('H:i');

                    // Lakukan pengecekan konflik
                    $this->evaluasiKandidat(
                        $hari,
                        $jamMulai,
                        $jamSelesai,
                        $jamSelesaiTransisi,
                        $item,
                        $tracker,
                        $candidates,
                        $failureCodes,
                        $alasanTerakhir
                    );
                }
            } else {
                /*
                 * ========================================================
                 * MODE DINAMIS: Looping bebas mencari ruang tiap 15 Menit
                 * ========================================================
                 */
                $jamBuka = $this->jamOperasional[$hari]['mulai'] ?? '08:00';
                $jamTutup = $this->jamOperasional[$hari]['selesai'] ?? '16:00';

                $waktuSekarang = Carbon::parse($jamBuka);
                $waktuTutupObj = Carbon::parse($jamTutup);

                while ($waktuSekarang->lessThan($waktuTutupObj)) {
                    $jamMulai = $waktuSekarang->format('H:i');

                    [$jamSelesai, $jamSelesaiTransisi, $lanjut] = $this->hitungJamSelesai($jamMulai, $item->sksTotal, $jamTutup);

                    if (!$lanjut) {
                        // Agar tidak membanjiri failure code
                        if (empty($failureCodes) || end($failureCodes) !== 'MELEBIHI_JAM_KAMPUS') {
                            $failureCodes[] = 'MELEBIHI_JAM_KAMPUS';
                            $alasanTerakhir = 'Durasi mata kuliah melebihi jam operasional kampus.';
                        }
                        break;
                    }

                    $waktuSekarang->addMinutes(15);

                    // Lakukan pengecekan konflik
                    $this->evaluasiKandidat(
                        $hari,
                        $jamMulai,
                        $jamSelesai,
                        $jamSelesaiTransisi,
                        $item,
                        $tracker,
                        $candidates,
                        $failureCodes,
                        $alasanTerakhir
                    );
                }
            }
        }

        // ================================================================
        // 3. RETURN HASIL
        // ================================================================
        if (!empty($candidates)) {
            return [
                'status' => 'success',
                'candidates' => $candidates,
                'failure_code' => null,
                'reason' => null,
            ];
        }

        $failureCodes = array_values(array_unique(array_filter($failureCodes)));
        $criticalCodes = ['FIXED_ROOM_NOT_FOUND', 'FIXED_ROOM_CAPACITY', 'FIXED_ROOM_TYPE', 'FIXED_ROOM_PRODI'];

        if (!empty(array_intersect($failureCodes, $criticalCodes))) {
            return [
                'status' => 'critical_conflict',
                'candidates' => [],
                'failure_code' => $this->firstMatchingCode($failureCodes, $criticalCodes),
                'reason' => $alasanTerakhir ?? 'Constraint ruang tidak dapat dipenuhi.',
            ];
        }

        return [
            'status' => 'needs_adjustment',
            'candidates' => [],
            'failure_code' => $failureCodes[0] ?? 'NO_FEASIBLE_SLOT',
            'reason' => $alasanTerakhir ?? 'Tidak ditemukan kombinasi hari, jam, dan ruang yang valid.',
        ];
    }

    /**
     * FUNGSI HELPER: Mengevaluasi apakah kombinasi waktu ini valid untuk kandidat.
     */
    private function evaluasiKandidat(
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        string $jamSelesaiTransisi,
        DemandItem $item,
        ScheduleTracker $tracker,
        array &$candidates,
        array &$failureCodes,
        ?string &$alasanTerakhir
    ): void {

        // Cek Istirahat
        if ($this->nabrakIstirahat($hari, $jamMulai, $jamSelesai)) {
            $failureCodes[] = 'JAM_ISTIRAHAT';
            $alasanTerakhir = 'Waktu kuliah melewati jam istirahat.';
            return;
        }

        // Cek Bentrok Kelas
        if ($tracker->isKelasBentrok($item->kelasId, $hari, $jamMulai, $jamSelesaiTransisi)) {
            $failureCodes[] = 'KELAS_BENTROK';
            $alasanTerakhir = 'Kelas sudah punya jadwal lain yang bentrok waktu.';
            return;
        }

        // Cek Dosen Bentrok / Availability
        $dosenBentrok = $this->cekDosenBentrokAtauDiluarAvailability($item->dosenIds, $hari, $jamMulai, $jamSelesaiTransisi, $tracker);
        if ($dosenBentrok) {
            $failureCodes[] = $this->deteksiKodeDosenFailure($dosenBentrok);
            $alasanTerakhir = $dosenBentrok;
            return;
        }

        // Cari Ruang
        $hasilRuang = $this->cariRuangValid($item, $hari, $jamMulai, $jamSelesaiTransisi, $tracker);
        foreach ($hasilRuang['ruang'] as $ruang) {
            $candidates[] = new Candidate(
                hari: $hari,
                jamMulai: $jamMulai,
                jamSelesai: $jamSelesai,
                ruangId: (int) $ruang['id'],
                kapasitasRuang: (int) $ruang['kapasitas'],
                roomSource: $hasilRuang['room_source'] ?? 'normal',
                roomNote: $hasilRuang['room_note'] ?? null,
            );
        }

        if ($hasilRuang['reason']) {
            $failureCodes[] = $hasilRuang['failure_code'];
            $alasanTerakhir = $hasilRuang['reason'];
        }
    }

    protected function hitungJamSelesai(string $jamMulai, int $sks, string $jamTutupKampus): array
    {
        $durasiMenit = $sks * $this->menitPerSks;
        $jamSelesai = Carbon::parse($jamMulai)->addMinutes($durasiMenit)->format('H:i');
        $jamSelesaiTransisi = Carbon::parse($jamSelesai)->addMinutes($this->menitTransisi)->format('H:i');
        return [$jamSelesai, $jamSelesaiTransisi, $jamSelesai <= $jamTutupKampus];
    }

    protected function nabrakIstirahat(string $hari, string $jamMulai, string $jamSelesai): bool
    {
        foreach ($this->jamIstirahat as $istirahat) {
            $hariBerlaku = $istirahat['hari'] ?? [];
            if (!in_array($hari, $hariBerlaku)) continue;

            if ($jamMulai < $istirahat['selesai'] && $jamSelesai > $istirahat['mulai']) {
                return true;
            }
        }
        return false;
    }

    protected function cekDosenBentrokAtauDiluarAvailability(array $dosenIds, string $hari, string $jamMulai, string $jamSelesaiTransisi, ScheduleTracker $tracker): ?string
    {
        foreach ($dosenIds as $dId) {
            if ($tracker->isDosenBentrok($dId, $hari, $jamMulai, $jamSelesaiTransisi)) {
                return 'Salah satu dosen pengampu bentrok jadwal (termasuk jeda kelas).';
            }
            if (isset($this->limitasiWaktuDosen[$dId])) {
                $isAvailable = false;
                foreach ($this->limitasiWaktuDosen[$dId][$hari] ?? [] as $whitelist) {
                    if ($jamMulai >= $whitelist['mulai'] && $jamSelesaiTransisi <= $whitelist['selesai']) {
                        $isAvailable = true;
                        break;
                    }
                }
                if (!$isAvailable) return 'Salah satu dosen di luar jam ketersediaan yang didefinisikan.';
            }
        }
        return null;
    }

    protected function cariRuangValid(DemandItem $item, string $hari, string $jamMulai, string $jamSelesaiTransisi, ScheduleTracker $tracker): array
    {
        if ($item->reqRuangId) {
            $ruangPilihan = collect($this->ruangTersedia)->firstWhere('id', $item->reqRuangId);
            if (!$ruangPilihan) return ['ruang' => [], 'failure_code' => 'FIXED_ROOM_NOT_FOUND', 'reason' => "CRITICAL: Ruang pilihan admin id={$item->reqRuangId} tidak aktif atau bukan bagian dari kampus ini."];
            if (!is_null($ruangPilihan['prodi_id']) && (int) $ruangPilihan['prodi_id'] !== (int) $item->kelasProdiId) return ['ruang' => [], 'failure_code' => 'FIXED_ROOM_PRODI', 'reason' => "CRITICAL: Ruang pilihan admin '{$ruangPilihan['nama_ruang']}' merupakan ruang eksklusif prodi lain."];
            if ((int) $ruangPilihan['kapasitas'] < (int) $item->kapasitasDibutuhkan) return ['ruang' => [], 'failure_code' => 'FIXED_ROOM_CAPACITY', 'reason' => "CRITICAL: Ruang pilihan admin '{$ruangPilihan['nama_ruang']}' berkapasitas {$ruangPilihan['kapasitas']}, butuh {$item->kapasitasDibutuhkan}."];
            if ($ruangPilihan['jenis_ruang'] !== $item->jenisRuangDibutuhkan) return ['ruang' => [], 'failure_code' => 'FIXED_ROOM_TYPE', 'reason' => "CRITICAL: Ruang pilihan admin '{$ruangPilihan['nama_ruang']}' berjenis {$ruangPilihan['jenis_ruang']}, butuh {$item->jenisRuangDibutuhkan}."];

            if (!$tracker->isRuangBentrok($ruangPilihan['id'], $hari, $jamMulai, $jamSelesaiTransisi)) {
                return ['ruang' => [$ruangPilihan], 'failure_code' => null, 'reason' => null];
            }

            $kandidatAlternatif = [];
            foreach ($this->ruangTersedia as $ruang) {
                if ((int) $ruang['id'] === (int) $item->reqRuangId) continue;
                if ($ruang['jenis_ruang'] !== $item->jenisRuangDibutuhkan) continue;
                if ((int) $ruang['kapasitas'] < (int) $item->kapasitasDibutuhkan) continue;
                if (!is_null($ruang['prodi_id']) && (int) $ruang['prodi_id'] !== (int) $item->kelasProdiId) continue;
                if ($tracker->isRuangBentrok($ruang['id'], $hari, $jamMulai, $jamSelesaiTransisi)) continue;
                $kandidatAlternatif[] = $ruang;
            }

            if (!empty($kandidatAlternatif)) {
                return ['ruang' => $kandidatAlternatif, 'failure_code' => null, 'reason' => null, 'room_source' => 'preferred_fallback', 'room_note' => "Ruang pilihan admin '{$ruangPilihan['nama_ruang']}' penuh. Sistem menggunakan alternatif."];
            }
            return ['ruang' => [], 'failure_code' => 'ROOM_UNAVAILABLE', 'reason' => "Ruang pilihan '{$ruangPilihan['nama_ruang']}' penuh dan tidak ada alternatif pada slot {$hari} {$jamMulai}."];
        }

        $kandidatRuang = [];
        foreach ($this->ruangTersedia as $ruang) {
            if ($ruang['jenis_ruang'] !== $item->jenisRuangDibutuhkan) continue;
            if ((int) $ruang['kapasitas'] < (int) $item->kapasitasDibutuhkan) continue;
            if (!is_null($ruang['prodi_id']) && (int) $ruang['prodi_id'] !== (int) $item->kelasProdiId) continue;
            if ($tracker->isRuangBentrok($ruang['id'], $hari, $jamMulai, $jamSelesaiTransisi)) continue;
            $kandidatRuang[] = $ruang;
        }

        if (empty($kandidatRuang)) return ['ruang' => [], 'failure_code' => 'ROOM_UNAVAILABLE', 'reason' => 'Semua ruang yang sesuai terpakai pada slot ini.'];
        return ['ruang' => $kandidatRuang, 'failure_code' => null, 'reason' => null];
    }

    protected function deteksiKodeDosenFailure(string $reason): string
    {
        if (str_contains($reason, 'bentrok jadwal')) return 'DOSEN_BENTROK';
        if (str_contains($reason, 'di luar jam ketersediaan')) return 'DOSEN_AVAILABILITY';
        return 'DOSEN_CONSTRAINT';
    }

    protected function firstMatchingCode(array $failureCodes, array $targetCodes): ?string
    {
        foreach ($failureCodes as $code) {
            if (in_array($code, $targetCodes, true)) return $code;
        }
        return null;
    }
}
