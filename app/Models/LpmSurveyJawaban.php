<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmSurveyJawaban extends Model
{
    // Definisikan nama tabel secara eksplisit
    protected $table = 'lpm_survey_jawaban';

    // Kolom yang diizinkan untuk mass-assignment
    protected $fillable = [
        'mahasiswa_id',
        'pertanyaan_id',
        'tahun_akademik_id',
        'jawaban_nilai',
    ];

    /**
     * Relasi balik ke Mahasiswa yang mengisi
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Relasi ke Pertanyaan yang dijawab
     */
    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(LpmKuisionerPertanyaan::class, 'pertanyaan_id');
    }

    /**
     * Relasi ke Tahun Akademik saat survey diisi
     */
    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }
}
