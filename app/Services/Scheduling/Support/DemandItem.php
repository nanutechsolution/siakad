<?php

namespace App\Services\Scheduling\Support;

class DemandItem
{
    public function __construct(
        public int $mataKuliahId,
        public int $kelasId,
        public int $kelasProdiId,

        public array $dosenPengampuRowIds,   // id baris dosen_pengampus
        public array $dosenIds,               // dosen_id aktual

        public int $kapasitasDibutuhkan,

        public string $jenisRuangDibutuhkan,  // TEORI | LABORATORIUM
        public int $sksTotal,

        public ?int $reqRuangId,

        // Kampus asal kelas
        public ?int $kelasKampusId = null,

        // Kampus tempat jadwal akhirnya ditempatkan
        public ?int $assignedKampusId = null,

        // True jika lokasi harus dipindahkan dari kampus asal
        public bool $hasLocationAdjustment = false,

        // Contoh: LAB_FALLBACK_MAIN_CAMPUS
        public ?string $adjustmentReasonCode = null,

        // Penjelasan untuk operator
        public ?string $adjustmentReasonText = null,

        // Diisi DemandCollector untuk sorting MCF/DSATUR
        public ?float $skorKesulitan = null,
    ) {}
}
