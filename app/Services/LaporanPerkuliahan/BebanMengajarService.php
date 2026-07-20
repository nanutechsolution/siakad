<?php

declare(strict_types=1);

namespace App\Services\LaporanPerkuliahan;

use App\Models\TrxDosen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class BebanMengajarService
{
    /**
     * @param  array{tahun_akademik_id?: int, semester?: int, fakultas_id?: int, prodi_id?: int}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return TrxDosen::query()
            ->select('trx_dosen.*')
            ->selectSub($this->filteredAssignments($filters)->selectRaw('COUNT(DISTINCT jk.mata_kuliah_id)'), 'jumlah_mata_kuliah')
            ->selectSub($this->filteredAssignments($filters)->selectRaw('COALESCE(SUM(mk.sks_default), 0)'), 'total_sks')
            ->selectSub($this->filteredAssignments($filters)->selectRaw('COUNT(DISTINCT jkd.jadwal_kuliah_id)'), 'jumlah_kelas')
            ->selectSub($this->filteredAssignments($filters)->selectRaw('COALESCE(SUM(jk.isi_kelas), 0)'), 'jumlah_mahasiswa')
            ->whereExists(function ($query) use ($filters) {
                $query->selectRaw('1')
                    ->from('jadwal_kuliah_dosen as jkd')
                    ->join('jadwal_kuliah as jk', 'jk.id', '=', 'jkd.jadwal_kuliah_id')
                    ->join('master_mata_kuliahs as mk', 'mk.id', '=', 'jk.mata_kuliah_id')
                    ->join('kelas as k', 'k.id', '=', 'jk.kelas_id')
                    ->whereColumn('jkd.dosen_id', 'trx_dosen.id')
                    ->when(
                        $filters['tahun_akademik_id'] ?? null,
                        fn($q, $v) => $q->where('jk.tahun_akademik_id', $v)
                    );
            })
            ->when(
                $filters['prodi_id'] ?? null,
                fn(Builder $query, $value) => $query->where('trx_dosen.prodi_id', $value)
            )
            ->when(
                $filters['fakultas_id'] ?? null,
                fn(Builder $query, $value) => $query->whereHas(
                    'prodi',
                    fn(Builder $q) => $q->where('fakultas_id', $value)
                )
            )
            ->orderByDesc('total_sks');
    }

    /**
     * Base subquery: seluruh penugasan mengajar (jadwal_kuliah_dosen) milik satu dosen,
     * dikorelasikan lewat whereColumn ke trx_dosen.id, dan sudah difilter tahun
     * akademik / semester / prodi / fakultas.
     */
    private function filteredAssignments(array $filters): QueryBuilder
    {
        return DB::table('jadwal_kuliah_dosen as jkd')
            ->join('jadwal_kuliah as jk', 'jk.id', '=', 'jkd.jadwal_kuliah_id')
            ->join('master_mata_kuliahs as mk', 'mk.id', '=', 'jk.mata_kuliah_id')
            ->join('kelas as k', 'k.id', '=', 'jk.kelas_id')
            ->whereColumn('jkd.dosen_id', 'trx_dosen.id')
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn(QueryBuilder $query, $value) => $query->where('jk.tahun_akademik_id', $value)
            )
            ->when(
                $filters['semester'] ?? null,
                fn(QueryBuilder $query, $value) => $query->whereIn('jk.tahun_akademik_id', function ($sub) use ($value) {
                    $sub->select('id')->from('ref_tahun_akademik')->where('semester', $value);
                })
            )
            ->when(
                $filters['prodi_id'] ?? null,
                fn(QueryBuilder $query, $value) => $query->where('k.prodi_id', $value)
            )
            ->when(
                $filters['fakultas_id'] ?? null,
                fn(QueryBuilder $query, $value) => $query->whereIn('k.prodi_id', function ($sub) use ($value) {
                    $sub->select('id')->from('ref_prodi')->where('fakultas_id', $value);
                })
            );
    }

    /**
     * @return array{total_dosen: int, total_sks: int}
     */
    public function summary(array $filters = []): array
    {
        $rows = $this->query($filters)->get();

        return [
            'total_dosen' => $rows->count(),
            'total_sks' => (int) $rows->sum('total_sks'),
        ];
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)->get()->map(fn($row) => [
            'nidn' => $row->nidn,
            'nama_dosen' => $row->person?->nama_lengkap,
            'jumlah_mata_kuliah' => $row->jumlah_mata_kuliah,
            'total_sks' => $row->total_sks,
            'jumlah_kelas' => $row->jumlah_kelas,
            'jumlah_mahasiswa' => $row->jumlah_mahasiswa,
        ]);
    }
}
