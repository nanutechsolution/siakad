<?php

declare(strict_types=1);

namespace App\Services\LaporanPerkuliahan;

use App\Models\MasterMataKuliah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DistribusiMataKuliahService
{
    /**
     * @param  array{tahun_akademik_id?: int, prodi_id?: int, fakultas_id?: int}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return MasterMataKuliah::query()
            ->select('master_mata_kuliahs.*')
            ->selectSub(
                DB::table('kurikulum_mata_kuliah')
                    ->join('master_kurikulums', 'master_kurikulums.id', '=', 'kurikulum_mata_kuliah.kurikulum_id')
                    ->whereColumn('kurikulum_mata_kuliah.mata_kuliah_id', 'master_mata_kuliahs.id')
                    ->where('master_kurikulums.is_active', true)
                    ->orderByDesc('master_kurikulums.tahun_mulai')
                    ->select('kurikulum_mata_kuliah.semester_paket')
                    ->limit(1),
                'semester_kurikulum'
            )
            ->selectSub(
                $this->jadwalFor($filters)->selectRaw('COUNT(DISTINCT jk.id)'),
                'jumlah_kelas'
            )
            ->selectSub(
                $this->jadwalFor($filters)->selectRaw('COALESCE(SUM(jk.isi_kelas), 0)'),
                'jumlah_peserta'
            )
            ->when(
                $filters['prodi_id'] ?? null,
                fn (Builder $query, $value) => $query->where('master_mata_kuliahs.prodi_id', $value)
            )
            ->when(
                $filters['fakultas_id'] ?? null,
                fn (Builder $query, $value) => $query->whereHas(
                    'prodi',
                    fn (Builder $q) => $q->where('fakultas_id', $value)
                )
            )
            ->orderBy('master_mata_kuliahs.kode_mk');
    }

    private function jadwalFor(array $filters): \Illuminate\Database\Query\Builder
    {
        return DB::table('jadwal_kuliah as jk')
            ->whereColumn('jk.mata_kuliah_id', 'master_mata_kuliahs.id')
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn ($query, $value) => $query->where('jk.tahun_akademik_id', $value)
            );
    }

    public function dosenPengampu(int $mataKuliahId): string
    {
        return DB::table('jadwal_kuliah_dosen as jkd')
            ->join('jadwal_kuliah as jk', 'jk.id', '=', 'jkd.jadwal_kuliah_id')
            ->join('trx_dosen as td', 'td.id', '=', 'jkd.dosen_id')
            ->join('ref_person as p', 'p.id', '=', 'td.person_id')
            ->where('jk.mata_kuliah_id', $mataKuliahId)
            ->distinct()
            ->pluck('p.nama_lengkap')
            ->implode(', ');
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)->get()->map(fn ($row) => [
            'kode_mk' => $row->kode_mk,
            'nama_mk' => $row->nama_mk,
            'sks' => $row->sks_default,
            'semester_kurikulum' => $row->semester_kurikulum,
            'jumlah_kelas' => $row->jumlah_kelas,
            'jumlah_peserta' => $row->jumlah_peserta,
            'dosen_pengampu' => $this->dosenPengampu($row->id),
        ]);
    }
}