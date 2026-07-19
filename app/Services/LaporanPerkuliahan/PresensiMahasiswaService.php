<?php

declare(strict_types=1);

namespace App\Services\LaporanPerkuliahan;

use App\Models\KrsDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class PresensiMahasiswaService
{
    /** Batas persentase kehadiran dianggap Aman. */
    public const THRESHOLD_AMAN = 80.0;

    /** Batas persentase kehadiran dianggap Peringatan (di bawah ini = Tidak Memenuhi Syarat). */
    public const THRESHOLD_PERINGATAN = 75.0;

    /**
     * @param  array{tahun_akademik_id?: int, semester?: int, prodi_id?: int, mata_kuliah_id?: int, dosen_id?: string}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return KrsDetail::query()
            ->join('krs', 'krs.id', '=', 'krs_detail.krs_id')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'krs.mahasiswa_id')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('jadwal_kuliah as jk', 'jk.id', '=', 'krs_detail.jadwal_kuliah_id')
            ->join('master_mata_kuliahs as mk', 'mk.id', '=', 'jk.mata_kuliah_id')
            ->select([
                'krs_detail.id',
                'mahasiswas.nim',
                'ref_person.nama_lengkap as nama_mahasiswa',
                'mk.nama_mk',
                'mk.kode_mk',
            ])
            ->selectSub($this->sesiTerlaksana(), 'total_pertemuan')
            ->selectSub($this->absensiCount('H'), 'hadir')
            ->selectSub($this->absensiCount('I'), 'izin')
            ->selectSub($this->absensiCount('S'), 'sakit')
            ->selectSub($this->absensiCount('A'), 'alpha')
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
                fn (Builder $query, $value) => $query->where('mahasiswas.prodi_id', $value)
            )
            ->when(
                $filters['mata_kuliah_id'] ?? null,
                fn (Builder $query, $value) => $query->where('jk.mata_kuliah_id', $value)
            )
            ->when(
                $filters['dosen_id'] ?? null,
                fn (Builder $query, $value) => $query->whereIn('jk.id', function (QueryBuilder $sub) use ($value) {
                    $sub->select('jadwal_kuliah_id')->from('jadwal_kuliah_dosen')->where('dosen_id', $value);
                })
            )
            ->orderBy('mahasiswas.nim');
    }

    private function sesiTerlaksana(): QueryBuilder
    {
        return DB::table('perkuliahan_sesi as ps')
            ->whereColumn('ps.jadwal_kuliah_id', 'krs_detail.jadwal_kuliah_id')
            ->where('ps.status_sesi', 'selesai')
            ->selectRaw('COUNT(*)');
    }

    private function absensiCount(string $statusKehadiran): QueryBuilder
    {
        return DB::table('perkuliahan_absensi as pa')
            ->whereColumn('pa.krs_detail_id', 'krs_detail.id')
            ->where('pa.status_kehadiran', $statusKehadiran)
            ->selectRaw('COUNT(*)');
    }

    public static function persentase(object $row): float
    {
        if ((int) $row->total_pertemuan === 0) {
            return 0.0;
        }

        return round(((int) $row->hadir / (int) $row->total_pertemuan) * 100, 2);
    }

    public static function status(float $persentase): string
    {
        return match (true) {
            $persentase >= self::THRESHOLD_AMAN => 'Aman',
            $persentase >= self::THRESHOLD_PERINGATAN => 'Peringatan',
            default => 'Tidak Memenuhi Syarat',
        };
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)->get()->map(function ($row) {
            $persentase = self::persentase($row);

            return [
                'nim' => $row->nim,
                'nama_mahasiswa' => $row->nama_mahasiswa,
                'mata_kuliah' => "{$row->kode_mk} - {$row->nama_mk}",
                'total_pertemuan' => $row->total_pertemuan,
                'hadir' => $row->hadir,
                'izin' => $row->izin,
                'sakit' => $row->sakit,
                'alpha' => $row->alpha,
                'persentase_kehadiran' => $persentase,
                'status' => self::status($persentase),
            ];
        });
    }
}