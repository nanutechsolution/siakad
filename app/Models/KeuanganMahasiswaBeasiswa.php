<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class KeuanganMahasiswaBeasiswa extends Model
{
    use LogsActivity;

    protected $table = 'keuangan_mahasiswa_beasiswas';

    protected $fillable = [
        'mahasiswa_id',
        'beasiswa_id',
        'tahun_akademik_mulai_id',
        'tahun_akademik_akhir_id',
        'nomor_sk',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('mahasiswa_beasiswa');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Mahasiswa::class, 'mahasiswa_id');
    }

    public function beasiswa(): BelongsTo
    {
        return $this->belongsTo(KeuanganMasterBeasiswa::class, 'beasiswa_id');
    }

    public function tahunAkademikMulai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\RefTahunAkademik::class, 'tahun_akademik_mulai_id');
    }

    public function tahunAkademikAkhir(): BelongsTo
    {
        return $this->belongsTo(\App\Models\RefTahunAkademik::class, 'tahun_akademik_akhir_id');
    }
}