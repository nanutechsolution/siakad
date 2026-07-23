<?php

declare(strict_types=1);

namespace App\Domain\Migration\ValueObjects;

use App\Models\Mahasiswa;
use App\Models\MasterMataKuliah;
use App\Models\RefProdi;
use App\Models\RefSkalaNilai;
use App\Models\RefTahunAkademik;

/**
 * Hasil resolusi seluruh entitas referensi untuk satu baris migrasi nilai.
 * Dibentuk oleh GradeMigrationResolverService setelah semua lookup berhasil.
 */
final readonly class ResolvedGradeContext
{
    public function __construct(
        public Mahasiswa $mahasiswa,
        public RefProdi $prodi,
        public MasterMataKuliah $mataKuliah,
        public RefTahunAkademik $tahunAkademik,
        public RefSkalaNilai $skalaNilai,
    ) {}
}
