<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmKategoriStandar extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function standars(): HasMany
    {
        return $this->hasMany(LpmStandar::class, 'kategori_standar_id');
    }
}
