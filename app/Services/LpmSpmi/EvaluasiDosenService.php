<?php

declare(strict_types=1);

namespace App\Services\LpmSpmi;

use App\Models\EdomProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * NOTE: Service ini mengasumsikan tabel-tabel LPM sudah ada persis seperti pada
 * schema (lpm_edom_progress, lpm_edom_jawaban, lpm_edom_saran,
 * lpm_kuisioner_pertanyaan, lpm_kuisioner_kelompok) dan kelompok kuisioner untuk
 * EDOM memakai kategori = 'EDOM'. Rata-rata nilai hanya dihitung dari pertanyaan
 * bertipe rating (jenis_input LIKE 'RATING%'), karena ESSAY/BOOLEAN tidak
 * relevan untuk agregasi skor numerik.
 *
 * Query dasar dibangun dari model Eloquent LpmEdomProgress (bukan
 * Illuminate\Database\Query\Builder murni) karena Filament Table mensyaratkan
 * Illuminate\Database\Eloquent\Builder pada method ->query().
 */
class EvaluasiDosenService
{
    /**
     * @param  array{tahun_akademik_id?: int, prodi_id?: int, dosen_id?: string, mata_kuliah_id?: int}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return EdomProgress::query()
            ->from('lpm_edom_progress as ep')
            ->join('jadwal_kuliah as jk', 'jk.id', '=', 'ep.jadwal_kuliah_id')
            ->join('master_mata_kuliahs as mk', 'mk.id', '=', 'jk.mata_kuliah_id')
            ->join('kelas as k', 'k.id', '=', 'jk.kelas_id')
            ->join('trx_dosen as td', 'td.id', '=', 'ep.dosen_id')
            ->join('ref_person as p', 'p.id', '=', 'td.person_id')
            ->select([
                'ep.dosen_id',
                'ep.jadwal_kuliah_id',
                'p.nama_lengkap as nama_dosen',
                'mk.kode_mk',
                'mk.nama_mk',
                'k.nama_kelas',
            ])
            ->selectRaw('COUNT(DISTINCT CASE WHEN ep.is_completed = 1 THEN ep.mahasiswa_id END) as jumlah_responden')
            ->selectSub(
                DB::table('krs_detail as kd')
                    ->whereColumn('kd.jadwal_kuliah_id', 'ep.jadwal_kuliah_id')
                    ->selectRaw('COUNT(*)'),
                'total_mahasiswa_kelas'
            )
            ->selectSub($this->rataRataNilai(), 'rata_rata_nilai')
            ->selectSub(
                DB::table('lpm_edom_saran as es')
                    ->whereColumn('es.jadwal_kuliah_id', 'ep.jadwal_kuliah_id')
                    ->whereColumn('es.dosen_id', 'ep.dosen_id')
                    ->selectRaw('COUNT(*)'),
                'jumlah_saran'
            )
            ->groupBy('ep.dosen_id', 'ep.jadwal_kuliah_id', 'p.nama_lengkap', 'mk.kode_mk', 'mk.nama_mk', 'k.nama_kelas')
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn(Builder $query, $value) => $query->where('jk.tahun_akademik_id', $value)
            )
            ->when(
                $filters['prodi_id'] ?? null,
                fn(Builder $query, $value) => $query->where('k.prodi_id', $value)
            )
            ->when(
                $filters['dosen_id'] ?? null,
                fn(Builder $query, $value) => $query->where('ep.dosen_id', $value)
            )
            ->when(
                $filters['mata_kuliah_id'] ?? null,
                fn(Builder $query, $value) => $query->where('jk.mata_kuliah_id', $value)
            )
            ->orderBy('p.nama_lengkap');
    }

    private function rataRataNilai(): QueryBuilder
    {
        return DB::table('lpm_edom_jawaban as ej')
            ->join('lpm_kuisioner_pertanyaan as kp', 'kp.id', '=', 'ej.pertanyaan_id')
            ->whereColumn('ej.jadwal_kuliah_id', 'ep.jadwal_kuliah_id')
            ->whereColumn('ej.dosen_id', 'ep.dosen_id')
            ->where('kp.jenis_input', 'like', 'RATING%')
            ->whereRaw("ej.jawaban_nilai REGEXP '^[0-9]+(\\\\.[0-9]+)?$'")
            ->selectRaw('ROUND(AVG(CAST(ej.jawaban_nilai AS DECIMAL(10,2))), 2)');
    }

    public static function responseRate(object $row): float
    {
        if ((int) $row->total_mahasiswa_kelas === 0) {
            return 0.0;
        }

        return round(((int) $row->jumlah_responden / (int) $row->total_mahasiswa_kelas) * 100, 2);
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return collect($this->query($filters)->get())->map(fn($row) => [
            'nama_dosen' => $row->nama_dosen,
            'mata_kuliah' => "{$row->kode_mk} - {$row->nama_mk} ({$row->nama_kelas})",
            'jumlah_responden' => $row->jumlah_responden,
            'total_mahasiswa_kelas' => $row->total_mahasiswa_kelas,
            'response_rate' => self::responseRate($row),
            'rata_rata_nilai' => $row->rata_rata_nilai,
            'jumlah_saran' => $row->jumlah_saran,
        ]);
    }
}
