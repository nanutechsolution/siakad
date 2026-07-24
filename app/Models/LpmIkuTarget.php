<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmIkuTarget extends Model
{
    protected $fillable = [
        'indikator_id',
        'prodi_id',
        'unit_kerja_id',
        'tahun',
        'target_nilai',
        'capaian_nilai',
        'file_bukti_path',
        'status',
        'verified_by',
        'analisis_kendala',
        'tindakan_koreksi',
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(LpmIndikator::class, 'indikator_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(LpmUnitKerja::class, 'unit_kerja_id');
    }
}
