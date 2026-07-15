<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EdomProgress extends Model
{
    use HasFactory;

    protected $table = 'lpm_edom_progress';

    protected $fillable = [
        'mahasiswa_id',
        'jadwal_kuliah_id',
        'dosen_id',
        'is_completed',
    ];

    /**
     * Relasi ke model Mahasiswa
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Relasi ke model Jadwal Kuliah
     */
    public function jadwalKuliah()
    {
        return $this->belongsTo(JadwalKuliah::class, 'jadwal_kuliah_id');
    }

    /**
     * Relasi ke model Dosen
     */
    public function dosen()
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }
}
