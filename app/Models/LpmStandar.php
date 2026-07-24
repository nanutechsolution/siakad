<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmStandar extends Model
{
    protected $fillable = [
        'kode_standar',
        'nama_standar',
        'kategori',
        'kategori_standar_id',
        'pernyataan_standar',
        'target_pencapaian',
        'satuan',
        'versi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kategoriStandar(): BelongsTo
    {
        return $this->belongsTo(LpmKategoriStandar::class, 'kategori_standar_id');
    }

    public function indikators(): HasMany
    {
        return $this->hasMany(LpmIndikator::class, 'standar_id');
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(LpmDokumen::class, 'standar_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(LpmAmiChecklist::class, 'standar_id')->orderBy('urutan');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(LpmAmiFinding::class, 'standar_id');
    }
}