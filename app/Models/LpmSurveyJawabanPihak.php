<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmSurveyJawabanPihak extends Model
{
    protected $table = 'lpm_survey_jawaban_pihak';

    protected $fillable = [
        'jenis_responden',
        'person_id',
        'nama_eksternal',
        'instansi_eksternal',
        'pertanyaan_id',
        'tahun_akademik_id',
        'jawaban_nilai',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(LpmKuisionerPertanyaan::class, 'pertanyaan_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    public function namaTampilan(): string
    {
        return $this->person?->nama_lengkap ?? $this->nama_eksternal ?? '-';
    }
}
