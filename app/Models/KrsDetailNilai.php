<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KrsDetailNilai extends Model
{
    protected $table = 'krs_detail_nilai';

    protected $fillable = [
        'krs_detail_id',
        'komponen_id',
        'nilai_angka',
    ];

    protected $casts = [
        'nilai_angka' => 'decimal:2',
    ];

    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }

    public function komponenNilai(): BelongsTo
    {
        return $this->belongsTo(RefKomponenNilai::class, 'komponen_id');
    }
}