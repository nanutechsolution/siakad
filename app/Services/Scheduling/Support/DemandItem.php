<?php

namespace App\Services\Scheduling\Support;

class DemandItem
{
    public function __construct(
        public int $mataKuliahId,
        public int $kelasId,
        public int $kelasProdiId,
        public array $dosenPengampuRowIds,   // id baris dosen_pengampus (utk simpan ke result)
        public array $dosenIds,               // dosen_id aktual (utk cek bentrok)
        public int $kapasitasDibutuhkan,
        public string $jenisRuangDibutuhkan,  // 'TEORI' | 'LABORATORIUM'
        public int $sksTotal,
        public ?int $reqRuangId,
        public ?float $skorKesulitan = null,  // diisi DemandCollector utk sorting MCF/DSATUR
    ) {}
}
