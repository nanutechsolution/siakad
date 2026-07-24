<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmAkreditasiEvidence extends Model
{
    protected $table = 'lpm_akreditasi_evidences';

    protected $fillable = [
        'elemen_id',
        'indikator_id',
        'file_path',
        'keterangan',
        'uploaded_by_person_id',
    ];

    public function elemen(): BelongsTo
    {
        return $this->belongsTo(LpmAkreditasiElemen::class, 'elemen_id');
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(LpmAkreditasiIndikator::class, 'indikator_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'uploaded_by_person_id');
    }
}
