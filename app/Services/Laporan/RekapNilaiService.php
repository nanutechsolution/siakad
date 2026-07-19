<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\DTOs\Laporan\RekapNilaiDto;
use App\Models\JadwalKuliah;
use App\Models\KrsDetail;

/**
 * Service untuk Laporan Rekap Nilai (Grade Report)
 * 
 * Menampilkan statistik nilai per mata kuliah per dosen
 */
class RekapNilaiService extends BaseLaporanService
{
    /**
     * Ambil data Rekap Nilai
     * 
     * @param array $filters {
     *     @var int $tahun_akademik_id Required
     *     @var int $prodi_id Optional
     *     @var int $mata_kuliah_id Optional
     * }
     * 
     * @return array {
     *     @var array $data
     *     @var array $summary
     *     @var string $filter_summary
     * }
     */
    public function getData(array $filters): array
    {
        $this->validateFilterParams($filters);

        $tahunAkademik = $this->getTahunAkademik((int)$filters['tahun_akademik_id']);

        $query = JadwalKuliah::query()
            ->with([
                'mataKuliah',
                'tahunAkademik',
                'krsDetails' => function ($q) {
                    $q->where('is_published', true);
                },
                'krsDetails.krs',
                'jadwalKuliahDosen.dosen.refPerson'
            ])
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->where('is_published', true);

        // Apply optional filter by subject
        if (!empty($filters['mata_kuliah_id'])) {
            $query->where('mata_kuliah_id', $filters['mata_kuliah_id']);
        }

        // Filter by prodi if provided
        if (!empty($filters['prodi_id'])) {
            $query->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $filters['prodi_id']));
        }

        $jadwalKuliahs = $query->get();

        // Transform ke DTO
        $dtos = [];
        foreach ($jadwalKuliahs as $jadwal) {
            $dtos = array_merge($dtos, $this->transformToDto($jadwal));
        }

        // Sort by nama_mata_kuliah
        $this->sortByKeys($dtos, ['nama_mata_kuliah' => 'ASC']);

        // Hitung summary
        $summary = $this->calculateSummary($dtos);

        return [
            'data' => $dtos,
            'summary' => $summary,
            'filter_summary' => $this->buildFilterSummary($filters),
        ];
    }

    /**
     * Transform JadwalKuliah ke array RekapNilaiDto
     * (bisa multiple jika ada multiple dosen penilai)
     */
    private function transformToDto(JadwalKuliah $jadwal): array
    {
        $dtos = [];
        $krsDetails = $jadwal->krsDetails;

        if ($krsDetails->isEmpty()) {
            return $dtos;
        }

        // Get penilai dosen (is_penilai = 1)
        $penilaiDosens = $jadwal->jadwalKuliahDosen->where('is_penilai', true);

        if ($penilaiDosens->isEmpty()) {
            // Jika tidak ada penilai khusus, ambil koordinator
            $penilaiDosens = $jadwal->jadwalKuliahDosen->where('is_koordinator', true);
        }

        foreach ($penilaiDosens as $dosens) {
            $dtos[] = $this->buildNilaiDto($jadwal, $krsDetails, $dosens);
        }

        // Jika tidak ada dosen, create generic entry
        if (empty($dtos)) {
            $dtos[] = $this->buildNilaiDto($jadwal, $krsDetails, null);
        }

        return $dtos;
    }

    /**
     * Build RekapNilaiDto
     */
    private function buildNilaiDto(JadwalKuliah $jadwal, $krsDetails, $dosens): RekapNilaiDto
    {
        $nilaiAngkas = $krsDetails->pluck('nilai_angka')->filter(fn($n) => $n > 0)->toArray();
        $nilaiHuruf = $krsDetails->pluck('nilai_huruf')->filter()->toArray();

        $jumlahPeserta = $krsDetails->count();
        $rataNilai = !empty($nilaiAngkas) ? array_sum($nilaiAngkas) / count($nilaiAngkas) : 0;

        // Count by grade
        $gradeCount = array_count_values($nilaiHuruf);
        $jmlA = $gradeCount['A'] ?? 0;
        $jmlB = $gradeCount['B'] ?? 0;
        $jmlC = $gradeCount['C'] ?? 0;
        $jmlD = $gradeCount['D'] ?? 0;
        $jmlE = $gradeCount['E'] ?? 0;
        $jmlTidakLulus = $jmlD + $jmlE;

        $persentaseLulus = $this->hitungPersentase($jumlahPeserta - $jmlTidakLulus, $jumlahPeserta);

        return new RekapNilaiDto(
            kode_mata_kuliah: $jadwal->mataKuliah->kode_mk,
            nama_mata_kuliah: $jadwal->mataKuliah->nama_mk,
            sks: $jadwal->mataKuliah->sks_default,
            nama_dosen: $dosens?->dosen?->refPerson?->nama_lengkap ?? 'TBA',
            jumlah_peserta: $jumlahPeserta,
            rata_rata_nilai: round($rataNilai, 2),
            jumlah_a: $jmlA,
            jumlah_b: $jmlB,
            jumlah_c: $jmlC,
            jumlah_d: $jmlD,
            jumlah_e: $jmlE,
            jumlah_tidak_lulus: $jmlTidakLulus,
            persentase_lulus: $persentaseLulus,
            kode_tahun_akademik: $jadwal->tahunAkademik->kode_tahun,
        );
    }

    /**
     * Hitung summary statistics
     */
    private function calculateSummary(array $dtos): array
    {
        $totalMataKuliah = count($dtos);
        $totalPeserta = array_sum(array_column($dtos, 'jumlah_peserta'));
        
        $rataRataNilaiKeseluruhan = $totalMataKuliah > 0
            ? array_sum(array_column($dtos, 'rata_rata_nilai')) / $totalMataKuliah
            : 0;

        $totalTidakLulus = array_sum(array_column($dtos, 'jumlah_tidak_lulus'));
        $persentaseLulusKeseluruhan = $this->hitungPersentase($totalPeserta - $totalTidakLulus, $totalPeserta);

        return [
            'total_mata_kuliah' => $totalMataKuliah,
            'total_peserta' => $totalPeserta,
            'rata_nilai_keseluruhan' => round($rataRataNilaiKeseluruhan, 2),
            'total_tidak_lulus' => $totalTidakLulus,
            'persentase_lulus_keseluruhan' => $persentaseLulusKeseluruhan,
        ];
    }
}