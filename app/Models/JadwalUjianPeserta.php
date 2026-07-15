<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalUjianPeserta extends Model
{
    protected $table = 'jadwal_ujian_pesertas';

    protected $casts = [
        'waktu_check_in' => 'datetime',
    ];

    /** A = Alpa/belum hadir (default), H = Hadir, dst -- selaraskan dgn modul ujian. */
    public const STATUS_LABEL = [
        'H' => 'Hadir',
        'A' => 'Belum/Tidak Hadir',
        'I' => 'Izin',
        'S' => 'Sakit',
    ];

    public function jadwalUjian(): BelongsTo
    {
        return $this->belongsTo(JadwalUjian::class, 'jadwal_ujian_id');
    }

    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status_kehadiran] ?? $this->status_kehadiran;
    }
}
