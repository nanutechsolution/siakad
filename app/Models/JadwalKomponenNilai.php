<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalKomponenNilai extends Model
{
    protected $table = 'jadwal_komponen_nilai'; // Sesuai dengan isi schema sql

    protected $fillable = [
        'jadwal_kuliah_id',
        'komponen_id',
        'bobot_persen', // Gunakan bobot_persen sesuai skema asli Anda
    ];

    public function jadwalKuliah(): BelongsTo
    {
        return $this->belongsTo(JadwalKuliah::class, 'jadwal_kuliah_id');
    }

    public function masterKomponen(): BelongsTo
    {
        return $this->belongsTo(RefKomponenNilai::class, 'komponen_id');
    }
}