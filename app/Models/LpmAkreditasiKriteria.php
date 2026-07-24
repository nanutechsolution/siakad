<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAkreditasiKriteria extends Model
{
    protected $table = 'lpm_akreditasi_kriterias';

    protected $fillable = [
        'akreditasi_id',
        'kode_kriteria',
        'nama_kriteria',
        'urutan',
    ];

    public function akreditasi(): BelongsTo
    {
        return $this->belongsTo(LpmAkreditasi::class, 'akreditasi_id');
    }

    public function elemens(): HasMany
    {
        return $this->hasMany(LpmAkreditasiElemen::class, 'kriteria_id')->orderBy('urutan');
    }
}
