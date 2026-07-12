<?php

namespace App\Models;

use App\Enums\StatusKehadiran;
use App\Enums\StatusKehadiranEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PerkuliahanAbsensi extends Model
{
    protected $table = 'perkuliahan_absensi';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'perkuliahan_sesi_id',
        'krs_detail_id',
        'status_kehadiran',
        'waktu_check_in',
        'bukti_validasi',
        'is_manual_update',
        'modified_by_user_id',
        'alasan_perubahan',
    ];

    protected $casts = [
        'status_kehadiran' => StatusKehadiranEnum::class,
        'waktu_check_in' => 'datetime',
        'bukti_validasi' => 'array',
        'is_manual_update' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->getKey())) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(PerkuliahanSesi::class, 'perkuliahan_sesi_id');
    }

    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }
}
