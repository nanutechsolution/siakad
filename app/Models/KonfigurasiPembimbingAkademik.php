<?php

namespace App\Models;

use App\Enums\PembimbingAkademikMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KonfigurasiPembimbingAkademik extends Model
{
    protected $table = 'konfigurasi_pembimbing_akademik';

    protected $fillable = [
        'prodi_id',
        'angkatan_id',
        'mode',
        'aktif',
        'keterangan',
    ];

    protected $casts = [
        'mode' => PembimbingAkademikMode::class,
        'aktif' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(RefAngkatan::class, 'angkatan_id', 'id_tahun');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPerKelas(): bool
    {
        return $this->mode === 'PER_KELAS';
    }

    public function isPerMahasiswa(): bool
    {
        return $this->mode === 'PER_MAHASISWA';
    }
}
