<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalUjianPengawas extends Model
{
    // Mengarahkan ke nama tabel yang sesuai di database Anda
    protected $table = 'jadwal_ujian_pengawas';

    protected $fillable = [
        'jadwal_ujian_id',
        'person_id',
        // tambahkan kolom lain dari tabel jadwal_ujian_pengawas jika ada (misal: tipe_pengawas, ruang_id, dll)
    ];

    /**
     * Relasi ke data Jadwal Ujian terkait.
     */
    public function jadwalUjian(): BelongsTo
    {
        return $this->belongsTo(JadwalUjian::class, 'jadwal_ujian_id');
    }

    /**
     * Relasi ke data Personil / Dosen yang bertindak sebagai pengawas.
     * Menggunakan tabel ref_people sesuai dengan foreign key `person_id`.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id'); // Sesuaikan nama model 'RefPerson' jika berbeda di aplikasi Anda
    }
}
