<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\DTOs\Laporan\StatistikIpkDto;
use App\Models\RiwayatStatusMahasiswa;

/**
 * Service untuk Laporan Statistik IPK (GPA Statistics)
 * 
 * Menampilkan distribusi IPK mahasiswa per prodi per angkatan
 */
class StatistikIpkService extends BaseLaporanService
{
    /**
     * Ambil data Statistik IPK
     * 
     * @param array $filters {
     *     @var int $tahun_akademik_id Required (untuk data terakhir)
     *     @var int $prodi_id Optional
     *     @var int $angkatan Optional
     * }
     * 
     * @return array {
     *     @var array $data (per prodi)
     *     @var array $summary
     *     @var string $filter_summary
     * }
     */
    public function getData(array $filters): array
    {
        $this->validateFilterParams($filters);

        $tahunAkademik = $this->getTahunAkademik((int)$filters['tahun_akademik_id']);

        $query = RiwayatStatusMahasiswa::query()
            ->with(['mahasiswa.refProdi', 'tahunAkademik'])
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->where('ipk', '>', 0); // Filter out zero IPK

        // Apply optional filters
        if (!empty($filters['prodi_id'])) {
            $query->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $filters['prodi_id']));
        }

        if (!empty($filters['angkatan'])) {
            $query->whereHas('mahasiswa', fn($q) => $q->where('angkatan_id', $filters['angkatan']));
        }

        $records = $query->get();

        // Group by prodi dan angkatan
        $grouped = $records->groupBy(function ($item) {
            return "{$item->mahasiswa->prodi_id}|{$item->mahasiswa->angkatan_id}";
        });

        $dtos = $grouped->map(function ($group) use ($tahunAkademik) {
            $firstItem = $group->first();
            $ipks = $group->pluck('ipk')->toArray();

            return new StatistikIpkDto(
                nama_prodi: $firstItem->mahasiswa->refProdi->nama_prodi,
                angkatan: $firstItem->mahasiswa->angkatan_id,
                ipk_rata_rata: round(array_sum($ipks) / count($ipks), 2),
                ipk_tertinggi: (float)max($ipks),
                ipk_terendah: (float)min($ipks),
                jumlah_mahasiswa: count($ipks),
                distribusi_ipk: $this->calculateDistribusiIpk($ipks),
                distribusi_status: $this->calculateDistribusiStatus($group),
                kode_tahun_akademik: $tahunAkademik->kode_tahun,
            );
        })->values()->toArray();

        // Sort by prodi name then angkatan
        $this->sortByKeys($dtos, [
            'nama_prodi' => 'ASC',
            'angkatan' => 'DESC'
        ]);

        // Calculate overall summary
        $summary = $this->calculateSummary($dtos, $records);

        return [
            'data' => $dtos,
            'summary' => $summary,
            'filter_summary' => $this->buildFilterSummary($filters),
        ];
    }

    /**
     * Hitung distribusi IPK dengan kategori range
     */
    private function calculateDistribusiIpk(array $ipks): array
    {
        $ranges = [
            '4.0 - 3.5' => ['min' => 3.5, 'max' => 4.0],
            '3.5 - 3.0' => ['min' => 3.0, 'max' => 3.49],
            '3.0 - 2.5' => ['min' => 2.5, 'max' => 2.99],
            '2.5 - 2.0' => ['min' => 2.0, 'max' => 2.49],
            '< 2.0' => ['min' => 0, 'max' => 1.99],
        ];

        $distribusi = [];
        foreach ($ranges as $label => $range) {
            $count = count(array_filter($ipks, fn($ipk) => 
                $ipk >= $range['min'] && $ipk <= $range['max']
            ));
            $distribusi[$label] = $count;
        }

        return $distribusi;
    }

    /**
     * Hitung distribusi status akademik
     */
    private function calculateDistribusiStatus($group): array
    {
        $statusCount = [];
        foreach ($group as $item) {
            $status = $this->determineStatusAkademik($item->ipk);
            $statusCount[$status] = ($statusCount[$status] ?? 0) + 1;
        }

        return $statusCount;
    }

    /**
     * Tentukan status akademik berdasarkan IPK
     */
    private function determineStatusAkademik(float $ipk): string
    {
        return match (true) {
            $ipk >= 3.5 => 'Sangat Memuaskan',
            $ipk >= 3.0 => 'Memuaskan',
            $ipk >= 2.5 => 'Baik',
            $ipk >= 2.0 => 'Cukup',
            default => 'Kurang',
        };
    }

    /**
     * Hitung summary statistik keseluruhan
     */
    private function calculateSummary(array $dtos, $allRecords): array
    {
        $allIpks = $allRecords->pluck('ipk')->toArray();

        return [
            'total_prodi' => count(array_unique(array_column($dtos, 'nama_prodi'))),
            'total_angkatan' => count(array_unique(array_column($dtos, 'angkatan'))),
            'total_mahasiswa' => count($allIpks),
            'ipk_rata_rata_keseluruhan' => round(array_sum($allIpks) / count($allIpks), 2),
            'ipk_tertinggi_keseluruhan' => (float)max($allIpks),
            'ipk_terendah_keseluruhan' => (float)min($allIpks),
            'distribusi_keseluruhan' => $this->calculateDistribusiIpk($allIpks),
        ];
    }
}