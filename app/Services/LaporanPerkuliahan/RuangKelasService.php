<?php

declare(strict_types=1);

namespace App\Services\LaporanPerkuliahan;

use App\Models\RefRuang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RuangKelasService
{
    /**
     * @param  array{tahun_akademik_id?: int}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return RefRuang::query()
            ->select('ref_ruang.*')
            ->selectSub($this->jadwalFor($filters)->selectRaw('COUNT(*)'), 'jumlah_jadwal')
            ->selectSub(
                $this->jadwalFor($filters)->selectRaw(
                    'COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(jk.jam_selesai, jk.jam_mulai))) / 3600, 0)'
                ),
                'total_jam_penggunaan'
            )
            ->orderBy('ref_ruang.nama_ruang');
    }

    private function jadwalFor(array $filters): \Illuminate\Database\Query\Builder
    {
        return DB::table('jadwal_kuliah as jk')
            ->whereColumn('jk.ruang_id', 'ref_ruang.id')
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn ($query, $value) => $query->where('jk.tahun_akademik_id', $value)
            );
    }

    public function prodiPenggunaRuang(int $ruangId, array $filters = []): string
    {
        return DB::table('jadwal_kuliah as jk')
            ->join('kelas as k', 'k.id', '=', 'jk.kelas_id')
            ->join('ref_prodi as rp', 'rp.id', '=', 'k.prodi_id')
            ->where('jk.ruang_id', $ruangId)
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn ($query, $value) => $query->where('jk.tahun_akademik_id', $value)
            )
            ->distinct()
            ->pluck('rp.nama_prodi')
            ->implode(', ');
    }

    public function mataKuliahPenggunaRuang(int $ruangId, array $filters = []): string
    {
        return DB::table('jadwal_kuliah as jk')
            ->join('master_mata_kuliahs as mk', 'mk.id', '=', 'jk.mata_kuliah_id')
            ->where('jk.ruang_id', $ruangId)
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn ($query, $value) => $query->where('jk.tahun_akademik_id', $value)
            )
            ->distinct()
            ->pluck('mk.nama_mk')
            ->implode(', ');
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)->get()->map(fn ($row) => [
            'nama_ruang' => $row->nama_ruang,
            'kapasitas' => $row->kapasitas,
            'jumlah_jadwal' => $row->jumlah_jadwal,
            'total_jam_penggunaan' => round((float) $row->total_jam_penggunaan, 2),
            'prodi' => $this->prodiPenggunaRuang($row->id, $filters),
            'mata_kuliah' => $this->mataKuliahPenggunaRuang($row->id, $filters),
        ]);
    }
}