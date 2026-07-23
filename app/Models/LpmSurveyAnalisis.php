<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmSurveyAnalisis extends Model
{
    protected $table = 'lpm_survey_analisis';

    protected $fillable = [
        'kelompok_id',
        'tahun_akademik_id',
        'unit_kerja_id',
        'rata_rata_skor',
        'kesimpulan',
        'rencana_perbaikan',
        'disusun_oleh_person_id',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'rata_rata_skor' => 'decimal:2',
    ];

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(LpmKuisionerKelompok::class, 'kelompok_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(LpmUnitKerja::class, 'unit_kerja_id');
    }

    public function disusunOleh(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'disusun_oleh_person_id');
    }
}
