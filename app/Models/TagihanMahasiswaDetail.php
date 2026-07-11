<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagihanMahasiswaDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tagihan_mahasiswas_details';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal_dasar' => 'decimal:2',
            'nominal_diskon' => 'decimal:2',
            'nominal_tagihan' => 'decimal:2', // Stored generated column
            'nominal_terbayar' => 'decimal:2',
        ];
    }

    /**
     * Get the bill that owns this detail.
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(TagihanMahasiswa::class, 'tagihan_id');
    }

    /**
     * Get the fee component.
     */
    public function komponenBiaya(): BelongsTo
    {
        return $this->belongsTo(KeuanganKomponenBiaya::class, 'komponen_biaya_id');
    }
}