<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class JadwalKuliahDosen extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }
    /**
     * Nama tabel di database.
     */
    protected $table = 'jadwal_kuliah_dosen';

    /**
     * Kolom-kolom yang dapat diisi.
     */
    protected $fillable = [
        'jadwal_kuliah_id',
        'dosen_id',
        'is_koordinator',
        'is_penilai',
        'rencana_tatap_muka',
    ];

    /**
     * Casting tipe data kolom.
     */
    protected $casts = [
        'is_koordinator' => 'boolean',
        'is_penilai' => 'boolean',
        'rencana_tatap_muka' => 'integer',
    ];

    /**
     * Relasi ke Jadwal Kuliah.
     */
    public function jadwalKuliah(): BelongsTo
    {
        return $this->belongsTo(JadwalKuliah::class, 'jadwal_kuliah_id');
    }

    /**
     * Relasi ke Dosen (TrxDosen sesuai dengan schema constraint).
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }
}
