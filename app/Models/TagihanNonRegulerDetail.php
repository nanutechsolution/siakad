<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagihanNonRegulerDetail extends Model
{
    use HasFactory;

    protected $table = 'tagihan_non_reguler_details';

    protected $fillable = [
        'tagihan_id',
        'komponen_biaya_id',
        'nama_komponen_snapshot',
        'nominal_dasar',
        'nominal_diskon',
        'nominal_tagihan',
        'nominal_terbayar',
    ];

    protected $casts = [
        'nominal_dasar' => 'decimal:2',
        'nominal_diskon' => 'decimal:2',
        'nominal_tagihan' => 'decimal:2',
        'nominal_terbayar' => 'decimal:2',
    ];

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(TagihanNonReguler::class, 'tagihan_id');
    }

    public function komponenBiaya(): BelongsTo
    {
        return $this->belongsTo(KeuanganKomponenBiaya::class, 'komponen_biaya_id');
    }
}