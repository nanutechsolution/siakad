<?php

declare(strict_types=1);

namespace App\Services\LaporanPerkuliahan;

use App\Models\JadwalKuliahDosen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class PresensiDosenService
{
    /**
     * @param  array{tahun_akademik_id?: int, semester?: int, prodi_id?: int, dosen_id?: string, mata_kuliah_id?: int}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return JadwalKuliahDosen::query()
            ->join('jadwal_kuliah as jk', 'jk.id', '=', 'jadwal_kuliah_dosen.jadwal_kuliah_id')
            ->join('master_mata_kuliahs as mk', 'mk.id', '=', 'jk.mata_kuliah_id')
            ->join('trx_dosen as td', 'td.id', '=', 'jadwal_kuliah_dosen.dosen_id')
            ->join('ref_person as p', 'p.id', '=', 'td.person_id')
            ->join('kelas as k', 'k.id', '=', 'jk.kelas_id')
            ->select([
                'jadwal_kuliah_dosen.id',
                'p.nama_lengkap as nama_dosen',
                'mk.kode_mk',
                'mk.nama_mk',
                'k.nama_kelas',
                'jadwal_kuliah_dosen.rencana_tatap_muka as jumlah_pertemuan',
            ])
            ->selectSub(
                DB::table('perkuliahan_sesi as ps')
                    ->whereColumn('ps.jadwal_kuliah_id', 'jk.id')
                    ->where('ps.status_sesi', 'selesai')
                    ->selectRaw('COUNT(*)'),
                'terlaksana'
            )
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn (Builder $query, $value) => $query->where('jk.tahun_akademik_id', $value)
            )
            ->when(
                $filters['semester'] ?? null,
                fn (Builder $query, $value) => $query->whereIn('jk.tahun_akademik_id', function (QueryBuilder $sub) use ($value) {
                    $sub->select('id')->from('ref_tahun_akademik')->where('semester', $value);
                })
            )
            ->when(
                $filters['prodi_id'] ?? null,
                fn (Builder $query, $value) => $query->where('k.prodi_id', $value)
            )
            ->when(
                $filters['dosen_id'] ?? null,
                fn (Builder $query, $value) => $query->where('jadwal_kuliah_dosen.dosen_id', $value)
            )
            ->when(
                $filters['mata_kuliah_id'] ?? null,
                fn (Builder $query, $value) => $query->where('jk.mata_kuliah_id', $value)
            )
            ->orderBy('p.nama_lengkap');
    }

    public static function tidakTerlaksana(object $row): int
    {
        return max(0, (int) $row->jumlah_pertemuan - (int) $row->terlaksana);
    }

    public static function persentase(object $row): float
    {
        if ((int) $row->jumlah_pertemuan === 0) {
            return 0.0;
        }

        return round(((int) $row->terlaksana / (int) $row->jumlah_pertemuan) * 100, 2);
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)->get()->map(fn ($row) => [
            'nama_dosen' => $row->nama_dosen,
            'mata_kuliah' => "{$row->kode_mk} - {$row->nama_mk} ({$row->nama_kelas})",
            'jumlah_pertemuan' => $row->jumlah_pertemuan,
            'terlaksana' => $row->terlaksana,
            'tidak_terlaksana' => self::tidakTerlaksana($row),
            'persentase' => self::persentase($row),
        ]);
    }
}