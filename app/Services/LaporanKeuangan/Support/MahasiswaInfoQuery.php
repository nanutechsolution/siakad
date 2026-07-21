<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan\Support;

use App\Models\LaporanKeuangan\MahasiswaRecord;
use Illuminate\Database\Eloquent\Builder;

/**
 * Query dasar identitas mahasiswa, sekarang beranchor pada Eloquent Model
 * (MahasiswaRecord) alih-alih DB::table() murni — supaya hasil akhirnya
 * (setelah di-join berlapis oleh Service) tetap berupa Eloquent Builder
 * yang bisa dipaginate native oleh Filament (->paginate() dengan
 * LIMIT/OFFSET di level database).
 *
 * ->from('mahasiswas as m') dipakai (bukan default table Model) supaya
 * SEMUA query lain di codebase yang sudah pakai alias 'm.', 'p.', 'pr.',
 * 'f.' tetap jalan tanpa perlu ditulis ulang.
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
