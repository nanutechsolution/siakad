<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAkreditasiIndikator extends Model
{
    protected $table = 'lpm_akreditasi_indikators';

    protected $fillable = [
        'elemen_id',
        'deskripsi',
        'bobot',
        'indikator_siakad_id',
    ];

    public function elemen(): BelongsTo
    {
        return $this->belongsTo(LpmAkreditasiElemen::class, 'elemen_id');
    }

    public function indikatorSiakad(): BelongsTo
    {
        return $this->belongsTo(LpmIndikator::class, 'indikator_siakad_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(LpmAkreditasiEvidence::class, 'indikator_id');
    }
}
