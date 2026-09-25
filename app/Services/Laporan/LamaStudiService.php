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
     * Cache per-run lama studi tiap mahasiswa: method ini dipanggil dari
     * rata-rata + tercepat + terlama.
     *
     * @var array<string, int|null>
     */
    private array $lamaStudiCache = [];

    /**
     * Hitung mahasiswa dengan status tertentu lewat relasi eager-loaded.
     */
    private function countByStatus($group, string $status): int
    {
        return $group->filter(fn(Mahasiswa $mahasiswa): bool => $this->hasStatus($mahasiswa, $status))->count();
    }

    /**
     * Cek status mahasiswa lewat relasi yang sudah di-eager-load (tanpa query
     * tambahan per mahasiswa).
     */
    private function hasStatus(Mahasiswa $mahasiswa, string $status): bool
    {
        return $mahasiswa->riwayatStatusMahasiswas
            ->contains(fn(RiwayatStatusMahasiswa $riwayat): bool => $riwayat->status_kuliah === $status);
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
     * Hitung rata-rata lama studi dalam semester (khusus mahasiswa LULUS,
     * populasi yang sama dengan semester tercepat/terlama sehingga angka
     * ringkasan berbobot jumlah_lulus di calculateSummary() konsisten).
     */
    private function calculateRataRataLamaStudi($group): float
    {
        $lamaStudis = $this->collectLamaStudiLulus($group);

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
        $key = (string) $mahasiswa->getKey();

        if (array_key_exists($key, $this->lamaStudiCache)) {
            return $this->lamaStudiCache[$key];
        }

        $angkatan = $mahasiswa->angkatan_id;

        // Relasi sudah di-eager-load; ambil status terakhir dari collection
        // agar laporan besar tidak menjalankan first() per mahasiswa.
        $lastStatus = $mahasiswa->riwayatStatusMahasiswas
            ->sortByDesc('tahun_akademik_id')
            ->first();

        if (!$lastStatus) {
            return $this->lamaStudiCache[$key] = null;
        }

        $tahunAkademik = $lastStatus->tahunAkademik;

        // Parse tahun akademik (format: "2024/2025")
        $tahunStart = (int)explode('/', $tahunAkademik->nama_tahun)[0];

        $jumlahTahun = $tahunStart - $angkatan;
        $semester = ($jumlahTahun * 2) + $tahunAkademik->semester;

        return $this->lamaStudiCache[$key] = max(1, $semester);
    }

    /**
     * Lam studi hanya bermakna untuk mahasiswa berstatus LULUS — perhitungan
     * sebelumnya menyertakan mahasiswa aktif/cuti/DO di sisi "tercepat",
     * padahal metodologi lama studi adalah semester sampai lulus.
     *
     * @return array<int, int>
     */
    private function collectLamaStudiLulus($group): array
    {
        $lamaStudis = [];

        foreach ($group as $mahasiswa) {
            if (! $this->hasStatus($mahasiswa, StatusKuliah::LULUS->value)) {
                continue;
            }

            $lamaStudi = $this->calculateLamaStudiSingle($mahasiswa);

            if ($lamaStudi !== null && $lamaStudi > 0) {
                $lamaStudis[] = $lamaStudi;
            }
        }

        return $lamaStudis;
    }

    /**
     * Find fastest completion (semester paling cepat di antara yang lulus)
     */
    private function findFastestCompletion($group): int
    {
        $lamaStudis = $this->collectLamaStudiLulus($group);

        return !empty($lamaStudis) ? min($lamaStudis) : 0;
    }

    /**
     * Find slowest completion (semester paling lama di antara yang lulus)
     */
    private function findSlowestCompletion($group): int
    {
        $lamaStudis = $this->collectLamaStudiLulus($group);

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

        // min()/max() melempar ValueError pada array kosong (PHP 8+) — laporan
        // tanpa data sekalipun harus merender ringkasan, bukan error 500.
        if (empty($dtos)) {
            return [
                'total_mahasiswa' => $totalMahasiswa,
                'total_lulus' => 0,
                'persentase_lulus_keseluruhan' => $this->hitungPersentase(0, $totalMahasiswa),
                'total_angkatan' => 0,
                'total_prodi' => 0,
                'rata_lama_studi_keseluruhan' => 0,
                'semester_tercepat_overall' => 0,
                'semester_terlama_overall' => 0,
            ];
        }

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