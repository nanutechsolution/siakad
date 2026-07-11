<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KeuanganKomponenBiaya extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'keuangan_komponen_biaya';

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
            'urutan_prioritas' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the payment policy details associated with this component.
     */
    public function paymentPolicyDetails(): HasMany
    {
        return $this->hasMany(PaymentPolicyDetail::class, 'komponen_biaya_id');
    }

    /**
     * Get the tariff details associated with this component.
     */
    public function detailTarifs(): HasMany
    {
        return $this->hasMany(KeuanganDetailTarif::class, 'komponen_biaya_id');
    }
}