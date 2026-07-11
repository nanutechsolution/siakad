<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class KeuanganSaldoTransaction extends Model
{
    use LogsActivity;

    protected $table = 'keuangan_saldo_transactions';

    protected $fillable = [
        'saldo_id',
        'tipe', // IN atau OUT
        'nominal',
        'referensi_id',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('keuangan_saldo_transaction')
            ->setDescriptionForEvent(fn(string $eventName) => "Transaksi saldo (Mutasi {$this->tipe}) telah di-{$eventName}");
    }

    public function saldo(): BelongsTo
    {
        return $this->belongsTo(KeuanganSaldo::class, 'saldo_id');
    }
}
