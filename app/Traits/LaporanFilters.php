<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\RefFakultas;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\RefAngkatan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Trait untuk shared filter logic pada semua laporan akademik
 * 
 * Menyediakan helper methods untuk filter standar:
 * - Tahun Akademik
 * - Semester
 * - Fakultas
 * - Program Studi
 * - Angkatan
 */
trait LaporanFilters
{
    /**
     * Validasi dan ambil tahun akademik aktif atau spesifik
     */
    protected function getTahunAkademik(?int $tahunAkademikId = null): RefTahunAkademik
    {
        if ($tahunAkademikId) {
            return RefTahunAkademik::findOrFail($tahunAkademikId);
        }

        $active = RefTahunAkademik::where('is_active', true)->first();
        
        if (!$active) {
            throw new \RuntimeException('Tidak ada tahun akademik yang aktif. Silakan set tahun akademik aktif terlebih dahulu.');
        }

        return $active;
    }

    /**
     * Filter query builder berdasarkan tahun akademik
     */
    protected function filterByTahunAkademik(Builder $query, int $tahunAkademikId, string $column = 'tahun_akademik_id'): Builder
    {
        return $query->where($column, $tahunAkademikId);
    }

    /**
     * Filter query builder berdasarkan semester
     */
    protected function filterBySemester(Builder $query, int $semester, string $foreignKeyColumn = 'tahun_akademik_id'): Builder
    {
        return $query->whereHas('tahunAkademik', function (Builder $q) use ($semester) {
            $q->where('semester', $semester);
        }, '=', $foreignKeyColumn);
    }

    /**
     * Filter query builder berdasarkan prodi
     */
    protected function filterByProdi(Builder $query, int $prodiId, string $column = 'prodi_id'): Builder
    {
        return $query->where($column, $prodiId);
    }

    /**
     * Filter query builder berdasarkan fakultas
     */
    protected function filterByFakultas(Builder $query, int $fakultasId): Builder
    {
        return $query->whereHas('prodi.fakultas', function (Builder $q) use ($fakultasId) {
            $q->where('id', $fakultasId);
        });
    }

    /**
     * Filter query builder berdasarkan angkatan
     */
    protected function filterByAngkatan(Builder $query, int $angkatan, string $column = 'angkatan_id'): Builder
    {
        return $query->where($column, $angkatan);
    }

    /**
     * Filter query builder berdasarkan mahasiswa
     */
    protected function filterByMahasiswa(Builder $query, string $mahasiswaId, string $column = 'mahasiswa_id'): Builder
    {
        return $query->where($column, $mahasiswaId);
    }

    /**
     * Dapatkan list tahun akademik untuk dropdown
     */
    protected function getListTahunAkademik(): Collection
    {
        return RefTahunAkademik::orderByDesc('created_at')
            ->get()
            ->map(fn($ta) => [
                'id' => $ta->id,
                'label' => "{$ta->nama_tahun} - Semester {$ta->semester}",
                'kode' => $ta->kode_tahun,
            ]);
    }

    /**
     * Dapatkan list prodi untuk dropdown
     */
    protected function getListProdi(?int $fakultasId = null): Collection
    {
        $query = RefProdi::where('is_active', true);

        if ($fakultasId) {
            $query->where('fakultas_id', $fakultasId);
        }

        return $query->orderBy('nama_prodi')
            ->get()
            ->map(fn($prodi) => [
                'id' => $prodi->id,
                'label' => $prodi->nama_prodi,
                'fakultas_id' => $prodi->fakultas_id,
            ]);
    }

    /**
     * Dapatkan list fakultas untuk dropdown
     */
    protected function getListFakultas(): Collection
    {
        return RefFakultas::orderBy('nama_fakultas')
            ->get()
            ->map(fn($fak) => [
                'id' => $fak->id,
                'label' => $fak->nama_fakultas,
            ]);
    }

    /**
     * Dapatkan list angkatan untuk dropdown
     */
    protected function getListAngkatan(): Collection
    {
        return RefAngkatan::orderByDesc('id_tahun')
            ->get()
            ->map(fn($ang) => [
                'id' => $ang->id_tahun,
                'label' => "Angkatan {$ang->id_tahun}",
            ]);
    }

    /**
     * Validasi filter parameters
     */
    protected function validateFilterParams(array $filters): void
    {
        if (empty($filters['tahun_akademik_id'])) {
            throw new \InvalidArgumentException('Tahun akademik harus dipilih');
        }

        if (!is_numeric($filters['tahun_akademik_id'])) {
            throw new \InvalidArgumentException('ID tahun akademik tidak valid');
        }
    }

    /**
     * Build filter summary string untuk laporan
     */
    protected function buildFilterSummary(array $filters): string
    {
        $parts = [];

        if (!empty($filters['tahun_akademik_id'])) {
            $ta = RefTahunAkademik::find($filters['tahun_akademik_id']);
            if ($ta) {
                $parts[] = "TA: {$ta->nama_tahun}";
            }
        }

        if (!empty($filters['prodi_id'])) {
            $prodi = RefProdi::find($filters['prodi_id']);
            if ($prodi) {
                $parts[] = "Prodi: {$prodi->nama_prodi}";
            }
        }

        if (!empty($filters['angkatan'])) {
            $parts[] = "Angkatan: {$filters['angkatan']}";
        }

        return implode(' | ', $parts);
    }
}