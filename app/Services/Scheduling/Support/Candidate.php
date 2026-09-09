<?php

namespace App\Services\Scheduling\Support;

class Candidate
{
    public function __construct(
        public string $hari,
        public string $jamMulai,
        public string $jamSelesai,
        public int $ruangId,
        public int $kapasitasRuang,
        public float $skor = 0.0,

        // normal | preferred_fallback
        public string $roomSource = 'normal',

        // Keterangan jika menggunakan ruang alternatif.
        public ?string $roomNote = null,
    ) {}
}