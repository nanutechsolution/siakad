<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalUjian extends Model
{
    // Sesuaikan nama tabel jika di DB menggunakan plural
    protected $table = 'jadwal_ujians';

    /**
     * Relasi ke data pengawas ujian.
     */
    public function pengawas(): HasMany
    {
        // Hubungkan ke model JadwalUjianPengawas dengan foreign key 'jadwal_ujian_id'
        return $this->hasMany(JadwalUjianPengawas::class, 'jadwal_ujian_id');
    }
}
