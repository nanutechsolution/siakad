<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LpmUnitKerja extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'jenis_unit',
        'kode_unit',
        'nama_unit',
        'fakultas_id',
        'prodi_id',
        'kepala_unit_person_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(RefFakultas::class, 'fakultas_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    public function kepalaUnit(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'kepala_unit_person_id');
    }

    public function pics(): HasMany
    {
        return $this->hasMany(LpmUnitPic::class, 'unit_kerja_id');
    }

    public function ikuTargets(): HasMany
    {
        return $this->hasMany(LpmIkuTarget::class, 'unit_kerja_id');
    }
}
