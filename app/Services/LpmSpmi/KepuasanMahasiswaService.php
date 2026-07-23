<?php

declare(strict_types=1);

namespace App\Services\LpmSpmi;

use App\Models\LpmSurveyJawaban;
use App\Models\LpmSurveyJawabanPihak;
use Illuminate\Database\Eloquent\Builder;

/**
 * NOTE: kelompok kuisioner dibedakan lewat kolom `kategori` (varchar) di
 * lpm_kuisioner_kelompok — kolom ini SUDAH ADA di schema (default 'EDOM'),
 * bukan hasil migration baru.
 *
 * Sumber jawaban berbeda tergantung kategori:
 * - KEPUASAN_MAHASISWA → tabel lpm_survey_jawaban (sudah ada, terkunci ke
 *   mahasiswa_id).
 * - KEPUASAN_DOSEN / KEPUASAN_TENDIK / KEPUASAN_ALUMNI /
 *   KEPUASAN_PENGGUNA_LULUSAN → tabel baru lpm_survey_jawaban_pihak.
 */
class KepuasanMahasiswaService
{
    public const DEFAULT_KATEGORI = 'KEPUASAN_MAHASISWA';

    /**
     * @param  array{tahun_akademik_id?: int, kategori?: string, kelompok_id?: int}  $filters
     */
    public function query(array $filters = []): Builder
    {
        $kategori = $filters['kategori'] ?? self::DEFAULT_KATEGORI;

        return $kategori === 'KEPUASAN_MAHASISWA'
            ? $this->queryMahasiswa($filters, $kategori)
            : $this->queryPihak($filters, $kategori);
    }

    private function queryMahasiswa(array $filters, string $kategori): Builder
    {
        return LpmSurveyJawaban::query()
            ->from('lpm_survey_jawaban as sj')
            ->join('lpm_kuisioner_pertanyaan as kp', 'kp.id', '=', 'sj.pertanyaan_id')
            ->join('lpm_kuisioner_kelompok as kk', 'kk.id', '=', 'kp.kelompok_id')
            ->where('kk.kategori', $kategori)
            ->select([
                'kk.nama_kelompok',
                'kp.id as pertanyaan_id',
                'kp.bunyi_pertanyaan',
                'kp.jenis_input',
            ])
            ->selectRaw('COUNT(sj.id) as jumlah_responden')
            ->selectRaw($this->rataRataSql('sj'))
            ->groupBy('kk.nama_kelompok', 'kp.id', 'kp.bunyi_pertanyaan', 'kp.jenis_input')
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn(Builder $query, $value) => $query->where('sj.tahun_akademik_id', $value)
            )
            ->when(
                $filters['kelompok_id'] ?? null,
                fn(Builder $query, $value) => $query->where('kk.id', $value)
            )
            ->orderBy('kk.nama_kelompok')
            ->orderBy('kp.urutan');
    }

    private function queryPihak(array $filters, string $kategori): Builder
    {
        return LpmSurveyJawabanPihak::query()
            ->from('lpm_survey_jawaban_pihak as sjp')
            ->join('lpm_kuisioner_pertanyaan as kp', 'kp.id', '=', 'sjp.pertanyaan_id')
            ->join('lpm_kuisioner_kelompok as kk', 'kk.id', '=', 'kp.kelompok_id')
            ->where('kk.kategori', $kategori)
            ->select([
                'kk.nama_kelompok',
                'kp.id as pertanyaan_id',
                'kp.bunyi_pertanyaan',
                'kp.jenis_input',
            ])
            ->selectRaw('COUNT(sjp.id) as jumlah_responden')
            ->selectRaw($this->rataRataSql('sjp'))
            ->groupBy('kk.nama_kelompok', 'kp.id', 'kp.bunyi_pertanyaan', 'kp.jenis_input')
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn(Builder $query, $value) => $query->where('sjp.tahun_akademik_id', $value)
            )
            ->when(
                $filters['kelompok_id'] ?? null,
                fn(Builder $query, $value) => $query->where('kk.id', $value)
            )
            ->orderBy('kk.nama_kelompok')
            ->orderBy('kp.urutan');
    }

    private function rataRataSql(string $alias): string
    {
        return "ROUND(AVG(CASE WHEN kp.jenis_input LIKE 'RATING%' AND {$alias}.jawaban_nilai REGEXP '^[0-9]+(\\\\.[0-9]+)?$' THEN CAST({$alias}.jawaban_nilai AS DECIMAL(10,2)) END), 2) as rata_rata_skor";
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return collect($this->query($filters)->get())->map(fn($row) => [
            'kelompok' => $row->nama_kelompok,
            'pertanyaan' => $row->bunyi_pertanyaan,
            'jumlah_responden' => $row->jumlah_responden,
            'rata_rata_skor' => $row->rata_rata_skor,
        ]);
    }
}
