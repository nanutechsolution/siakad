<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MidtransTransaction extends Model
{
    use HasUuids;

    protected $table = 'midtrans_transactions';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'tagihan_id',
        'tagihan_type',
        'mahasiswa_id',
        'nominal',
        'snap_token',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'created_at' => 'datetime',
    ];
}
