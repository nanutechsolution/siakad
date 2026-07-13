<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KrsDetail extends Model
{
    protected $table = 'krs_detail';

    protected $fillable = [
        'krs_id',
        'jadwal_kuliah_id',
        'mata_kuliah_id',
        'kode_mk_snapshot',
        'nama_mk_snapshot',
        'sks_snapshot',
        'activity_type_snapshot',
        'ekuivalensi_id',
        'status_ambil',
        'nilai_angka',
        'nilai_huruf',
        'nilai_indeks',
        'is_published',
        'is_locked',
        'is_edom_filled',
    ];

    protected $casts = [
        'sks_snapshot' => 'integer',
        'nilai_angka' => 'decimal:2',
        'nilai_indeks' => 'decimal:2',
        'is_published' => 'boolean',
        'is_locked' => 'boolean',
        'is_edom_filled' => 'boolean',
    ];


    public function nilaiKomponen(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(KrsDetailNilai::class, 'krs_detail_id');
    }


    public function krs(): BelongsTo
    {
        return $this->belongsTo(Krs::class, 'krs_id');
    }

    public function jadwalKuliah(): BelongsTo
    {
        return $this->belongsTo(JadwalKuliah::class, 'jadwal_kuliah_id');
    }
    public function absensi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PerkuliahanAbsensi::class, 'krs_detail_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MasterMataKuliah::class, 'mata_kuliah_id');
    }
    /**
     * Relasi ke detail nilai komponen mahasiswa.
     */
    public function detailNilai()
    {
        return $this->hasMany(\App\Models\KrsDetailNilai::class, 'krs_detail_id');
    }
    public function getNilaiKomponen(int $komponenId): float
    {
        return (float) $this->detailNilai->where('komponen_id', $komponenId)->first()?->nilai_angka ?? 0.00;
    }
}
