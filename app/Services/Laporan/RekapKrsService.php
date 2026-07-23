<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\DTOs\Laporan\RekapKrsDto;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use Illuminate\Support\Collection;

/**
 * Service untuk Laporan Rekap KRS (Course Registration Summary)
 * 
 * Menampilkan ringkasan KRS per mahasiswa per semester
 */
class RekapKrsService extends BaseLaporanService
{
    /**
     * Ambil data Rekap KRS
     * 
     * @param array $filters {
     *     @var int $tahun_akademik_id Required
     *     @var int $prodi_id Optional
     *     @var int $angkatan Optional
     *     @var string $mahasiswa_id Optional (specific student)
     * }
     * 
     * @return array {
     *     @var Collection $data
     *     @var array $summary
     *     @var string $filter_summary
     * }
     */
    public function getData(array $filters): array
    {
        $this->validateFilterParams($filters);

        $tahunAkademik = $this->getTahunAkademik((int)$filters['tahun_akademik_id']);

        $query = Krs::query()
            ->with([
                'mahasiswa.person',
                'mahasiswa.prodi',
                'tahunAkademik',
                'details.mataKuliah'
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

        $krsRecords = $query->orderBy('created_at', 'desc')->get();

        // Transform ke DTO
        $dtos = $krsRecords->map(fn(Krs $krs) => $this->transformToDto($krs, $tahunAkademik))->all();

        // Sort by nim
        $this->sortByKeys($dtos, ['nim' => 'ASC']);

        // Hitung summary
        $summary = $this->calculateSummary($dtos);

        return [
            'data' => $dtos,
            'summary' => $summary,
            'filter_summary' => $this->buildFilterSummary($filters),
        ];
    }

    /**
     * Transform KRS record ke RekapKrsDto
     */
    private function transformToDto(Krs $krs, $tahunAkademik): RekapKrsDto
    {
        $jumlahMk = $krs->details->count();
        $totalSks = (int)$krs->details->sum(fn($detail) => $detail->masterMataKuliah?->sks_default ?? 0);
        return new RekapKrsDto(
            nim: $krs->mahasiswa->nim,
            nama_mahasiswa: $krs->mahasiswa->person->nama_lengkap,
            nama_prodi: $krs->mahasiswa->prodi->nama_prodi,
            angkatan: $krs->mahasiswa->angkatan_id,
            semester: $tahunAkademik->semester,
            jumlah_mata_kuliah: $jumlahMk,
            total_sks: $totalSks,
            status_krs: $krs->status_krs,
            kode_tahun_akademik: $tahunAkademik->kode_tahun,
            nama_tahun_akademik: $tahunAkademik->nama_tahun,
        );
    }

    /**
     * Hitung summary statistics
     */
    private function calculateSummary(array $dtos): array
    {
        $totalMahasiswa = count($dtos);
        $totalMataKuliah = array_sum(array_column($dtos, 'jumlah_mata_kuliah'));
        $totalSks = array_sum(array_column($dtos, 'total_sks'));
        $rataSksPerMahasiswa = $totalMahasiswa > 0 ? $totalSks / $totalMahasiswa : 0;

        $statusBreakdown = array_count_values(
            array_map(
                fn($status) => $status->value,
                array_column($dtos, 'status_krs')
            )
        );
        return [
            'total_mahasiswa' => $totalMahasiswa,
            'total_mata_kuliah' => $totalMataKuliah,
            'total_sks' => $totalSks,
            'rata_sks_per_mahasiswa' => round($rataSksPerMahasiswa, 2),
            'status_breakdown' => $statusBreakdown,
        ];
    }
}
