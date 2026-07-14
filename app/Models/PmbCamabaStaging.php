<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmbCamabaStaging extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     * Secara eksplisit dideklarasikan karena nama tabel tidak menggunakan akhiran 's' standar Laravel.
     */
    protected $table = 'pmb_camaba_staging';

    /**
     * Atribut yang diizinkan untuk mass-assignment (Mass Assignable).
     */
    protected $fillable = [
        'external_id',
        'payload',
        'status',
        'error_log',
        'retry_count',
        'last_retry_at',
        'processed_at',
        'source',
        'ip_address',
        'user_agent',
        'mahasiswa_id',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data bawaan (Native Types).
     */
    protected $casts = [
        'payload'       => 'array', // Otomatis konversi string JSON ke Array PHP
        'last_retry_at' => 'datetime',
        'processed_at'  => 'datetime',
    ];

    /**
     * Relasi ke model Mahasiswa.
     * Menggunakan tipe data Char(36) UUID dari tabel mahasiswas.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'id');
    }
}