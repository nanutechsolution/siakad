<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    use HasFactory;

    protected $fillable = ['district_id', 'code', 'name', 'type'];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function mahasiswaBiodatas(): HasMany
    {
        return $this->hasMany(MahasiswaBiodata::class);
    }

    /** Regency induk (Kabupaten/Kota) — convenience lewat district. */
    public function regency(): Regency
    {
        return $this->district->regency;
    }

    /** Province induk — convenience lewat district + regency. */
    public function province(): Province
    {
        return $this->district->regency->province;
    }
}
