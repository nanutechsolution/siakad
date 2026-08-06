<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class MahasiswaKelas extends Model
{
    use LogsActivity;
    protected $table = 'mahasiswa_kelas';

    protected $fillable = [
        'mahasiswa_id',
        'kelas_id',
        'tanggal_masuk',
        'tanggal_keluar',
    ];
    protected $with = [
        'mahasiswa.person',
    ];

    /**
     * Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('mahasiswa_kelas')
            ->logOnly([
                'mahasiswa_id',
                'kelas_id',
                'tanggal_masuk',
                'tanggal_keluar',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $event) {
                return match ($event) {
                    'created' => 'Mahasiswa masuk ke kelas',
                    'updated' => 'Data keanggotaan kelas diubah',
                    'deleted' => 'Riwayat kelas dihapus',
                    default => $event,
                };
            });
    }

    public function getStatusAttribute(): string
    {
        return $this->tanggal_keluar
            ? 'NONAKTIF'
            : 'AKTIF';
    }
    public function getNamaMahasiswaAttribute(): string
    {
        return "{$this->mahasiswa->nim} - {$this->mahasiswa->person->nama_lengkap}";
    }
    public function getNimAttribute(): ?string
    {
        return $this->mahasiswa?->nim;
    }

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'id');
    }


    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereNull('tanggal_keluar');
    }

    public function scopeNonAktif(Builder $query): Builder
    {
        return $query->whereNotNull('tanggal_keluar');
    }
}
