<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\DTOs\Laporan\StatusMahasiswaDto;
use App\Enums\StatusKuliah;
use App\Models\RiwayatStatusMahasiswa;

/**
 * Service untuk Laporan Status Mahasiswa
 * 
 * Menampilkan data mahasiswa berdasarkan status: Aktif, Cuti, atau Drop Out
 * Digunakan untuk 3 laporan: Mahasiswa Aktif, Cuti, DO
 */
class StatusMahasiswaService extends BaseLaporanService
{
    /**
     * Ambil data status mahasiswa
     * 
     * @param array $filters {
     *     @var int $tahun_akademik_id Required
     *     @var string $status Required (A, C, D, L, K, G, N)
     *     @var int $prodi_id Optional
     *     @var int $angkatan Optional
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

        if (empty($filters['status'])) {
            throw new \InvalidArgumentException('Status mahasiswa harus dipilih (A/C/D/L/K/G/N)');
        }

        $tahunAkademik = $this->getTahunAkademik((int)$filters['tahun_akademik_id']);
        $statusCode = $filters['status'];

        // Validasi status code
        if (!in_array($statusCode, ['A', 'C', 'D', 'L', 'K', 'G', 'N'])) {
            throw new \InvalidArgumentException("Status '{$statusCode}' tidak valid");
        }

        $query = RiwayatStatusMahasiswa::query()
            ->with(['mahasiswa.refPerson', 'mahasiswa.refProdi', 'tahunAkademik'])
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->where('status_kuliah', $statusCode);

        // Apply optional filters
        if (!empty($filters['prodi_id'])) {
            $query->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $filters['prodi_id']));
        }

        if (!empty($filters['angkatan'])) {
            $query->whereHas('mahasiswa', fn($q) => $q->where('angkatan_id', $filters['angkatan']));
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        // Transform ke DTO
        $dtos = $records->map(fn(RiwayatStatusMahasiswa $record) => $this->transformToDto($record, $statusCode))
            ->all();

        // Sort by nim
        $this->sortByKeys($dtos, ['nim' => 'ASC']);

        // Calculate summary
        $summary = $this->calculateSummary($dtos, $statusCode);

        return [
            'data' => $dtos,
            'summary' => $summary,
            'filter_summary' => $this->buildFilterSummary($filters),
        ];
    }

    /**
     * Transform RiwayatStatusMahasiswa ke StatusMahasiswaDto
     */
    private function transformToDto(RiwayatStatusMahasiswa $record, string $statusCode): StatusMahasiswaDto
    {
        $semesterTerdaftar = $record->mahasiswa->riwayatStatusMahasiswas()
            ->count();

        return new StatusMahasiswaDto(
            nim: $record->mahasiswa->nim,
            nama_mahasiswa: $record->mahasiswa->refPerson->nama_lengkap,
            nama_prodi: $record->mahasiswa->refProdi->nama_prodi,
            angkatan: $record->mahasiswa->angkatan_id,
            status_kuliah: $statusCode,
            status_label: $this->getStatusLabel($statusCode),
            semester_terdaftar: $semesterTerdaftar,
            ips_terakhir: $record->ips !== null ? (float) $record->ips : null,
            ipk_terakhir: $record->ipk !== null ? (float) $record->ipk : null,
            kode_tahun_akademik: $record->tahunAkademik->kode_tahun,
        );
    }

    /**
     * Ambil label status dari enum
     */
    private function getStatusLabel(string $code): string
    {
        $status = StatusKuliah::tryFrom($code);
        return $status ? $status->label() : $code;
    }

    /**
     * Hitung summary statistik
     */
    private function calculateSummary(array $dtos, string $statusCode): array
    {
        $totalMahasiswa = count($dtos);

        // Group by prodi
        $prodiBreakdown = [];
        foreach ($dtos as $dto) {
            $prodi = $dto->nama_prodi;
            $prodiBreakdown[$prodi] = ($prodiBreakdown[$prodi] ?? 0) + 1;
        }

        // Group by angkatan
        $angkatanBreakdown = [];
        foreach ($dtos as $dto) {
            $angkatan = $dto->angkatan;
            $angkatanBreakdown[$angkatan] = ($angkatanBreakdown[$angkatan] ?? 0) + 1;
        }

        // Rata-rata IPS dan IPK
        $ipsValues = array_filter(array_column($dtos, 'ips_terakhir'));
        $ipkValues = array_filter(array_column($dtos, 'ipk_terakhir'));

        $rataIps = !empty($ipsValues) ? array_sum($ipsValues) / count($ipsValues) : 0;
        $rataIpk = !empty($ipkValues) ? array_sum($ipkValues) / count($ipkValues) : 0;

        return [
            'total_mahasiswa' => $totalMahasiswa,
            'status' => $this->getStatusLabel($statusCode),
            'rata_ips' => round($rataIps, 2),
            'rata_ipk' => round($rataIpk, 2),
            'breakdown_prodi' => $prodiBreakdown,
            'breakdown_angkatan' => $angkatanBreakdown,
        ];
    }
}
