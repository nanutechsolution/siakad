<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmBenchmark extends Model
{
    protected $fillable = [
        'indikator_id',
        'institusi_pembanding_id',
        'tahun',
        'nilai_internal',
        'nilai_eksternal',
        'analisis_gap',
        'sumber_data',
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(LpmIndikator::class, 'indikator_id');
    }

    public function institusiPembanding(): BelongsTo
    {
        return $this->belongsTo(LpmBenchmarkInstitusi::class, 'institusi_pembanding_id');
    }

    /**
     * Selisih nilai internal terhadap eksternal. Positif berarti unggul,
     * negatif berarti tertinggal dari institusi pembanding.
     */
    public function gap(): ?float
    {
        if ($this->nilai_internal === null || $this->nilai_eksternal === null) {
            return null;
        }

        return round((float) $this->nilai_internal - (float) $this->nilai_eksternal, 2);
    }
}
