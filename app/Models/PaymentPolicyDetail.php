<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentPolicyDetail extends Model
{
    protected $table = 'payment_policy_details';

    protected $fillable = [
        'payment_policy_id',
        'komponen_biaya_id',
        'minimal_persen',
        'minimal_nominal',
        'wajib',
    ];

    protected $casts = [
        'minimal_persen' => 'decimal:2',
        'minimal_nominal' => 'decimal:2',
        'wajib' => 'boolean',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(PaymentPolicy::class, 'payment_policy_id');
    }

    public function komponenBiaya(): BelongsTo
    {
        return $this->belongsTo(KeuanganKomponenBiaya::class, 'komponen_biaya_id');
    }
}