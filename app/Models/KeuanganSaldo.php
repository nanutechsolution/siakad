<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class KeuanganSaldo extends Model
{
    use HasUuids, LogsActivity;

    protected $table = 'keuangan_saldos';

    // Primary Key adalah UUID
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mahasiswa_id',
        'saldo',
        'last_updated_at',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'last_updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('keuangan_saldo');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(KeuanganSaldoTransaction::class, 'saldo_id');
    }


    // Helper untuk hitung saldo terbaru dari transaksi
    public function getSaldoAkhirAttribute(): float
    {
        return (float) $this->transactions()->sum(DB::raw("CASE WHEN tipe = 'IN' THEN nominal ELSE -nominal END"));
    }
}
