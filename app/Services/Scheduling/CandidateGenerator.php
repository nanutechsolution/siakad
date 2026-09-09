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
        protected array $slotWaktu,
        protected array $jamIstirahat,
        protected string $modeWaktu,
        protected int $menitPerSks,
        protected array $ruangTersedia,
        protected array $limitasiWaktuDosen,
    ) {
    }

    /**
     * @return array{candidates: Candidate[], reason: ?string}
     * reason diisi hanya kalau candidates kosong, untuk keperluan pesan gagal yang jelas.
     */
    public function generate(DemandItem $item, ScheduleTracker $tracker): array
    {
        $candidates = [];
        $jamTutupKampus = $this->slotWaktu[count($this->slotWaktu) - 1]['selesai'];
        $alasanTerakhir = null;

        foreach ($this->hariOperasional as $hari) {
            foreach ($item->dosenIds as $dId) {
                if ($tracker->isDosenKarantinaDiHari($dId, $hari)) {
                    $alasanTerakhir = 'Dosen sedang mengajar di kampus lain pada hari ini.';
                    continue 2;
                }
            }

            foreach ($this->slotWaktu as $slot) {
                $jamMulai = $slot['mulai'];
                [$jamSelesai, $lanjut] = $this->hitungJamSelesai($jamMulai, $item->sksTotal, $slot, $jamTutupKampus);
                if (!$lanjut) {
                    continue;
                }

                if ($this->nabrakIstirahat($jamMulai, $jamSelesai)) {
                    continue;
                }

                if ($tracker->isKelasBentrok($item->kelasId, $hari, $jamMulai, $jamSelesai)) {
                    $alasanTerakhir = 'Kelas sudah punya jadwal lain yang bentrok waktu.';
                    continue;
                }

                $dosenBentrok = $this->cekDosenBentrokAtauDiluarAvailability($item->dosenIds, $hari, $jamMulai, $jamSelesai, $tracker);
                if ($dosenBentrok) {
                    $alasanTerakhir = $dosenBentrok;
                    continue;
                }

                $hasilRuang = $this->cariRuangValid($item, $hari, $jamMulai, $jamSelesai, $tracker);
                foreach ($hasilRuang['ruang'] as $ruang) {
                    $candidates[] = new Candidate($hari, $jamMulai, $jamSelesai, $ruang['id'], $ruang['kapasitas']);
                }
                if ($hasilRuang['reason']) {
                    $alasanTerakhir = $hasilRuang['reason'];
                }
            }
        }

        return [
            'candidates' => $candidates,
            'reason' => empty($candidates) ? ($alasanTerakhir ?? 'Tidak ditemukan kombinasi hari/jam/ruang yang valid.') : null,
        ];
    }

    protected function hitungJamSelesai(string $jamMulai, int $sks, array $slot, string $jamTutupKampus): array
    {
        if ($this->modeWaktu === 'statis') {
            return [$slot['selesai'], true];
        }
        $durasiMenit = $sks * $this->menitPerSks;
        $jamSelesai = Carbon::parse($jamMulai)->addMinutes($durasiMenit)->format('H:i');
        return [$jamSelesai, $jamSelesai <= $jamTutupKampus];
    }

    protected function nabrakIstirahat(string $jamMulai, string $jamSelesai): bool
    {
        foreach ($this->jamIstirahat as $istirahat) {
            if ($jamMulai < $istirahat['selesai'] && $jamSelesai > $istirahat['mulai']) {
                return true;
            }
        }
        return false;
    }

    protected function cekDosenBentrokAtauDiluarAvailability(array $dosenIds, string $hari, string $jamMulai, string $jamSelesai, ScheduleTracker $tracker): ?string
    {
        foreach ($dosenIds as $dId) {
            if ($tracker->isDosenBentrok($dId, $hari, $jamMulai, $jamSelesai)) {
                return 'Salah satu dosen pengampu bentrok jadwal.';
            }

            if (isset($this->limitasiWaktuDosen[$dId])) {
                $isAvailable = false;
                foreach ($this->limitasiWaktuDosen[$dId][$hari] ?? [] as $whitelist) {
                    if ($jamMulai >= $whitelist['mulai'] && $jamSelesai <= $whitelist['selesai']) {
                        $isAvailable = true;
                        break;
                    }
                }
                if (!$isAvailable) {
                    return 'Salah satu dosen di luar jam ketersediaan yang didefinisikan.';
                }
            }
        }
        return null;
    }

    /**
     * @return array{ruang: array[], reason: ?string}
     */
    protected function cariRuangValid(DemandItem $item, string $hari, string $jamMulai, string $jamSelesai, ScheduleTracker $tracker): array
    {
        // --- PERBAIKAN kebijakan fixed room (dikonfirmasi poin D):
        // ruang_id dari dosen_pengampus WAJIB dipakai bila diisi, TAPI tetap
        // divalidasi penuh (aktif, kampus, kapasitas, jenis, bentrok). Kalau
        // gagal salah satu, seluruh kandidat untuk item ini gagal dengan
        // alasan jelas -- bukan lolos diam-diam seperti versi lama. ---
        if ($item->reqRuangId) {
            $ruang = collect($this->ruangTersedia)->firstWhere('id', $item->reqRuangId);

            if (!$ruang) {
                return ['ruang' => [], 'reason' => "CRITICAL: Ruang tetap (fixed room) id={$item->reqRuangId} tidak aktif atau bukan milik kampus ini."];
            }
            if ($ruang['kapasitas'] < $item->kapasitasDibutuhkan) {
                return ['ruang' => [], 'reason' => "CRITICAL: Ruang tetap '{$ruang['nama_ruang']}' kapasitasnya {$ruang['kapasitas']}, tidak cukup untuk {$item->kapasitasDibutuhkan} mahasiswa."];
            }
            if ($ruang['jenis_ruang'] !== $item->jenisRuangDibutuhkan) {
                return ['ruang' => [], 'reason' => "CRITICAL: Ruang tetap '{$ruang['nama_ruang']}' berjenis {$ruang['jenis_ruang']}, tapi mata kuliah ini butuh {$item->jenisRuangDibutuhkan}."];
            }
            if ($tracker->isRuangBentrok($ruang['id'], $hari, $jamMulai, $jamSelesai)) {
                return ['ruang' => [], 'reason' => "Ruang tetap '{$ruang['nama_ruang']}' sedang dipakai jadwal lain pada slot ini."];
            }

            return ['ruang' => [$ruang], 'reason' => null];
        }

        $kandidatRuang = [];
        foreach ($this->ruangTersedia as $ruang) {
            if ($ruang['jenis_ruang'] !== $item->jenisRuangDibutuhkan) continue;
            if ($ruang['kapasitas'] < $item->kapasitasDibutuhkan) continue;
            if (!is_null($ruang['prodi_id']) && $ruang['prodi_id'] != $item->kelasProdiId) continue;
            if ($tracker->isRuangBentrok($ruang['id'], $hari, $jamMulai, $jamSelesai)) continue;

            $kandidatRuang[] = $ruang;
        }

        return [
            'ruang' => $kandidatRuang,
            'reason' => empty($kandidatRuang) ? 'Semua ruang yang sesuai jenis & kapasitas sedang terpakai pada slot ini.' : null,
        ];
    }
}
