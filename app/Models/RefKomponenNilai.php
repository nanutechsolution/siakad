<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefKomponenNilai extends Model
{
    protected $table = 'ref_komponen_nilai';

    protected $fillable = [
        'nama_komponen',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function krsDetailNilais(): HasMany
    {
        return $this->hasMany(KrsDetailNilai::class, 'komponen_id');
    }
}