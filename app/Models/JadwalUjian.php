<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalUjian extends Model
{
    protected $table = 'jadwal_ujians';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'tanggal_ujian' => 'date',
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
    ];

    public function pengawas(): HasMany
    {
        // Hubungkan ke model JadwalUjianPengawas dengan foreign key 'jadwal_ujian_id'
        return $this->hasMany(JadwalUjianPengawas::class, 'jadwal_ujian_id');
    }

    public function jadwalKuliah(): BelongsTo
    {
        return $this->belongsTo(JadwalKuliah::class, 'jadwal_kuliah_id');
    }

    public function ruang(): BelongsTo
    {
        return $this->belongsTo(RefRuang::class, 'ruang_id');
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(JadwalUjianPeserta::class, 'jadwal_ujian_id');
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis_ujian) {
            'UTS' => 'Ujian Tengah Semester',
            'UAS' => 'Ujian Akhir Semester',
            'SUSULAN' => 'Ujian Susulan',
            default => 'Ujian Lainnya',
        };
    }
}
