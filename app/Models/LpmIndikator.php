<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmIndikator extends Model
{
    protected $fillable = [
        'standar_id',
        'kode_indikator',
        'nama_indikator',
        'satuan',
        'deskripsi',
        'slug',
        'bobot',
        'is_iku',
        'is_active',
        'sumber_data_siakad',
        'calculation_method',
        'calculation_params',
    ];

    protected $casts = [
        'is_iku' => 'boolean',
        'is_active' => 'boolean',
        'calculation_params' => 'array',
    ];

    public function standar(): BelongsTo
    {
        return $this->belongsTo(LpmStandar::class, 'standar_id');
    }

    public function ikuTargets(): HasMany
    {
        return $this->hasMany(LpmIkuTarget::class, 'indikator_id');
    }
}