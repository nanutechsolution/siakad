<?php

declare(strict_types=1);

namespace App\Services\LpmSpmi;

use App\Models\LpmStandar;
use Illuminate\Database\Eloquent\Builder;

class StandarMutuService
{
    /**
     * @param  array{kategori?: string, is_active?: bool}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return LpmStandar::query()
            ->withCount('indikators')
            ->when(
                $filters['kategori'] ?? null,
                fn (Builder $query, $value) => $query->where('kategori', $value)
            )
            ->when(
                array_key_exists('is_active', $filters) && $filters['is_active'] !== null,
                fn (Builder $query) => $query->where('is_active', $filters['is_active'])
            )
            ->orderBy('kode_standar');
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)->get()->map(fn (LpmStandar $row) => [
            'kode_standar' => $row->kode_standar,
            'nama_standar' => $row->nama_standar,
            'kategori' => $row->kategori,
            'target_pencapaian' => "{$row->target_pencapaian}{$row->satuan}",
            'versi' => $row->versi,
            'jumlah_indikator' => $row->indikators_count,
            'status' => $row->is_active ? 'Aktif' : 'Non-aktif',
        ]);
    }
}
