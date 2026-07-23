<?php

declare(strict_types=1);

namespace App\Application\Migration\Services;

use App\Domain\Migration\DTOs\GradeMigrationRowData;
use App\Domain\Migration\Exceptions\GradeMigrationResolutionException;
use App\Domain\Migration\ValueObjects\ResolvedGradeContext;
use App\Models\Mahasiswa;
use App\Models\MasterMataKuliah;
use App\Models\RefProdi;
use App\Models\RefSkalaNilai;
use App\Models\RefTahunAkademik;

/**
 * Meresolusi satu baris GradeMigrationRowData menjadi entitas-entitas
 * referensi yang sudah tervalidasi ada di database (ResolvedGradeContext).
 *
 * Melempar GradeMigrationResolutionException jika salah satu referensi
 * tidak ditemukan atau tidak konsisten (nilai angka vs nilai huruf).
 */
final class GradeMigrationResolverService
{
    public function resolve(GradeMigrationRowData $row): ResolvedGradeContext
    {
        $mahasiswa = $this->resolveMahasiswa($row->nim);
        $prodi = $this->resolveProdi($row->kodeProdiInternal);
        $mataKuliah = $this->resolveMataKuliah($row->kodeMk, $prodi);
        $tahunAkademik = $this->resolveTahunAkademik($row->tahun, $row->semester);
        $skalaNilai = $this->resolveSkalaNilai($row->nilaiAngka, $row->nilaiHuruf);

        return new ResolvedGradeContext(
            mahasiswa: $mahasiswa,
            prodi: $prodi,
            mataKuliah: $mataKuliah,
            tahunAkademik: $tahunAkademik,
            skalaNilai: $skalaNilai,
        );
    }

    private function resolveMahasiswa(string $nim): Mahasiswa
    {
        $mahasiswa = Mahasiswa::query()->where('nim', $nim)->first();

        if (! $mahasiswa instanceof Mahasiswa) {
            throw GradeMigrationResolutionException::mahasiswaNotFound($nim);
        }

        return $mahasiswa;
    }

    private function resolveProdi(string $kodeProdiInternal): RefProdi
    {
        $prodi = RefProdi::query()->where('kode_prodi_internal', $kodeProdiInternal)->first();

        if (! $prodi instanceof RefProdi) {
            throw GradeMigrationResolutionException::prodiNotFound($kodeProdiInternal);
        }

        return $prodi;
    }

    private function resolveMataKuliah(string $kodeMk, RefProdi $prodi): MasterMataKuliah
    {
        $mataKuliah = MasterMataKuliah::query()
            ->where('prodi_id', $prodi->id)
            ->where('kode_mk', $kodeMk)
            ->first();

        if (! $mataKuliah instanceof MasterMataKuliah) {
            throw GradeMigrationResolutionException::mataKuliahNotFound($kodeMk, $prodi->nama_prodi);
        }

        return $mataKuliah;
    }

    private function resolveTahunAkademik(int $tahun, int $semester): RefTahunAkademik
    {
        $kodeTahun = $this->buildKodeTahun($tahun, $semester);

        $tahunAkademik = RefTahunAkademik::query()->where('kode_tahun', $kodeTahun)->first();

        if (! $tahunAkademik instanceof RefTahunAkademik) {
            throw GradeMigrationResolutionException::tahunAkademikNotFound($tahun, $semester, $kodeTahun);
        }

        return $tahunAkademik;
    }

    /**
     * ASUMSI PENTING (mohon dikonfirmasi):
     * `ref_tahun_akademik.kode_tahun` adalah varchar(5) dan diasumsikan mengikuti
     * format "YYYYS" — 4 digit tahun + 1 digit semester (mis. tahun=2023, semester=1 → "20231"),
     * konsisten dengan pola kode semester PDDikti Feeder.
     *
     * Jika konvensi kode_tahun di data Anda berbeda, method ini HARUS disesuaikan
     * sebelum Fase 3 dijalankan.
     */
    private function buildKodeTahun(int $tahun, int $semester): string
    {
        return sprintf('%04d%d', $tahun, $semester);
    }

    private function resolveSkalaNilai(float $nilaiAngka, string $nilaiHuruf): RefSkalaNilai
    {
        $skala = RefSkalaNilai::query()->where('huruf', $nilaiHuruf)->first();

        if (! $skala instanceof RefSkalaNilai) {
            throw GradeMigrationResolutionException::skalaNilaiNotFound($nilaiHuruf);
        }

        $nilaiMin = (float) $skala->nilai_min;
        $nilaiMax = (float) $skala->nilai_max;

        if ($nilaiAngka < $nilaiMin || $nilaiAngka > $nilaiMax) {
            throw GradeMigrationResolutionException::skalaNilaiTidakSesuai(
                $nilaiAngka,
                $nilaiHuruf,
                $nilaiMin,
                $nilaiMax,
            );
        }

        return $skala;
    }
}
