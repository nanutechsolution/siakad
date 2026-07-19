<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\DTOs\Laporan\LamaStudiDto;
use App\Enums\StatusKuliah;
use App\Models\Mahasiswa;
use App\Models\RiwayatStatusMahasiswa;

/**
 * Service untuk Laporan Lama Studi (Study Duration Report)
 * 
 * Menampilkan analisis lama studi per angkatan dan prodi
 */
class LamaStudiService extends BaseLaporanService
{
    /**
     * Ambil data Lama Studi
     * 
     * @param array $filters {
     *     @var int $prodi_id Optional
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
        $query = Mahasiswa::query()
            ->with(['refProdi', 'riwayatStatusMahasiswas.tahunAkademik'])
            ->where('deleted_at', null);

        // Apply optional filter
        if (!empty($filters['prodi_id'])) {
            $query->where('prodi_id', $filters['prodi_id']);
        }

        $mahasiswas = $query->get();

        // Group by angkatan dan prodi
        $grouped = $mahasiswas->groupBy(fn($m) => "{$m->angkatan_id}|{$m->prodi_id}");

        $dtos = $grouped->map(function ($group) {
            $firstItem = $group->first();
            
            return new LamaStudiDto(
                angkatan: $firstItem->angkatan_id,
                nama_prodi: $firstItem->refProdi->nama_prodi,
                jumlah_mahasiswa: count($group),
                jumlah_lulus: $this->countByStatus($group, StatusKuliah::LULUS->value),
                persentase_lulus: $this->calculatePersentaseLulus($group),
                rata_rata_semester_studi: $this->calculateRataRataLamaStudi($group),
                semester_tercepat: $this->findFastestCompletion($group),
                semester_terlama: $this->findSlowestCompletion($group),
                jumlah_aktif: $this->countByStatus($group, StatusKuliah::AKTIF->value),
                jumlah_cuti: $this->countByStatus($group, StatusKuliah::CUTI->value),
                jumlah_do: $this->countByStatus($group, StatusKuliah::DROP_OUT->value),
            );
        })->values()->toArray();

        // Sort by angkatan desc, then prodi
        $this->sortByKeys($dtos, [
            'angkatan' => 'DESC',
            'nama_prodi' => 'ASC'
        ]);

        // Calculate summary
        $summary = $this->calculateSummary($dtos, $mahasiswas);

        return [
            'data' => $dtos,
            'summary' => $summary,
            'filter_summary' => $this->buildFilterSummary($filters),
        ];
    }

    /**
     * Hitung mahasiswa dengan status tertentu
     */
    private function countByStatus($group, string $status): int
    {
        return $group->filter(function ($mahasiswa) use ($status) {
            return $mahasiswa->riwayatStatusMahasiswas()
                ->where('status_kuliah', $status)
                ->exists();
        })->count();
    }

    /**
     * Hitung persentase kelulusan
     */
    private function calculatePersentaseLulus($group): float
    {
        $jumlah = count($group);
        if ($jumlah === 0) {
            return 0;
        }

        $lulus = $this->countByStatus($group, StatusKuliah::LULUS->value);
        return $this->hitungPersentase($lulus, $jumlah);
    }

    /**
     * Hitung rata-rata lama studi dalam semester
     */
    private function calculateRataRataLamaStudi($group): float
    {
        $lamaStudis = [];

        foreach ($group as $mahasiswa) {
            $lamaStudi = $this->calculateLamaStudiSingle($mahasiswa);
            if ($lamaStudi !== null && $lamaStudi > 0) {
                $lamaStudis[] = $lamaStudi;
            }
        }

        if (empty($lamaStudis)) {
            return 0;
        }

        return array_sum($lamaStudis) / count($lamaStudis);
    }

    /**
     * Hitung lama studi untuk single mahasiswa
     */
    private function calculateLamaStudiSingle(Mahasiswa $mahasiswa): ?int
    {
        $angkatan = $mahasiswa->angkatan_id;
        
        // Hitung dari riwayat status
        $lastStatus = $mahasiswa->riwayatStatusMahasiswas()
            ->orderBy('tahun_akademik_id', 'desc')
            ->first();

        if (!$lastStatus) {
            return null;
        }

        $tahunAkademik = $lastStatus->tahunAkademik;
        
        // Parse tahun akademik (format: "2024/2025")
        $tahunStart = (int)explode('/', $tahunAkademik->nama_tahun)[0];
        
        $jumlahTahun = $tahunStart - $angkatan;
        $semester = ($jumlahTahun * 2) + $tahunAkademik->semester;

        return max(1, $semester);
    }

    /**
     * Find fastest completion (terfelit semester)
     */
    private function findFastestCompletion($group): int
    {
        $lamaStudis = [];

        foreach ($group as $mahasiswa) {
            if ($this->countByStatus($group, StatusKuliah::LULUS->value) > 0) {
                $lamaStudi = $this->calculateLamaStudiSingle($mahasiswa);
                if ($lamaStudi !== null && $lamaStudi > 0) {
                    $lamaStudis[] = $lamaStudi;
                }
            }
        }

        return !empty($lamaStudis) ? min($lamaStudis) : 0;
    }

    /**
     * Find slowest completion (terlama semester)
     */
    private function findSlowestCompletion($group): int
    {
        $lamaStudis = [];

        foreach ($group as $mahasiswa) {
            $lamaStudi = $this->calculateLamaStudiSingle($mahasiswa);
            if ($lamaStudi !== null && $lamaStudi > 0) {
                $lamaStudis[] = $lamaStudi;
            }
        }

        return !empty($lamaStudis) ? max($lamaStudis) : 0;
    }

    /**
     * Hitung summary statistik keseluruhan
     */
    private function calculateSummary(array $dtos, $allMahasiswas): array
    {
        $totalMahasiswa = count($allMahasiswas);
        $totalLulus = 0;
        $totalAngkatan = count(array_unique(array_column($dtos, 'angkatan')));
        $totalProdi = count(array_unique(array_column($dtos, 'nama_prodi')));

        $allLamaStudis = [];
        
        foreach ($dtos as $dto) {
            $totalLulus += $dto->jumlah_lulus;
            for ($i = 0; $i < $dto->jumlah_lulus; $i++) {
                $allLamaStudis[] = $dto->rata_rata_semester_studi;
            }
        }

        $rataLamaStudiKeseluruhan = !empty($allLamaStudis) 
            ? array_sum($allLamaStudis) / count($allLamaStudis) 
            : 0;

        return [
            'total_mahasiswa' => $totalMahasiswa,
            'total_lulus' => $totalLulus,
            'persentase_lulus_keseluruhan' => $this->hitungPersentase($totalLulus, $totalMahasiswa),
            'total_angkatan' => $totalAngkatan,
            'total_prodi' => $totalProdi,
            'rata_lama_studi_keseluruhan' => round($rataLamaStudiKeseluruhan, 2),
            'semester_tercepat_overall' => min(array_column($dtos, 'semester_tercepat')),
            'semester_terlama_overall' => max(array_column($dtos, 'semester_terlama')),
        ];
    }
}