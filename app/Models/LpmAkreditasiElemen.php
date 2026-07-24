<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAkreditasiElemen extends Model
{
    protected $table = 'lpm_akreditasi_elemens';

    protected $fillable = [
        'kriteria_id',
        'kode_elemen',
        'deskripsi',
        'urutan',
        'status_kelengkapan',
    ];

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(LpmAkreditasiKriteria::class, 'kriteria_id');
    }

    public function indikators(): HasMany
    {
        return $this->hasMany(LpmAkreditasiIndikator::class, 'elemen_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(LpmAkreditasiEvidence::class, 'elemen_id');
    }
}
