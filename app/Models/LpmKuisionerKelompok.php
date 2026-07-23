<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class LpmKuisionerKelompok extends Model
{
    use HasFactory;

    protected $table = 'lpm_kuisioner_kelompok';
    protected $fillable = [
        'tahun_akademik_id',
        'nama_kelompok',
        'kategori',
        'urutan',
        'is_active',
    ];
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }
    public function pertanyaans(): HasMany
    {
        return $this->hasMany(LpmKuisionerPertanyaan::class, 'kelompok_id')->orderBy('urutan');
    }

    public function respondens(): HasMany
    {
        return $this->hasMany(LpmSurveyResponden::class, 'kelompok_id');
    }
    /**
     * Jawaban mahasiswa (kategori KEPUASAN_MAHASISWA) lewat lpm_survey_jawaban.
     */
    public function jawabanMahasiswas(): HasManyThrough
    {
        return $this->hasManyThrough(
            LpmSurveyJawaban::class,
            LpmKuisionerPertanyaan::class,
            'kelompok_id',
            'pertanyaan_id',
            'id',
            'id'
        );
    }

    /**
     * Jawaban Dosen/Tendik/Alumni/Pengguna Lulusan lewat lpm_survey_jawaban_pihak.
     */
    public function jawabanPihaks(): HasManyThrough
    {
        return $this->hasManyThrough(
            LpmSurveyJawabanPihak::class,
            LpmKuisionerPertanyaan::class,
            'kelompok_id',
            'pertanyaan_id',
            'id',
            'id'
        );
    }
}
