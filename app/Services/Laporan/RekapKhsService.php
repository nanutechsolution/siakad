<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\DTOs\Laporan\RekapKhsDto;
use App\Models\RiwayatStatusMahasiswa;
use App\Models\RefTahunAkademik;

/**
 * Service untuk Laporan Rekap KHS (Course Result Sheet Summary)
 * 
 * Menampilkan IPS, SKS, dan status akademik per mahasiswa per semester
 */
class RekapKhsService extends BaseLaporanService
{
    /**
     * Ambil data Rekap KHS
     * 
     * @param array $filters {
     *     @var int $tahun_akademik_id Required
     *     @var int $prodi_id Optional
     *     @var int $angkatan Optional
     *     @var string $mahasiswa_id Optional
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

        $query = RiwayatStatusMahasiswa::query()
            ->with([
                'mahasiswa.refPerson',
                'mahasiswa.refProdi',
                'tahunAkademik'
            ])
            ->where('tahun_akademik_id', $tahunAkademik->id);

        // Apply optional filters
        if (!empty($filters['prodi_id'])) {
            $query->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $filters['prodi_id']));
        }

        if (!empty($filters['angkatan'])) {
            $query->whereHas('mahasiswa', fn($q) => $q->where('angkatan_id', $filters['angkatan']));
        }

        if (!empty($filters['mahasiswa_id'])) {
            $query->where('mahasiswa_id', $filters['mahasiswa_id']);
        }

        $records = $query->orderBy('ips', 'desc')->get();

        // Transform ke DTO
        $dtos = $records->map(fn(RiwayatStatusMahasiswa $record) => $this->transformToDto($record))
            ->toArray();

        // Sort by nim
        $this->sortByKeys($dtos, ['nim' => 'ASC']);

        // Hitung summary
        $summary = $this->calculateSummary($dtos, $tahunAkademik->semester);

        return [
            'data' => $dtos,
            'summary' => $summary,
            'filter_summary' => $this->buildFilterSummary($filters),
        ];
    }

    /**
     * Transform RiwayatStatusMahasiswa ke RekapKhsDto
     */
    private function transformToDto(RiwayatStatusMahasiswa $record): RekapKhsDto
    {
        $statusAkademik = $this->determineStatusAkademik($record->ips);

        return new RekapKhsDto(
            nim: $record->mahasiswa->nim,
            nama_mahasiswa: $record->mahasiswa->refPerson->nama_lengkap,
            nama_prodi: $record->mahasiswa->refProdi->nama_prodi,
            angkatan: $record->mahasiswa->angkatan_id,
            semester: $record->tahunAkademik->semester,
            ips: $record->ips,
            sks_semester: $record->sks_semester,
            sks_total: $record->sks_total,
            status_akademik: $statusAkademik,
            kode_tahun_akademik: $record->tahunAkademik->kode_tahun,
            nama_tahun_akademik: $record->tahunAkademik->nama_tahun,
        );
    }

    /**
     * Tentukan status akademik berdasarkan IPS
     */
    private function determineStatusAkademik(float $ips): string
    {
        return match (true) {
            $ips >= 3.5 => 'Sangat Memuaskan',
            $ips >= 3.0 => 'Memuaskan',
            $ips >= 2.5 => 'Baik',
            $ips >= 2.0 => 'Cukup',
            $ips >= 1.0 => 'Kurang',
            default => 'Sangat Kurang',
        };
    }

    /**
     * Hitung summary statistics
     */
    private function calculateSummary(array $dtos, int $semester): array
    {
        $totalMahasiswa = count($dtos);
        $totalSksTotal = array_sum(array_column($dtos, 'sks_total'));

        $rataIps = $totalMahasiswa > 0
            ? array_sum(array_column($dtos, 'ips')) / $totalMahasiswa
            : 0;

        $maxIps = max(array_column($dtos, 'ips')) ?: 0;
        $minIps = min(array_column($dtos, 'ips')) ?: 0;

        $statusBreakdown = [];
        foreach ($dtos as $dto) {
            $status = $dto->status_akademik;
            $statusBreakdown[$status] = ($statusBreakdown[$status] ?? 0) + 1;
        }

        return [
            'total_mahasiswa' => $totalMahasiswa,
            'total_sks_keseluruhan' => $totalSksTotal,
            'rata_sks_per_mhs' => $totalMahasiswa > 0 ? round($totalSksTotal / $totalMahasiswa, 2) : 0,
            'rata_ips' => round($rataIps, 2),
            'max_ips' => round($maxIps, 2),
            'min_ips' => round($minIps, 2),
            'status_breakdown' => $statusBreakdown,
            'semester' => $semester,
        ];
    }
}
