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
    ) {}

    /**
     * @return array{
     *     candidates: Candidate[],
     *     status: string,
     *     failure_code: ?string,
     *     reason: ?string
     * }
     */
    public function generate(DemandItem $item, ScheduleTracker $tracker): array
    {
        $candidates = [];

        $jamTutupKampus = $this->slotWaktu[count($this->slotWaktu) - 1]['selesai'];

        $failureCodes = [];
        $alasanTerakhir = null;

        foreach ($this->hariOperasional as $hari) {

            /*
             * ============================================================
             * 1. CEK KARANTINA DOSEN / KAMPUS LAIN
             * ============================================================
             */
            foreach ($item->dosenIds as $dId) {
                if ($tracker->isDosenKarantinaDiHari($dId, $hari)) {
                    $failureCodes[] = 'DOSEN_KARANTINA';

                    $alasanTerakhir =
                        'Dosen sedang mengajar di kampus lain pada hari ini.';

                    continue 2;
                }
            }

            /*
             * ============================================================
             * 2. LOOP SLOT WAKTU
             * ============================================================
             */
            foreach ($this->slotWaktu as $slot) {

                $jamMulai = $slot['mulai'];

                [$jamSelesai, $lanjut] = $this->hitungJamSelesai(
                    $jamMulai,
                    $item->sksTotal,
                    $slot,
                    $jamTutupKampus
                );

                if (!$lanjut) {
                    $failureCodes[] = 'MELEBIHI_JAM_KAMPUS';

                    $alasanTerakhir =
                        'Durasi mata kuliah melebihi jam operasional kampus.';

                    continue;
                }

                /*
                 * ========================================================
                 * 3. ISTIRAHAT
                 * ========================================================
                 */
                if ($this->nabrakIstirahat($jamMulai, $jamSelesai)) {
                    $failureCodes[] = 'JAM_ISTIRAHAT';

                    $alasanTerakhir =
                        'Waktu kuliah melewati jam istirahat.';

                    continue;
                }

                /*
                 * ========================================================
                 * 4. KONFLIK KELAS
                 * ========================================================
                 */
                if (
                    $tracker->isKelasBentrok(
                        $item->kelasId,
                        $hari,
                        $jamMulai,
                        $jamSelesai
                    )
                ) {
                    $failureCodes[] = 'KELAS_BENTROK';

                    $alasanTerakhir =
                        'Kelas sudah punya jadwal lain yang bentrok waktu.';

                    continue;
                }

                /*
                 * ========================================================
                 * 5. KONFLIK / AVAILABILITY DOSEN
                 * ========================================================
                 */
                $dosenBentrok =
                    $this->cekDosenBentrokAtauDiluarAvailability(
                        $item->dosenIds,
                        $hari,
                        $jamMulai,
                        $jamSelesai,
                        $tracker
                    );

                if ($dosenBentrok) {

                    $failureCodes[] = $this->deteksiKodeDosenFailure(
                        $dosenBentrok
                    );

                    $alasanTerakhir = $dosenBentrok;

                    continue;
                }

                /*
                 * ========================================================
                 * 6. CARI RUANG
                 * ========================================================
                 */
                $hasilRuang = $this->cariRuangValid(
                    $item,
                    $hari,
                    $jamMulai,
                    $jamSelesai,
                    $tracker
                );

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
        }

        /*
         * ================================================================
         * BERHASIL
         * ================================================================
         */
        if (!empty($candidates)) {
            return [
                'status' => 'success',
                'candidates' => $candidates,
                'failure_code' => null,
                'reason' => null,
            ];
        }

        /*
         * ================================================================
         * TENTUKAN STATUS KETIKA TIDAK ADA CANDIDATE
         * ================================================================
         */

        $failureCodes = array_values(
            array_unique(
                array_filter($failureCodes)
            )
        );

        /*
         * Hard constraint / fixed room tidak mungkin dipenuhi.
         *
         * Contoh:
         * - room tidak ditemukan
         * - kapasitas tidak cukup
         * - jenis room tidak sesuai
         */
        $criticalCodes = [
            'FIXED_ROOM_NOT_FOUND',
            'FIXED_ROOM_CAPACITY',
            'FIXED_ROOM_TYPE',
            'FIXED_ROOM_PRODI',
        ];

        $isCritical = !empty(array_intersect($failureCodes, $criticalCodes));

        if ($isCritical) {
            return [
                'status' => 'critical_conflict',
                'candidates' => [],
                'failure_code' => $this->firstMatchingCode(
                    $failureCodes,
                    $criticalCodes
                ),
                'reason' => $alasanTerakhir
                    ?? 'Constraint ruang yang ditentukan tidak dapat dipenuhi.',
            ];
        }

        /*
         * Semua kegagalan akibat bentrok jadwal / availability /
         * keterbatasan slot dianggap PERLU PENYESUAIAN.
         *
         * Ini berlaku baik fixed room maupun auto room.
         */
        return [
            'status' => 'needs_adjustment',
            'candidates' => [],
            'failure_code' => $failureCodes[0] ?? 'NO_FEASIBLE_SLOT',
            'reason' => $alasanTerakhir
                ?? 'Tidak ditemukan kombinasi hari, jam, dan ruang yang valid.',
        ];
    }

    /**
     * Hitung jam selesai berdasarkan mode waktu.
     */
    protected function hitungJamSelesai(
        string $jamMulai,
        int $sks,
        array $slot,
        string $jamTutupKampus
    ): array {
        if ($this->modeWaktu === 'statis') {
            return [$slot['selesai'], true];
        }

        $durasiMenit = $sks * $this->menitPerSks;

        $jamSelesai = Carbon::parse($jamMulai)
            ->addMinutes($durasiMenit)
            ->format('H:i');

        return [
            $jamSelesai,
            $jamSelesai <= $jamTutupKampus,
        ];
    }

    /**
     * Cek apakah jadwal melewati jam istirahat.
     */
    protected function nabrakIstirahat(
        string $jamMulai,
        string $jamSelesai
    ): bool {
        foreach ($this->jamIstirahat as $istirahat) {

            if (
                $jamMulai < $istirahat['selesai']
                && $jamSelesai > $istirahat['mulai']
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek bentrok dosen dan availability.
     */
    protected function cekDosenBentrokAtauDiluarAvailability(
        array $dosenIds,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        ScheduleTracker $tracker
    ): ?string {
        foreach ($dosenIds as $dId) {

            if (
                $tracker->isDosenBentrok(
                    $dId,
                    $hari,
                    $jamMulai,
                    $jamSelesai
                )
            ) {
                return 'Salah satu dosen pengampu bentrok jadwal.';
            }

            if (isset($this->limitasiWaktuDosen[$dId])) {

                $isAvailable = false;

                foreach (
                    $this->limitasiWaktuDosen[$dId][$hari] ?? []
                    as $whitelist
                ) {
                    if (
                        $jamMulai >= $whitelist['mulai']
                        && $jamSelesai <= $whitelist['selesai']
                    ) {
                        $isAvailable = true;
                        break;
                    }
                }

                if (!$isAvailable) {
                    return
                        'Salah satu dosen di luar jam ketersediaan yang didefinisikan.';
                }
            }
        }

        return null;
    }

    /**
     * Cari ruang yang valid.
     *
     * Jika admin memilih ruang:
     *   1. Ruang pilihan admin menjadi prioritas.
     *   2. Jika ruang tersebut bentrok pada slot ini,
     *      generator boleh mencari ruang alternatif.
     *   3. Jika alternatif tersedia → gunakan alternatif.
     *   4. Jika tidak ada alternatif → lanjut mencoba slot berikutnya.
     *
     * Jika admin tidak memilih ruang:
     *   Generator bebas mencari ruang sesuai constraint.
     *
     * Hard constraint tetap berlaku:
     *   - ruang tidak ditemukan
     *   - kapasitas tidak cukup
     *   - jenis ruang tidak sesuai
     *   - ruang milik prodi lain
     *
     * @return array{
     *     ruang: array[],
     *     reason: ?string,
     *     failure_code: ?string
     * }
     */
    protected function cariRuangValid(
        DemandItem $item,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        ScheduleTracker $tracker
    ): array {

        /*
     * ================================================================
     * 1. ADMIN MEMILIH RUANG
     * ================================================================
     */
        if ($item->reqRuangId) {

            $ruangPilihan = collect($this->ruangTersedia)
                ->firstWhere('id', $item->reqRuangId);

            /*
         * ------------------------------------------------------------
         * Ruang pilihan tidak ditemukan
         * ------------------------------------------------------------
         */
            if (!$ruangPilihan) {
                return [
                    'ruang' => [],
                    'failure_code' => 'FIXED_ROOM_NOT_FOUND',
                    'reason' =>
                    "CRITICAL: Ruang pilihan admin " .
                        "id={$item->reqRuangId} tidak aktif " .
                        "atau bukan bagian dari kampus ini.",
                ];
            }

            /*
         * ------------------------------------------------------------
         * Ruang milik prodi lain
         * ------------------------------------------------------------
         */
            if (
                !is_null($ruangPilihan['prodi_id'])
                && (int) $ruangPilihan['prodi_id']
                !== (int) $item->kelasProdiId
            ) {
                return [
                    'ruang' => [],
                    'failure_code' => 'FIXED_ROOM_PRODI',
                    'reason' =>
                    "CRITICAL: Ruang pilihan admin " .
                        "'{$ruangPilihan['nama_ruang']}' " .
                        "merupakan ruang eksklusif prodi lain.",
                ];
            }

            /*
         * ------------------------------------------------------------
         * Kapasitas ruang pilihan
         * ------------------------------------------------------------
         */
            if (
                (int) $ruangPilihan['kapasitas']
                < (int) $item->kapasitasDibutuhkan
            ) {
                return [
                    'ruang' => [],
                    'failure_code' => 'FIXED_ROOM_CAPACITY',
                    'reason' =>
                    "CRITICAL: Ruang pilihan admin " .
                        "'{$ruangPilihan['nama_ruang']}' " .
                        "kapasitasnya {$ruangPilihan['kapasitas']} kursi, " .
                        "sedangkan kelas membutuhkan " .
                        "{$item->kapasitasDibutuhkan} kursi.",
                ];
            }

            /*
         * ------------------------------------------------------------
         * Jenis ruang
         * ------------------------------------------------------------
         */
            if (
                $ruangPilihan['jenis_ruang']
                !== $item->jenisRuangDibutuhkan
            ) {
                return [
                    'ruang' => [],
                    'failure_code' => 'FIXED_ROOM_TYPE',
                    'reason' =>
                    "CRITICAL: Ruang pilihan admin " .
                        "'{$ruangPilihan['nama_ruang']}' " .
                        "berjenis {$ruangPilihan['jenis_ruang']}, " .
                        "sedangkan kebutuhan mata kuliah adalah " .
                        "{$item->jenisRuangDibutuhkan}.",
                ];
            }

            /*
         * ------------------------------------------------------------
         * RUANG PILIHAN ADMIN TERSEDIA
         * ------------------------------------------------------------
         */
            if (
                !$tracker->isRuangBentrok(
                    $ruangPilihan['id'],
                    $hari,
                    $jamMulai,
                    $jamSelesai
                )
            ) {
                return [
                    'ruang' => [$ruangPilihan],
                    'failure_code' => null,
                    'reason' => null,
                ];
            }

            /*
         * ============================================================
         * RUANG PILIHAN ADMIN BENTROK
         *
         * Jangan langsung gagal.
         *
         * Cari ruang alternatif yang:
         * - jenisnya sama
         * - kapasitas cukup
         * - shared ATAU milik prodi yang sama
         * - tidak bentrok
         *
         * ============================================================
         */

            $kandidatAlternatif = [];

            foreach ($this->ruangTersedia as $ruang) {

                /*
             * Jangan gunakan ruang pilihan admin lagi.
             */
                if (
                    (int) $ruang['id']
                    === (int) $item->reqRuangId
                ) {
                    continue;
                }

                /*
             * Jenis ruang harus sama.
             */
                if (
                    $ruang['jenis_ruang']
                    !== $item->jenisRuangDibutuhkan
                ) {
                    continue;
                }

                /*
             * Kapasitas harus cukup.
             */
                if (
                    (int) $ruang['kapasitas']
                    < (int) $item->kapasitasDibutuhkan
                ) {
                    continue;
                }

                /*
             * Ruang khusus prodi lain tidak boleh dipakai.
             *
             * NULL = ruang bersama.
             * prodi_id sama = boleh.
             */
                if (
                    !is_null($ruang['prodi_id'])
                    && (int) $ruang['prodi_id']
                    !== (int) $item->kelasProdiId
                ) {
                    continue;
                }

                /*
             * Ruangan sedang dipakai?
             */
                if (
                    $tracker->isRuangBentrok(
                        $ruang['id'],
                        $hari,
                        $jamMulai,
                        $jamSelesai
                    )
                ) {
                    continue;
                }

                $kandidatAlternatif[] = $ruang;
            }

            /*
         * ------------------------------------------------------------
         * Ada ruang alternatif
         * ------------------------------------------------------------
         */
            if (!empty($kandidatAlternatif)) {
                return [
                    'ruang' => $kandidatAlternatif,
                    'failure_code' => null,
                    'reason' => null,
                    'room_source' => 'preferred_fallback',
                    'room_note' =>
                    "Ruang pilihan admin '{$ruangPilihan['nama_ruang']}' " .
                        "sedang penuh pada slot ini. Sistem otomatis " .
                        "menggunakan ruang alternatif.",
                ];
            }

            /*
         * ------------------------------------------------------------
         * Tidak ada ruang alternatif pada SLOT INI.
         *
         * Jangan langsung critical.
         *
         * generate() akan lanjut ke slot berikutnya.
         * ------------------------------------------------------------
         */
            return [
                'ruang' => [],
                'failure_code' => 'ROOM_UNAVAILABLE',
                'reason' =>
                "Ruang pilihan admin '{$ruangPilihan['nama_ruang']}' " .
                    "sedang penuh dan tidak tersedia ruang alternatif " .
                    "pada slot {$hari} {$jamMulai}-{$jamSelesai}.",
            ];
        }

        /*
     * ================================================================
     * 2. AUTO ROOM
     * ================================================================
     */

        $kandidatRuang = [];

        foreach ($this->ruangTersedia as $ruang) {

            /*
         * Jenis ruang.
         */
            if (
                $ruang['jenis_ruang']
                !== $item->jenisRuangDibutuhkan
            ) {
                continue;
            }

            /*
         * Kapasitas.
         */
            if (
                (int) $ruang['kapasitas']
                < (int) $item->kapasitasDibutuhkan
            ) {
                continue;
            }

            /*
         * Ruang khusus prodi lain tidak boleh digunakan.
         */
            if (
                !is_null($ruang['prodi_id'])
                && (int) $ruang['prodi_id']
                !== (int) $item->kelasProdiId
            ) {
                continue;
            }

            /*
         * Bentrok ruang.
         */
            if (
                $tracker->isRuangBentrok(
                    $ruang['id'],
                    $hari,
                    $jamMulai,
                    $jamSelesai
                )
            ) {
                continue;
            }

            $kandidatRuang[] = $ruang;
        }

        /*
     * Tidak ada ruang otomatis.
     */
        if (empty($kandidatRuang)) {
            return [
                'ruang' => [],
                'failure_code' => 'ROOM_UNAVAILABLE',
                'reason' =>
                'Semua ruang yang sesuai jenis dan kapasitas ' .
                    'sedang terpakai pada slot ini.',
            ];
        }

        return [
            'ruang' => $kandidatRuang,
            'failure_code' => null,
            'reason' => null,
        ];
    }

    /**
     * Mapping pesan dosen -> failure code.
     */
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

    /**
     * Ambil failure code pertama dari daftar target.
     */
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
}
