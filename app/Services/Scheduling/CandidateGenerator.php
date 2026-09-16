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
        protected array $ketersediaanDosenKhusus = [],
        protected ?int $kampusUtamaId = null,
    ) {}

    public function generate(DemandItem $item, ScheduleTracker $tracker): array
    {
        $candidates = [];
        $failureCodes = [];
        $alasanTerakhir = null;

        foreach ($this->hariOperasional as $hari) {

            // ============================================================
            // 1. KANDIDAT NORMAL SESUAI WIZARD
            // ============================================================
            if ($this->modeWaktu === 'statis') {

                $slotsHariIni = $this->jamOperasional[$hari]['slots'] ?? [];

                foreach ($slotsHariIni as $slot) {

                    $jamMulai = $slot['mulai'];
                    $jamSelesai = $slot['selesai'];

                    $jamSelesaiTransisi = Carbon::parse($jamSelesai)
                        ->addMinutes($this->menitTransisi)
                        ->format('H:i');

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

                $jamBuka = $this->jamOperasional[$hari]['mulai'] ?? '08:00';
                $jamTutup = $this->jamOperasional[$hari]['selesai'] ?? '16:00';

                $waktuSekarang = Carbon::parse($jamBuka);
                $waktuTutupObj = Carbon::parse($jamTutup);

                while ($waktuSekarang->lessThan($waktuTutupObj)) {

                    $jamMulai = $waktuSekarang->format('H:i');

                    [
                        $jamSelesai,
                        $jamSelesaiTransisi,
                        $lanjut
                    ] = $this->hitungJamSelesai(
                        $jamMulai,
                        $item->sksTotal,
                        $jamTutup
                    );

                    if (!$lanjut) {

                        if (
                            empty($failureCodes)
                            || end($failureCodes) !== 'MELEBIHI_JAM_KAMPUS'
                        ) {
                            $failureCodes[] = 'MELEBIHI_JAM_KAMPUS';

                            $alasanTerakhir =
                                'Durasi mata kuliah melebihi jam operasional kampus.';
                        }

                        break;
                    }

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

                    $waktuSekarang->addMinutes(15);
                }
            }

            // ============================================================
            // 2. KANDIDAT KHUSUS DOSEN DI LUAR JAM OPERASIONAL
            // ============================================================
            $this->generateKandidatDosenKhusus(
                $hari,
                $item,
                $tracker,
                $candidates,
                $failureCodes,
                $alasanTerakhir
            );
        }

        // ================================================================
        // RETURN HASIL
        // ================================================================
        if (!empty($candidates)) {
            return [
                'status' => 'success',
                'candidates' => $candidates,
                'failure_code' => null,
                'reason' => null,
            ];
        }

        $failureCodes = array_values(
            array_unique(
                array_filter($failureCodes)
            )
        );

        $criticalCodes = [
            'FIXED_ROOM_NOT_FOUND',
            'FIXED_ROOM_CAPACITY',
            'FIXED_ROOM_TYPE',
            'FIXED_ROOM_PRODI',
        ];

        if (!empty(array_intersect($failureCodes, $criticalCodes))) {
            return [
                'status' => 'critical_conflict',
                'candidates' => [],
                'failure_code' => $this->firstMatchingCode(
                    $failureCodes,
                    $criticalCodes
                ),
                'reason' =>
                $alasanTerakhir
                    ?? 'Constraint ruang tidak dapat dipenuhi.',
            ];
        }

        return [
            'status' => 'needs_adjustment',
            'candidates' => [],
            'failure_code' => $failureCodes[0] ?? 'NO_FEASIBLE_SLOT',
            'reason' =>
            $alasanTerakhir
                ?? 'Tidak ditemukan kombinasi hari, jam, dan ruang yang valid.',
        ];
    }
    protected function generateKandidatDosenKhusus(
        string $hari,
        DemandItem $item,
        ScheduleTracker $tracker,
        array &$candidates,
        array &$failureCodes,
        ?string &$alasanTerakhir
    ): void {

        /*
     * Tidak ada dosen khusus → jangan membuka jam di luar
     * operasional sama sekali.
     */
        if (empty($item->dosenIds)) {
            return;
        }

        /*
     * Ambil availability khusus dari SEMUA dosen pengampu.
     *
     * Ini penting:
     *
     * Dosen A = 18:00-20:00
     * Dosen B = 18:30-20:30
     *
     * Maka kandidat hanya boleh berada di irisan:
     *
     * 18:30-20:00
     */
        $window = $this->getIrisanAvailabilityKhusus(
            $item->dosenIds,
            $hari
        );

        if ($window === null) {
            return;
        }

        $windowMulai = $window['mulai'];
        $windowSelesai = $window['selesai'];

        /*
     * Pastikan window memang berada di luar jam operasional
     * global.
     *
     * Kalau hanya overlap dengan jam normal, jangan membuat
     * kandidat duplikat.
     */
        if (
            !$this->windowBeradaDiLuarOperasional(
                $hari,
                $windowMulai,
                $windowSelesai
            )
        ) {
            return;
        }

        // ================================================================
        // MODE STATIS
        // ================================================================
        if ($this->modeWaktu === 'statis') {

            /*
         * Gunakan durasi blok statis yang lazim pada hari tersebut.
         *
         * Contoh:
         * Wizard:
         * 08:00-09:30
         *
         * Maka special:
         * 18:00-19:30
         */
            $durasiSlot = $this->durasiSlotStatis($hari);

            if ($durasiSlot <= 0) {
                $failureCodes[] = 'SPECIAL_SLOT_DURATION_UNKNOWN';
                $alasanTerakhir =
                    'Durasi slot statis tidak dapat ditentukan untuk availability dosen khusus.';
                return;
            }

            $mulai = Carbon::parse($windowMulai);
            $batas = Carbon::parse($windowSelesai);

            while (true) {

                $selesai = $mulai->copy()->addMinutes($durasiSlot);

                if ($selesai->greaterThan($batas)) {
                    break;
                }

                $jamMulai = $mulai->format('H:i');
                $jamSelesai = $selesai->format('H:i');

                $jamSelesaiTransisi = $selesai
                    ->copy()
                    ->addMinutes($this->menitTransisi)
                    ->format('H:i');

                /*
             * Pastikan tetap benar-benar di luar jam global.
             */
                if (
                    $this->slotBeradaDiLuarOperasional(
                        $hari,
                        $jamMulai,
                        $jamSelesai
                    )
                ) {
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

                /*
             * Static slot cukup maju 15 menit agar availability
             * khusus tetap fleksibel.
             */
                $mulai->addMinutes(15);
            }

            return;
        }

        // ================================================================
        // MODE DINAMIS
        // ================================================================
        $waktuSekarang = Carbon::parse($windowMulai);
        $waktuBatas = Carbon::parse($windowSelesai);

        while ($waktuSekarang->lessThan($waktuBatas)) {

            $jamMulai = $waktuSekarang->format('H:i');

            [
                $jamSelesai,
                $jamSelesaiTransisi,
                $lanjut
            ] = $this->hitungJamSelesaiDalamWindow(
                $jamMulai,
                $item->sksTotal,
                $windowSelesai
            );

            if (!$lanjut) {
                break;
            }

            if (
                $this->slotBeradaDiLuarOperasional(
                    $hari,
                    $jamMulai,
                    $jamSelesai
                )
            ) {
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

            $waktuSekarang->addMinutes(15);
        }
    }
    protected function durasiSlotStatis(string $hari): int
    {
        $slots = $this->jamOperasional[$hari]['slots'] ?? [];

        if (empty($slots)) {
            return 0;
        }

        $slotPertama = $slots[0];

        if (
            empty($slotPertama['mulai'])
            || empty($slotPertama['selesai'])
        ) {
            return 0;
        }

        return Carbon::parse($slotPertama['mulai'])
            ->diffInMinutes(
                Carbon::parse($slotPertama['selesai'])
            );
    }
    protected function hitungJamSelesaiDalamWindow(
        string $jamMulai,
        int $sks,
        string $jamTutupWindow
    ): array {

        $durasiMenit = $sks * $this->menitPerSks;

        $jamSelesai = Carbon::parse($jamMulai)
            ->addMinutes($durasiMenit)
            ->format('H:i');

        $jamSelesaiTransisi = Carbon::parse($jamSelesai)
            ->addMinutes($this->menitTransisi)
            ->format('H:i');

        return [
            $jamSelesai,
            $jamSelesaiTransisi,
            $jamSelesai <= $jamTutupWindow,
        ];
    }
    protected function slotBeradaDiLuarOperasional(
        string $hari,
        string $mulai,
        string $selesai
    ): bool {

        $operasionalMulai =
            $this->jamOperasional[$hari]['mulai']
            ?? '08:00';

        $operasionalSelesai =
            $this->jamOperasional[$hari]['selesai']
            ?? '16:00';

        /*
     * Slot yang seluruhnya berada di jam operasional
     * tidak dianggap special.
     */
        if (
            $mulai >= $operasionalMulai
            && $selesai <= $operasionalSelesai
        ) {
            return false;
        }

        /*
     * Kita hanya ingin slot yang benar-benar berada
     * di luar jam normal.
     *
     * Contoh:
     * 15:30-17:00
     *
     * Jangan dianggap sebagai special slot.
     * Ini masih melewati jam normal dan harus diperlakukan
     * hati-hati.
     *
     * Untuk sementara kita hanya izinkan slot yang mulai
     * pada/ setelah jam tutup, atau selesai pada/sebelum
     * jam buka.
     */
        $diLuarSetelahTutup =
            $mulai >= $operasionalSelesai;

        $diLuarSebelumBuka =
            $selesai <= $operasionalMulai;

        return $diLuarSetelahTutup || $diLuarSebelumBuka;
    }
    protected function getIrisanAvailabilityKhusus(
        array $dosenIds,
        string $hari
    ): ?array {

        $intersection = null;

        foreach ($dosenIds as $dosenId) {

            $windows =
                $this->ketersediaanDosenKhusus[$dosenId][$hari]
                ?? [];

            if (empty($windows)) {
                /*
             * Dosen ini tidak memiliki special availability.
             *
             * Artinya mata kuliah tidak boleh dibuatkan
             * kandidat di luar jam operasional.
             */
                return null;
            }

            if ($intersection === null) {
                $intersection = $windows;
                continue;
            }

            $newIntersection = [];

            foreach ($intersection as $a) {
                foreach ($windows as $b) {

                    $mulai = max(
                        $a['mulai'],
                        $b['mulai']
                    );

                    $selesai = min(
                        $a['selesai'],
                        $b['selesai']
                    );

                    if ($mulai < $selesai) {
                        $newIntersection[] = [
                            'mulai' => $mulai,
                            'selesai' => $selesai,
                        ];
                    }
                }
            }

            $intersection = $newIntersection;

            if (empty($intersection)) {
                return null;
            }
        }

        if (empty($intersection)) {
            return null;
        }

        /*
     * Untuk sekarang ambil window pertama.
     *
     * Kalau seorang dosen memiliki beberapa window:
     * 18-20
     * 20-22
     *
     * keduanya tetap diproses apabila nanti kita ingin
     * mendukung multi-window secara penuh.
     */
        return $intersection[0];
    }
    protected function windowBeradaDiLuarOperasional(
        string $hari,
        string $mulai,
        string $selesai
    ): bool {

        $operasionalMulai =
            $this->jamOperasional[$hari]['mulai']
            ?? '08:00';

        $operasionalSelesai =
            $this->jamOperasional[$hari]['selesai']
            ?? '16:00';

        /*
     * Seluruh window masih berada di dalam jam operasional.
     */
        if (
            $mulai >= $operasionalMulai
            && $selesai <= $operasionalSelesai
        ) {
            return false;
        }

        return true;
    }
    /**
     * Evaluasi kombinasi hari + waktu + ruang.
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

        // ============================================================
        // CEK ISTIRAHAT
        // ============================================================
        if ($this->nabrakIstirahat(
            $hari,
            $jamMulai,
            $jamSelesai
        )) {
            $failureCodes[] = 'JAM_ISTIRAHAT';
            $alasanTerakhir =
                'Waktu kuliah melewati jam istirahat.';
            return;
        }

        // ============================================================
        // CEK BENTROK KELAS
        // ============================================================
        if ($tracker->isKelasBentrok(
            $item->kelasId,
            $hari,
            $jamMulai,
            $jamSelesaiTransisi
        )) {
            $failureCodes[] = 'KELAS_BENTROK';
            $alasanTerakhir =
                'Kelas sudah punya jadwal lain yang bentrok waktu.';
            return;
        }

        // ============================================================
        // CEK DOSEN
        // ============================================================
        $dosenBentrok =
            $this->cekDosenBentrokAtauDiluarAvailability(
                $item->dosenIds,
                $hari,
                $jamMulai,
                $jamSelesaiTransisi,
                $tracker
            );

        if ($dosenBentrok) {
            $failureCodes[] =
                $this->deteksiKodeDosenFailure($dosenBentrok);

            $alasanTerakhir = $dosenBentrok;
            return;
        }

        // ============================================================
        // CARI RUANG
        // ============================================================
        $hasilRuang = $this->cariRuangValid(
            $item,
            $hari,
            $jamMulai,
            $jamSelesaiTransisi,
            $tracker
        );

        foreach ($hasilRuang['ruang'] as $ruang) {

            // Kampus aktual = kampus tempat ruang benar-benar berada.
            $assignedKampusId = $ruang['kampus_id'] ?? null;

            // Ruang tanpa kampus tidak boleh dipakai.
            if ($assignedKampusId === null) {
                $failureCodes[] = 'ROOM_WITHOUT_CAMPUS';
                $alasanTerakhir =
                    "Ruang '{$ruang['nama_ruang']}' belum memiliki kampus.";
                continue;
            }

            $assignedKampusId = (int) $assignedKampusId;

            // ============================================================
            // KELAS HANYA BOLEH BERADA DI 1 KAMPUS PER HARI
            // ============================================================
            if (
                $tracker->isKelasBedaKampusDiHari(
                    $item->kelasId,
                    $hari,
                    $assignedKampusId
                )
            ) {
                $failureCodes[] = 'CLASS_CROSS_CAMPUS_SAME_DAY';
                $alasanTerakhir =
                    'Kelas sudah memiliki jadwal di kampus lain pada hari yang sama.';
                continue;
            }

            // ============================================================
            // DOSEN HANYA BOLEH BERADA DI 1 KAMPUS PER HARI
            // ============================================================
            $dosenBedaKampus = false;

            foreach ($item->dosenIds as $dosenId) {
                if (
                    $tracker->isDosenBedaKampusDiHari(
                        $dosenId,
                        $hari,
                        $assignedKampusId
                    )
                ) {
                    $dosenBedaKampus = true;
                    break;
                }
            }

            if ($dosenBedaKampus) {
                $failureCodes[] = 'DOSEN_CROSS_CAMPUS_SAME_DAY';
                $alasanTerakhir =
                    'Salah satu dosen sudah memiliki jadwal di kampus lain pada hari yang sama.';
                continue;
            }

            $candidates[] = new Candidate(
                hari: $hari,
                jamMulai: $jamMulai,
                jamSelesai: $jamSelesai,
                ruangId: (int) $ruang['id'],
                kapasitasRuang: (int) $ruang['kapasitas'],
                roomSource: $hasilRuang['room_source'] ?? 'normal',
                roomNote: $hasilRuang['room_note'] ?? null,
                assignedKampusId: $assignedKampusId,
            );
        }

        if (!empty($hasilRuang['reason'])) {
            $failureCodes[] = $hasilRuang['failure_code'];
            $alasanTerakhir = $hasilRuang['reason'];
        }
    }

    protected function hitungJamSelesai(
        string $jamMulai,
        int $sks,
        string $jamTutupKampus
    ): array {

        $durasiMenit = $sks * $this->menitPerSks;

        $jamSelesai = Carbon::parse($jamMulai)
            ->addMinutes($durasiMenit)
            ->format('H:i');

        $jamSelesaiTransisi = Carbon::parse($jamSelesai)
            ->addMinutes($this->menitTransisi)
            ->format('H:i');

        return [
            $jamSelesai,
            $jamSelesaiTransisi,
            $jamSelesai <= $jamTutupKampus,
        ];
    }

    protected function nabrakIstirahat(
        string $hari,
        string $jamMulai,
        string $jamSelesai
    ): bool {

        foreach ($this->jamIstirahat as $istirahat) {

            $hariBerlaku = $istirahat['hari'] ?? [];

            if (!in_array($hari, $hariBerlaku)) {
                continue;
            }

            if (
                $jamMulai < $istirahat['selesai']
                && $jamSelesai > $istirahat['mulai']
            ) {
                return true;
            }
        }

        return false;
    }

    protected function cekDosenBentrokAtauDiluarAvailability(
        array $dosenIds,
        string $hari,
        string $jamMulai,
        string $jamSelesaiTransisi,
        ScheduleTracker $tracker
    ): ?string {

        foreach ($dosenIds as $dId) {

            if (
                $tracker->isDosenBentrok(
                    $dId,
                    $hari,
                    $jamMulai,
                    $jamSelesaiTransisi
                )
            ) {
                return 'Salah satu dosen pengampu bentrok jadwal (termasuk jeda kelas).';
            }

            if (isset($this->limitasiWaktuDosen[$dId])) {

                $isAvailable = false;

                foreach (
                    $this->limitasiWaktuDosen[$dId][$hari] ?? []
                    as $whitelist
                ) {

                    if (
                        $jamMulai >= $whitelist['mulai']
                        && $jamSelesaiTransisi <= $whitelist['selesai']
                    ) {
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
     * ================================================================
     * ATURAN RUANG
     * ================================================================
     *
     * 1. Jika admin memilih LAB:
     *    - fixed room
     *    - tidak boleh fallback
     *
     * 2. Jika admin memilih TEORI:
     *    - preferred room
     *    - boleh fallback ke TEORI lain
     *    - harus kampus yang sama
     *    - ruang umum (prodi_id NULL) boleh lintas prodi
     *    - ruang teori khusus prodi hanya untuk prodi tersebut
     *
     * 3. Jika tidak ada pilihan admin:
     *    - TEORI:
     *        ruang umum atau ruang milik prodi sendiri
     *    - LAB:
     *        ruang umum atau ruang milik prodi sendiri
     */
    protected function cariRuangValid(
        DemandItem $item,
        string $hari,
        string $jamMulai,
        string $jamSelesaiTransisi,
        ScheduleTracker $tracker
    ): array {

        // ============================================================
        // ADMIN MEMILIH RUANG
        // ============================================================
        if ($item->reqRuangId) {

            $ruangPilihan = collect($this->ruangTersedia)
                ->firstWhere('id', $item->reqRuangId);

            // --------------------------------------------------------
            // RUANG TIDAK DITEMUKAN
            // --------------------------------------------------------
            if (!$ruangPilihan) {
                return [
                    'ruang' => [],
                    'failure_code' => 'FIXED_ROOM_NOT_FOUND',
                    'reason' =>
                    "CRITICAL: Ruang pilihan admin "
                        . "id={$item->reqRuangId} tidak aktif "
                        . "atau tidak tersedia dalam konteks generator.",
                ];
            }

            // --------------------------------------------------------
            // VALIDASI PRODI
            //
            // Ruang khusus prodi hanya boleh dipakai prodi tersebut.
            //
            // Ruang prodi_id NULL = ruang umum.
            // --------------------------------------------------------
            if (
                !is_null($ruangPilihan['prodi_id'])
                && (int) $ruangPilihan['prodi_id']
                !== (int) $item->kelasProdiId
            ) {
                return [
                    'ruang' => [],
                    'failure_code' => 'FIXED_ROOM_PRODI',
                    'reason' =>
                    "CRITICAL: Ruang pilihan admin "
                        . "'{$ruangPilihan['nama_ruang']}' "
                        . "merupakan ruang eksklusif prodi lain.",
                ];
            }

            // --------------------------------------------------------
            // VALIDASI KAPASITAS
            // --------------------------------------------------------
            if (
                (int) $ruangPilihan['kapasitas']
                < (int) $item->kapasitasDibutuhkan
            ) {
                return [
                    'ruang' => [],
                    'failure_code' => 'FIXED_ROOM_CAPACITY',
                    'reason' =>
                    "CRITICAL: Ruang pilihan admin "
                        . "'{$ruangPilihan['nama_ruang']}' "
                        . "berkapasitas {$ruangPilihan['kapasitas']}, "
                        . "butuh {$item->kapasitasDibutuhkan}.",
                ];
            }

            // --------------------------------------------------------
            // VALIDASI JENIS
            // --------------------------------------------------------
            if (
                $ruangPilihan['jenis_ruang']
                !== $item->jenisRuangDibutuhkan
            ) {
                return [
                    'ruang' => [],
                    'failure_code' => 'FIXED_ROOM_TYPE',
                    'reason' =>
                    "CRITICAL: Ruang pilihan admin "
                        . "'{$ruangPilihan['nama_ruang']}' "
                        . "berjenis {$ruangPilihan['jenis_ruang']}, "
                        . "butuh {$item->jenisRuangDibutuhkan}.",
                ];
            }

            // ========================================================
            // LABORATORIUM = FIXED HARD CONSTRAINT
            // ========================================================
            if (
                $ruangPilihan['jenis_ruang']
                === 'LABORATORIUM'
            ) {

                if (
                    $tracker->isRuangBentrok(
                        $ruangPilihan['id'],
                        $hari,
                        $jamMulai,
                        $jamSelesaiTransisi
                    )
                ) {
                    return [
                        'ruang' => [],
                        'failure_code' => 'FIXED_LAB_UNAVAILABLE',
                        'reason' =>
                        "Ruang LAB pilihan admin "
                            . "'{$ruangPilihan['nama_ruang']}' "
                            . "sedang terpakai pada slot {$hari} "
                            . "{$jamMulai}-{$jamSelesaiTransisi}. "
                            . "LAB pilihan admin tidak boleh diganti.",
                        'room_source' => 'fixed',
                        'room_note' =>
                        'LAB pilihan admin wajib digunakan. '
                            . 'Tidak ada fallback ke laboratorium lain.',
                    ];
                }

                return [
                    'ruang' => [$ruangPilihan],
                    'failure_code' => null,
                    'reason' => null,
                    'room_source' => 'fixed',
                    'room_note' => null,
                ];
            }

            // // --------------------------------------------------------
            // // VALIDASI KAMPUS
            // //
            // // TEORI:
            // //   wajib kampus yang sama dengan kelas.
            // //
            // // LAB:
            // //   boleh lintas kampus sesuai aturan fallback.
            // //
            // // Hanya TEORI yang wajib same-campus di level fixed room.
            // // --------------------------------------------------------
            // if (
            //     $ruangPilihan['jenis_ruang'] === 'TEORI'
            //     && (
            //         is_null($ruangPilihan['kampus_id'])
            //         || (int) $ruangPilihan['kampus_id']
            //         !== (int) $item->kelasKampusId
            //     )
            // ) {
            //     return [
            //         'ruang' => [],
            //         'failure_code' => 'FIXED_ROOM_CAMPUS',
            //         'reason' =>
            //         "CRITICAL: Ruang TEORI pilihan admin "
            //             . "'{$ruangPilihan['nama_ruang']}' "
            //             . "berada di kampus berbeda dengan kelas.",
            //     ];
            // }

            // ========================================================
            // TEORI = PREFERRED + FALLBACK
            // ========================================================

            // Ruang pilihan masih tersedia
            if (
                !$tracker->isRuangBentrok(
                    $ruangPilihan['id'],
                    $hari,
                    $jamMulai,
                    $jamSelesaiTransisi
                )
            ) {
                return [
                    'ruang' => [$ruangPilihan],
                    'failure_code' => null,
                    'reason' => null,
                    'room_source' => 'preferred',
                    'room_note' => null,
                ];
            }

            // --------------------------------------------------------
            // RUANG TEORI PILIHAN PENUH
            // CARI ALTERNATIF TEORI
            // --------------------------------------------------------
            $kandidatAlternatif = [];

            foreach ($this->ruangTersedia as $ruang) {

                if (
                    (int) $ruang['id']
                    === (int) $item->reqRuangId
                ) {
                    continue;
                }

                // Hanya TEORI
                if ($ruang['jenis_ruang'] !== 'TEORI') {
                    continue;
                }

                // Kapasitas
                if (
                    (int) $ruang['kapasitas']
                    < (int) $item->kapasitasDibutuhkan
                ) {
                    continue;
                }

                // ----------------------------------------------------
                // KAMPUS HARUS SAMA
                // ----------------------------------------------------
                if (
                    is_null($ruang['kampus_id'])
                    || is_null($ruangPilihan['kampus_id'])
                    || (int) $ruang['kampus_id']
                    !== (int) $ruangPilihan['kampus_id']
                ) {
                    continue;
                }

                // ----------------------------------------------------
                // RUANG TEORI KHUSUS PRODI
                // hanya boleh untuk prodi tersebut.
                //
                // NULL = ruang umum = boleh lintas prodi.
                // ----------------------------------------------------
                if (
                    !is_null($ruang['prodi_id'])
                    && (int) $ruang['prodi_id']
                    !== (int) $item->kelasProdiId
                ) {
                    continue;
                }

                // Bentrok
                if (
                    $tracker->isRuangBentrok(
                        $ruang['id'],
                        $hari,
                        $jamMulai,
                        $jamSelesaiTransisi
                    )
                ) {
                    continue;
                }

                $kandidatAlternatif[] = $ruang;
            }

            if (!empty($kandidatAlternatif)) {
                return [
                    'ruang' => $kandidatAlternatif,
                    'failure_code' => null,
                    'reason' => null,
                    'room_source' => 'preferred_fallback',
                    'room_note' =>
                    "Ruang pilihan admin "
                        . "'{$ruangPilihan['nama_ruang']}' penuh. "
                        . "Sistem menggunakan alternatif ruang teori "
                        . "yang valid pada kampus yang sama.",
                ];
            }

            return [
                'ruang' => [],
                'failure_code' => 'ROOM_UNAVAILABLE',
                'reason' =>
                "Ruang pilihan '{$ruangPilihan['nama_ruang']}' "
                    . "penuh dan tidak ada alternatif ruang teori "
                    . "yang valid pada kampus yang sama.",
            ];
        }

        $kampusKelas = (int) $item->assignedKampusId;

        /**
         * Filter ruang berdasarkan kampus + seluruh constraint ruang.
         */
        $filterRuang = function (int $kampusTarget) use (
            $item,
            $hari,
            $jamMulai,
            $jamSelesaiTransisi,
            $tracker
        ): array {
            $hasil = [];

            foreach ($this->ruangTersedia as $ruang) {

                // --------------------------------------------------------
                // JENIS RUANG
                // --------------------------------------------------------
                if (
                    $ruang['jenis_ruang']
                    !== $item->jenisRuangDibutuhkan
                ) {
                    continue;
                }

                // --------------------------------------------------------
                // KAMPUS
                // --------------------------------------------------------
                if (
                    is_null($ruang['kampus_id'])
                    || (int) $ruang['kampus_id'] !== $kampusTarget
                ) {
                    continue;
                }

                // --------------------------------------------------------
                // KAPASITAS
                // --------------------------------------------------------
                if (
                    (int) $ruang['kapasitas']
                    < (int) $item->kapasitasDibutuhkan
                ) {
                    continue;
                }

                // --------------------------------------------------------
                // RUANG KHUSUS PRODI
                //
                // NULL = ruang umum
                // terisi = hanya prodi tersebut
                // --------------------------------------------------------
                if (
                    !is_null($ruang['prodi_id'])
                    && (int) $ruang['prodi_id']
                    !== (int) $item->kelasProdiId
                ) {
                    continue;
                }

                // --------------------------------------------------------
                // BENTROK RUANG
                // --------------------------------------------------------
                if (
                    $tracker->isRuangBentrok(
                        $ruang['id'],
                        $hari,
                        $jamMulai,
                        $jamSelesaiTransisi
                    )
                ) {
                    continue;
                }

                // --------------------------------------------------------
                // CONSTRAINT KAMPUS KELAS + DOSEN
                // --------------------------------------------------------
                if (
                    !$this->ruangMemenuhiConstraintKampus(
                        $item,
                        $hari,
                        $ruang,
                        $tracker
                    )
                ) {
                    continue;
                }

                $hasil[] = $ruang;
            }

            return $hasil;
        };


        /**
         * ================================================================
         * TEORI
         * ================================================================
         *
         * TEORI hanya boleh menggunakan kampus asal kelas.
         */
        if ($item->jenisRuangDibutuhkan === 'TEORI') {

            $kandidatRuang = $filterRuang($kampusKelas);

            if (empty($kandidatRuang)) {
                return [
                    'ruang' => [],
                    'failure_code' => 'ROOM_UNAVAILABLE',
                    'reason' =>
                    'Semua ruang TEORI yang sesuai pada kampus kelas '
                        . 'terpakai atau tidak memenuhi constraint.',
                ];
            }

            return [
                'ruang' => $kandidatRuang,
                'failure_code' => null,
                'reason' => null,
                'room_source' => 'normal',
                'room_note' => null,
            ];
        }


        /**
         * ================================================================
         * LABORATORIUM
         * ================================================================
         *
         * 1. Cari LAB kampus asal terlebih dahulu.
         * 2. Jika tidak ada yang usable → fallback ke kampus utama.
         * 3. Hanya LAB yang boleh melakukan fallback lintas kampus.
         */

        // ---------------------------------------------------------------
        // PRIORITAS 1: LAB KAMPUS ASAL
        // ---------------------------------------------------------------
        $kandidatLokal = $filterRuang($kampusKelas);

        if (!empty($kandidatLokal)) {
            return [
                'ruang' => $kandidatLokal,
                'failure_code' => null,
                'reason' => null,
                'room_source' => 'normal',
                'room_note' => null,
            ];
        }


        // ---------------------------------------------------------------
        // PRIORITAS 2: LAB KAMPUS UTAMA
        // ---------------------------------------------------------------
        if (
            $this->kampusUtamaId !== null
            && (int) $this->kampusUtamaId !== $kampusKelas
        ) {
            $kandidatFallback = $filterRuang(
                (int) $this->kampusUtamaId
            );

            if (!empty($kandidatFallback)) {
                return [
                    'ruang' => $kandidatFallback,
                    'failure_code' => null,
                    'reason' => null,
                    'room_source' => 'lab_fallback',
                    'room_note' =>
                    'LAB kampus asal tidak tersedia pada slot ini. '
                        . 'Sistem menggunakan LAB kampus utama.',
                ];
            }
        }


        // ---------------------------------------------------------------
        // TIDAK ADA LAB
        // ---------------------------------------------------------------
        return [
            'ruang' => [],
            'failure_code' => 'ROOM_UNAVAILABLE',
            'reason' =>
            'LAB kampus asal tidak tersedia dan LAB kampus utama '
                . 'juga tidak memiliki ruang yang usable pada slot ini.',
        ];
    }

    protected function deteksiKodeDosenFailure(
        string $reason
    ): string {

        if (str_contains($reason, 'bentrok jadwal')) {
            return 'DOSEN_BENTROK';
        }

        if (str_contains($reason, 'di luar jam ketersediaan')) {
            return 'DOSEN_AVAILABILITY';
        }

        return 'DOSEN_CONSTRAINT';
    }

    protected function firstMatchingCode(
        array $failureCodes,
        array $targetCodes
    ): ?string {

        foreach ($failureCodes as $code) {
            if (in_array($code, $targetCodes, true)) {
                return $code;
            }
        }

        return null;
    }

    protected function ruangMemenuhiConstraintKampus(
        DemandItem $item,
        string $hari,
        array $ruang,
        ScheduleTracker $tracker
    ): bool {
        $assignedKampusId = $ruang['kampus_id'] ?? null;

        if ($assignedKampusId === null) {
            return false;
        }

        $assignedKampusId = (int) $assignedKampusId;

        // Kelas tidak boleh pindah kampus pada hari yang sama.
        if (
            $tracker->isKelasBedaKampusDiHari(
                $item->kelasId,
                $hari,
                $assignedKampusId
            )
        ) {
            return false;
        }

        // Dosen tidak boleh pindah kampus pada hari yang sama.
        foreach ($item->dosenIds as $dosenId) {
            if (
                $tracker->isDosenBedaKampusDiHari(
                    $dosenId,
                    $hari,
                    $assignedKampusId
                )
            ) {
                return false;
            }
        }

        return true;
    }
}
