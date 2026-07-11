<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DispensasiAkademik extends Model
{
    use HasUuids, LogsActivity;

    protected $table = 'dispensasi_akademiks';

    protected $fillable = [
        'mahasiswa_id',
        'jenis',
        'alasan',
        'berlaku_mulai',
        'berlaku_sampai',
        'status',
        'disetujui_oleh',
        'disetujui_pada',
        'created_by',
    ];

    protected $casts = [
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
        'disetujui_pada' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Dispensasi Akademik has been {$eventName}");
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke log histori jika Anda sudah membuat modelnya sesuai skema dispensasi_akademik_logs
    public function statusLogs(): HasMany
    {
        return $this->hasMany(DispensasiAkademikLog::class, 'dispensasi_id')->orderBy('created_at', 'desc');
    }
}
