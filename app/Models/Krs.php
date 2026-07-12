<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KrsStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Krs extends Model
{
    use HasUuids, LogsActivity;

    protected $table = 'krs';

    protected $fillable = [
        'mahasiswa_id',
        'tahun_akademik_id',
        'kelas_id',
        'tgl_krs',
        'status_krs',
        'is_paket_snapshot',
        'dosen_wali_id',
        'diajukan_at',
        'disetujui_oleh',
        'disetujui_pada',
        'ditolak_oleh',
        'ditolak_pada',
        'catatan_admin',
        'is_financial_verified',
        'financial_override_by',
        'financial_override_reason',
        'total_sks_diambil',
        'dispensasi_id',
    ];

    protected $casts = [
        'tgl_krs' => 'datetime',
        'status_krs' => KrsStatusEnum::class,
        'is_paket_snapshot' => 'boolean',
        'diajukan_at' => 'datetime',
        'disetujui_pada' => 'datetime',
        'ditolak_pada' => 'datetime',
        'is_financial_verified' => 'boolean',
        'total_sks_diambil' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "KRS has been {$eventName}");
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function dosenWali(): BelongsTo
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_wali_id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function ditolakOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditolak_oleh');
    }

    public function financialOverrideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'financial_override_by');
    }

    public function dispensasi(): BelongsTo
    {
        return $this->belongsTo(DispensasiAkademik::class, 'dispensasi_id');
    }

    public function krsDetails(): HasMany
    {
        return $this->hasMany(KrsDetail::class, 'krs_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(KrsStatusLog::class, 'krs_id')->orderBy('created_at', 'desc');
    }
    public function dosenWaliSnapshot(): BelongsTo
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_wali_id', 'id');
    }
}
