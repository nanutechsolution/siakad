<?php

declare(strict_types=1);

namespace App\Services\LpmSpmi;

use App\Models\LpmAmiFinding;
use Illuminate\Database\Eloquent\Builder;

class AuditMutuInternalService
{
    /**
     * @param  array{periode_id?: int, prodi_id?: int, klasifikasi?: string, status_workflow?: string}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return LpmAmiFinding::query()
            ->with(['periode', 'prodi', 'standar'])
            ->when(
                $filters['periode_id'] ?? null,
                fn (Builder $query, $value) => $query->where('periode_id', $value)
            )
            ->when(
                $filters['prodi_id'] ?? null,
                fn (Builder $query, $value) => $query->where('prodi_id', $value)
            )
            ->when(
                $filters['klasifikasi'] ?? null,
                fn (Builder $query, $value) => $query->where('klasifikasi', $value)
            )
            ->when(
                $filters['status_workflow'] ?? null,
                fn (Builder $query, $value) => $query->where('status_workflow', $value)
            )
            ->orderByDesc('created_at');
    }

    /**
     * @return array{total_temuan: int, kts_mayor: int, kts_minor: int, observasi: int, closed: int, open: int}
     */
    public function summary(array $filters = []): array
    {
        $rows = $this->query($filters)->get();

        return [
            'total_temuan' => $rows->count(),
            'kts_mayor' => $rows->where('klasifikasi', 'KTS_MAYOR')->count(),
            'kts_minor' => $rows->where('klasifikasi', 'KTS_MINOR')->count(),
            'observasi' => $rows->where('klasifikasi', 'OB')->count(),
            'closed' => $rows->where('is_closed', true)->count(),
            'open' => $rows->where('is_closed', false)->count(),
        ];
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)->get()->map(fn (LpmAmiFinding $row) => [
            'periode' => $row->periode?->nama_periode,
            'prodi' => $row->prodi?->nama_prodi,
            'standar' => $row->standar?->nama_standar,
            'klasifikasi' => $row->klasifikasi,
            'jenis_temuan' => $row->jenis_temuan,
            'auditor' => $row->auditor_name,
            'deskripsi_temuan' => $row->deskripsi_temuan,
            'status_workflow' => $row->status_workflow,
            'deadline_perbaikan' => optional($row->deadline_perbaikan)->format('d/m/Y'),
            'status' => $row->is_closed ? 'Closed' : 'Open',
        ]);
    }
}
