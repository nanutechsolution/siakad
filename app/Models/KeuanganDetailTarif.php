<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeuanganDetailTarif extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'keuangan_detail_tarif';

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
            'nominal' => 'decimal:2',
            'berlaku_semester' => 'integer',
        ];
    }

    /**
     * Get the parent tariff scheme.
     */
    public function skemaTarif(): BelongsTo
    {
        return $this->belongsTo(KeuanganSkemaTarif::class, 'skema_tarif_id');
    }

    /**
     * Get the fee component for this tariff detail.
     */
    public function komponenBiaya(): BelongsTo
    {
        return $this->belongsTo(KeuanganKomponenBiaya::class, 'komponen_biaya_id');
    }

    
}
