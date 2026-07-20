<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\DTOs\Laporan\RekapKelulusanDto;
use App\Enums\StatusKuliah;
use App\Models\RiwayatStatusMahasiswa;
use DateTimeImmutable;

/**
 * Service untuk Laporan Rekap Kelulusan (Graduation Report)
 * 
 * Menampilkan data mahasiswa yang lulus (status_kuliah = 'L')
 */
class RekapKelulusanService extends BaseLaporanService
{
    // Minimum IPK untuk dianggap lulus
    private const MIN_IPK_LULUS = 2.0;
    
    // Minimum SKS untuk dianggap lulus
    private const MIN_SKS_LULUS = 144;

    /**
     * Ambil data Rekap Kelulusan
     * 
     * @param array $filters {
     *     @var int $prodi_id Optional
     *     @var int $angkatan Optional
     *     @var string $tahun_masuk Optional (filter by year)
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
        $query = RiwayatStatusMahasiswa::query()
            ->with(['mahasiswa.refPerson', 'mahasiswa.refProdi'])
            ->where('status_kuliah', StatusKuliah::LULUS->value);

        // Apply optional filters
        if (!empty($filters['prodi_id'])) {
            $query->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $filters['prodi_id']));
        }

        if (!empty($filters['angkatan'])) {
            $query->whereHas('mahasiswa', fn($q) => $q->where('angkatan_id', $filters['angkatan']));
        }

        $records = $query->get();

        // Transform ke DTO
        $dtos = $records->map(fn(RiwayatStatusMahasiswa $record) => $this->transformToDto($record))
            ->filter(fn($dto) => $dto !== null) // Filter null results
            ->values()
            ->all();

        // Sort by nama_mahasiswa
        $this->sortByKeys($dtos, ['nama_mahasiswa' => 'ASC']);

        // Calculate summary
        $summary = $this->calculateSummary($dtos, (int)$filters['angkatan'] ?? null);

        return [
            'data' => $dtos,
            'summary' => $summary,
            'filter_summary' => $this->buildFilterSummary($filters),
        ];
    }

    /**
     * Transform RiwayatStatusMahasiswa ke RekapKelulusanDto
     */
    private function transformToDto(RiwayatStatusMahasiswa $record): ?RekapKelulusanDto
    {
        $ipk = (float) $record->ipk;
        $sksTotal = (int) $record->sks_total;

        // Hanya include jika memenuhi kriteria lulus
        if ($ipk < self::MIN_IPK_LULUS || $sksTotal < self::MIN_SKS_LULUS) {
            return null;
        }

        $lamaStudi = $this->calculateLamaStudi($record->mahasiswa);
        $predikat = $this->tentkanPredikatLulus($ipk);

        return new RekapKelulusanDto(
            nim: $record->mahasiswa->nim ?? '-',
            nama_mahasiswa: $record->mahasiswa->refPerson->nama_lengkap ?? '(Data Person tidak ditemukan)',
            nama_prodi: $record->mahasiswa->refProdi->nama_prodi ?? '(Prodi tidak ditemukan)',
            angkatan: $record->mahasiswa->angkatan_id ?? 0,
            ipk_final: $ipk,
            sks_final: $sksTotal,
            tanggal_lulus: new DateTimeImmutable($record->updated_at->toDateTimeString()),
            lama_studi_semester: $lamaStudi,
            predikat_lulus: $predikat,
        );
    }

    /**
     * Hitung lama studi dalam semester
     */
    private function calculateLamaStudi($mahasiswa): ?int
    {
        $angkatan = $mahasiswa->angkatan_id;
        $latestStatus = $mahasiswa->riwayatStatusMahasiswas()
            ->where('status_kuliah', StatusKuliah::LULUS->value)
            ->orderBy('tahun_akademik_id', 'desc')
            ->first();

        if (!$latestStatus) {
            return null;
        }

        $tahunAkademikTerakhir = $latestStatus->tahunAkademik;
        
        // Hitung semester dari angkatan ke tahun lulus
        $tahunLulus = (int)explode('/', $tahunAkademikTerakhir->nama_tahun)[0];
        
        $jumlahTahun = $tahunLulus - $angkatan;
        $semester = ($jumlahTahun * 2) + $tahunAkademikTerakhir->semester;

        return max(1, $semester);
    }

    /**
     * Hitung summary statistik kelulusan
     */
    private function calculateSummary(array $dtos, ?int $angkatanFilter): array
    {
        $totalLulus = count($dtos);
        $totalIpk = array_sum(array_column($dtos, 'ipk_final'));
        $rataIpk = $totalLulus > 0 ? $totalIpk / $totalLulus : 0;

        $rataLamaStudi = 0;
        $lamaStudis = array_filter(array_column($dtos, 'lama_studi_semester'));
        if (!empty($lamaStudis)) {
            $rataLamaStudi = array_sum($lamaStudis) / count($lamaStudis);
        }

        // Breakdown by predikat
        $predikatBreakdown = [];
        foreach ($dtos as $dto) {
            $predikat = $dto->predikat_lulus ?? 'Tidak Diketahui';
            $predikatBreakdown[$predikat] = ($predikatBreakdown[$predikat] ?? 0) + 1;
        }

        return [
            'total_lulus' => $totalLulus,
            'rata_ipk_lulus' => round($rataIpk, 2),
            'rata_lama_studi_semester' => round($rataLamaStudi, 2),
            'predikat_breakdown' => $predikatBreakdown,
            'min_ipk_syarat' => self::MIN_IPK_LULUS,
            'min_sks_syarat' => self::MIN_SKS_LULUS,
        ];
    }
}