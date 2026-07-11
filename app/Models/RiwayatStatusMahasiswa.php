<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatusMahasiswa extends Model
{
    protected $table = 'riwayat_status_mahasiswas';

    protected $fillable = [
        'mahasiswa_id',
        'tahun_akademik_id',
        'status_kuliah',
        'ips',
        'ipk',
        'sks_semester',
        'sks_total',
        'nomor_sk',
    ];

    protected $casts = [
        'ips' => 'decimal:2',
        'ipk' => 'decimal:2',
        'sks_semester' => 'integer',
        'sks_total' => 'integer',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }
}