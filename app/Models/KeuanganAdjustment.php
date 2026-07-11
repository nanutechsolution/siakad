<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Keuangan\JenisAdjustment;
use App\Enums\Keuangan\StatusAdjustment;
use App\Enums\Keuangan\TindakLanjutKelebihanBayar;
use App\Support\Keuangan\NomorAdjustmentGenerator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class KeuanganAdjustment extends Model
{
    use HasUuids, SoftDeletes, LogsActivity;

    protected $table = 'keuangan_adjustments';

    // Disable auto-increment karena primary key adalah char(36) UUID
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tagihan_id',
        'nomor_adjustment',
        'jenis_adjustment',
        'nominal',
        'keterangan',
        'status',
        'tindak_lanjut_kelebihan_bayar',
        'created_by',
        'diajukan_oleh',
        'diajukan_at',
        'disetujui_oleh',
        'disetujui_at',
        'catatan_approval',
        'diposting_at',
        'dibatalkan_oleh',
        'dibatalkan_at',
        'alasan_pembatalan',
        'adjustment_pembalik_id',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'jenis_adjustment' => JenisAdjustment::class,
        'status' => StatusAdjustment::class,
        'tindak_lanjut_kelebihan_bayar' => TindakLanjutKelebihanBayar::class,
        'diajukan_at' => 'datetime',
        'disetujui_at' => 'datetime',
        'diposting_at' => 'datetime',
        'dibatalkan_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('adjustment_keuangan');
    }

    protected static function booted(): void
    {
        static::creating(function (KeuanganAdjustment $adjustment) {
            if (empty($adjustment->nomor_adjustment)) {
                $adjustment->nomor_adjustment = NomorAdjustmentGenerator::generate();
            }
        });
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(TagihanMahasiswa::class, 'tagihan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function pembatal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh');
    }

    public function adjustmentPembalik(): BelongsTo
    {
        return $this->belongsTo(self::class, 'adjustment_pembalik_id');
    }
}