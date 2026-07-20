<?php

declare(strict_types=1);

namespace App\Services\LpmSpmi;

use App\Models\LpmIkuTarget;
use Illuminate\Database\Eloquent\Builder;

class CapaianPembelajaranService
{
    /**
     * @param  array{tahun?: int, prodi_id?: int, standar_id?: int}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return LpmIkuTarget::query()
            ->with(['indikator.standar', 'prodi'])
            ->when(
                $filters['tahun'] ?? null,
                fn (Builder $query, $value) => $query->where('tahun', $value)
            )
            ->when(
                $filters['prodi_id'] ?? null,
                fn (Builder $query, $value) => $query->where('prodi_id', $value)
            )
            ->when(
                $filters['standar_id'] ?? null,
                fn (Builder $query, $value) => $query->whereHas(
                    'indikator',
                    fn (Builder $q) => $q->where('standar_id', $value)
                )
            )
            ->orderByDesc('tahun');
    }

    public static function persenCapaian(object $row): float
    {
        $target = (float) $row->target_nilai;

        if ($target <= 0.0) {
            return 0.0;
        }

        return round(((float) $row->capaian_nilai / $target) * 100, 2);
    }

    public static function status(float $persenCapaian): string
    {
        return match (true) {
            $persenCapaian >= 100 => 'Tercapai',
            $persenCapaian >= 75 => 'Mendekati Target',
            default => 'Belum Tercapai',
        };
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)->get()->map(function (LpmIkuTarget $row) {
            $persen = self::persenCapaian($row);

            return [
                'kode_indikator' => $row->indikator?->kode_indikator,
                'nama_indikator' => $row->indikator?->nama_indikator,
                'standar' => $row->indikator?->standar?->nama_standar,
                'prodi' => $row->prodi?->nama_prodi ?? 'Institusi',
                'tahun' => $row->tahun,
                'target_nilai' => $row->target_nilai,
                'capaian_nilai' => $row->capaian_nilai,
                'persen_capaian' => $persen,
                'status' => self::status($persen),
            ];
        });
    }
}
