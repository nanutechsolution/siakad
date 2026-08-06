<?php

namespace App\Models;

use App\Enums\PembimbingAkademikMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class KonfigurasiPembimbingAkademik extends Model
{
    use LogsActivity;
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('konfigurasi-pembimbing-akademik')
            ->logOnly([
                'prodi_id',
                'angkatan_id',
                'mode',
                'aktif',
                'keterangan',
            ])
            ->logOnlyDirty();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'Menambahkan konfigurasi pembimbing akademik',
            'updated' => 'Mengubah konfigurasi pembimbing akademik',
            'deleted' => 'Menghapus konfigurasi pembimbing akademik',
            default => $eventName,
        };
    }
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
