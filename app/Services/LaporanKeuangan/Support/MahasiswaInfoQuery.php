<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Query dasar identitas mahasiswa yang dipakai berulang di seluruh laporan.
 * Alias tabel baku dipakai konsisten: m = mahasiswas, p = ref_person,
 * pr = ref_prodi, f = ref_fakultas.
 */
final class MahasiswaInfoQuery
{
    public static function base(): Builder
    {
        return DB::table('mahasiswas as m')
            ->join('ref_person as p', 'p.id', '=', 'm.person_id')
            ->join('ref_prodi as pr', 'pr.id', '=', 'm.prodi_id')
            ->join('ref_fakultas as f', 'f.id', '=', 'pr.fakultas_id')
            ->whereNull('m.deleted_at');
    }

    public static function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['fakultas_id'] ?? null, fn(Builder $q, $v) => $q->where('f.id', $v))
            ->when($filters['prodi_id'] ?? null, fn(Builder $q, $v) => $q->where('pr.id', $v))
            ->when($filters['angkatan_id'] ?? null, fn(Builder $q, $v) => $q->where('m.angkatan_id', $v));
    }
}
