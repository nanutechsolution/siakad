<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAkreditasi extends Model
{
    protected $table = 'lpm_akreditasis';

    protected $fillable = [
        'lembaga_id',
        'prodi_id',
        'jenis_akreditasi',
        'instrumen',
        'status',
        'peringkat_target',
        'peringkat_hasil',
        'tanggal_submit',
        'tanggal_visitasi',
        'berlaku_sampai',
    ];

    protected $casts = [
        'tanggal_submit' => 'date',
        'tanggal_visitasi' => 'date',
        'berlaku_sampai' => 'date',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(LpmAkreditasiLembaga::class, 'lembaga_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    public function kriterias(): HasMany
    {
        return $this->hasMany(LpmAkreditasiKriteria::class, 'akreditasi_id')->orderBy('urutan');
    }
}
