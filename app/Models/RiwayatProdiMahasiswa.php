<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatProdiMahasiswa extends Model
{
    protected $table = 'riwayat_prodi_mahasiswas';

    protected $fillable = [
        'mahasiswa_id',
        'prodi_id',
        'nomor_sk',
        'tanggal_berlaku',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_berlaku' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Mahasiswa pemilik riwayat prodi.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Program studi.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }
}
