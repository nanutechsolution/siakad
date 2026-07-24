<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAkreditasiLembaga extends Model
{
    protected $fillable = ['kode', 'nama', 'jenis'];

    public function akreditasis(): HasMany
    {
        return $this->hasMany(LpmAkreditasi::class, 'lembaga_id');
    }
}
