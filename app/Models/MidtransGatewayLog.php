<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class MidtransGatewayLog extends Model
{
    use HasUuids;

    protected $table = 'midtrans_gateway_logs';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'transaction_status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function () {
            throw new RuntimeException('midtrans_gateway_logs bersifat append-only — tidak boleh di-update.');
        });

        static::deleting(function () {
            throw new RuntimeException('midtrans_gateway_logs bersifat append-only — tidak boleh dihapus.');
        });
    }
}
