<?php

declare(strict_types=1);

namespace App\Services\LpmSpmi;

use App\Models\LpmSurveyJawaban;
use Illuminate\Database\Eloquent\Builder;

/**
 * NOTE: Survey kepuasan mahasiswa diasumsikan memakai lpm_kuisioner_kelompok
 * dengan kategori selain 'EDOM' (mis. 'KEPUASAN') dan jawabannya tersimpan di
 * lpm_survey_jawaban. Silakan sesuaikan nilai kategori jika berbeda di data Anda.
 *
 * Query dasar dibangun dari model Eloquent LpmSurveyJawaban agar kompatibel
 * dengan Filament Table yang mensyaratkan Illuminate\Database\Eloquent\Builder.
 */
class KepuasanMahasiswaService
{
    public const KATEGORI = 'KEPUASAN';

    /**
     * @param  array{tahun_akademik_id?: int, kelompok_id?: int}  $filters
     */
    public function query(array $filters = []): Builder
    {
        return LpmSurveyJawaban::query()
            ->from('lpm_survey_jawaban as sj')
            ->join('lpm_kuisioner_pertanyaan as kp', 'kp.id', '=', 'sj.pertanyaan_id')
            ->join('lpm_kuisioner_kelompok as kk', 'kk.id', '=', 'kp.kelompok_id')
            ->where('kk.kategori', self::KATEGORI)
            ->select([
                'kk.nama_kelompok',
                'kp.id as pertanyaan_id',
                'kp.bunyi_pertanyaan',
                'kp.jenis_input',
            ])
            ->selectRaw('COUNT(sj.id) as jumlah_responden')
            ->selectRaw("ROUND(AVG(CASE WHEN kp.jenis_input LIKE 'RATING%' AND sj.jawaban_nilai REGEXP '^[0-9]+(\\\\.[0-9]+)?$' THEN CAST(sj.jawaban_nilai AS DECIMAL(10,2)) END), 2) as rata_rata_skor")
            ->groupBy('kk.nama_kelompok', 'kp.id', 'kp.bunyi_pertanyaan', 'kp.jenis_input')
            ->when(
                $filters['tahun_akademik_id'] ?? null,
                fn (Builder $query, $value) => $query->where('sj.tahun_akademik_id', $value)
            )
            ->when(
                $filters['kelompok_id'] ?? null,
                fn (Builder $query, $value) => $query->where('kk.id', $value)
            )
            ->orderBy('kk.nama_kelompok')
            ->orderBy('kp.urutan');
    }

    public function exportRows(array $filters = []): \Illuminate\Support\Collection
    {
        return collect($this->query($filters)->get())->map(fn ($row) => [
            'kelompok' => $row->nama_kelompok,
            'pertanyaan' => $row->bunyi_pertanyaan,
            'jumlah_responden' => $row->jumlah_responden,
            'rata_rata_skor' => $row->rata_rata_skor,
        ]);
    }
}
