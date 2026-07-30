<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan\Support;

use App\Models\LaporanKeuangan\MahasiswaRecord;
use Illuminate\Database\Eloquent\Builder;

/**
 * Query dasar identitas mahasiswa berjangkar pada Eloquent Model (MahasiswaRecord).
 * 
 * Menggunakan tabel 'mahasiswas' (tanpa alias) beserta JOIN ke ref_person (p),
 * ref_prodi (pr), dan ref_fakultas (f) agar konsisten digunakan oleh seluruh
 * Laporan Keuangan.
 */
final class MahasiswaInfoQuery
{
    public static function base(): Builder
    {
        return MahasiswaRecord::query()
            ->from('mahasiswas')
            ->join('ref_person as p', 'p.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi as pr', 'pr.id', '=', 'mahasiswas.prodi_id')
            ->join('ref_fakultas as f', 'f.id', '=', 'pr.fakultas_id')
            ->whereNull('mahasiswas.deleted_at');
    }

    public static function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                $filters['fakultas_id'] ?? null,
                fn(Builder $q, $v) => $q->where('f.id', $v)
            )
            ->when(
                $filters['prodi_id'] ?? null,
                fn(Builder $q, $v) => $q->where('pr.id', $v)
            )
            ->when(
                $filters['angkatan_id'] ?? null,
                fn(Builder $q, $v) => $q->where('mahasiswas.angkatan_id', $v)
            );
    }
}
